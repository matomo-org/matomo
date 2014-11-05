<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Profiler;
use Piwik\CliMulti;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Piwik\Plugins\TestRunner\Aws\Config;
use Piwik\Plugins\TestRunner\Aws\Instance;
use Piwik\Plugins\TestRunner\Aws\Ssh;
use Piwik\Plugins\TestRunner\Runner\InstanceLauncher;
use Piwik\Plugins\TestRunner\Runner\Remote;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;
use Exception;

/**
 * Benchmarks Piwik code.
 */
class TestsBenchmark extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:benchmark');
        $this->setDescription('Run a Piwik benchmark locally or on AWS.');
        $this->addOption('benchmark-file', null, InputOption::VALUE_REQUIRED, 'Run the test case found in this file. Specify a path to the file.');
        $this->addOption('fixture', null, InputOption::VALUE_REQUIRED,
            'The data to load into the DB before benchmarking. This argument is required and should be set to a fully qualified'
          . ' class name.');
        $this->addOption('fixture-file', null, InputOption::VALUE_REQUIRED, 'Path to the PHP file that contains the fixture definition to use.');
        $this->addOption('url', null, InputOption::VALUE_REQUIRED,
            'A Piwik request URL to benchmark. eg, "?module=API&action=VisitsSummary.get&idSite=1&date=today&period=day"');
        $this->addOption('aws', null, InputOption::VALUE_NONE, 'If supplied, benchmark is run on AWS. (recommended for core devs)');
        $this->addOption('xhprof', null, InputOption::VALUE_NONE, 'If supplied, execution is profiled w/ xhprof.');
        $this->addOption('checkout', null, InputOption::VALUE_REQUIRED, 'Git hash, tag or branch to checkout. Defaults to current hash', $this->getCurrentGitHash());
        $this->setHelp('This command will benchmark a URL or PHPUnit test case of your choosing. Before executing the benchmark, it will load a fixture of your choosing into the database. This lets you run benchmarks under any number of conditions.

To benchmark a URL, run the command using the --url=\'?...\' option. To benchmark a PHPUnit test case, run the command with the --benchmark-file=path/to/file.php option.

Specify a fixture to setup before running the benchmark with the --fixture=Piwik\\\\Tests\\\\Fixtures\\\\SomeFixtureClass option. You can benchmark with your own test data by creating a new fixture in a PHP class and using the --fixture-file=path/to/fixture.php option. Then specify the fixture class using the --fixture=... option.

You can also run the tests on EC2 (if you have the proper credentials) and run the benchmark w/ xhprof using the appropriate options.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $benchmarkFile = $input->getOption('benchmark-file');
        $fixture = $input->getOption('fixture');
        $fixtureFile = $input->getOption('fixture-file');
        $urlToTest = $input->getOption('url');
        $useAws = $input->getOption('aws');
        $useXhprof = $input->getOption('xhprof');
        $gitHash = $input->getOption('checkout');

        $this->checkThereIsSomethingToTest($benchmarkFile, $urlToTest);
        $this->checkTheFixtureCanBeFound($fixture, $fixtureFile);

        if ($useAws) {
            $this->launchAndForwardCommandToAws($input, $output, $gitHash);
        } else {
            $this->loadFixture($output, $fixture);
            $this->executeBenchmark($output, $urlToTest, $benchmarkFile, $useXhprof);
        }
    }

    private function launchAndForwardCommandToAws(InputInterface $input, OutputInterface $output, $gitHash)
    {
        // TODO: code duplication w/ TestsRunOnAws.
        $awsConfig = new Config();
        $awsConfig->validate();

        $runOnAwsCommand = new TestsRunOnAws();
        $host = $runOnAwsCommand->launchInstance($output, $useOneInstancePerTestSuite = false, $awsConfig, $testSuite = null);

        $ssh = Ssh::connectToAws($host, $awsConfig->getPemFile());
        $ssh->setOutput($output);

        $testRunner = new Remote($ssh);
        $testRunner->updatePiwik($gitHash);
        $testRunner->replaceConfigIni(PIWIK_INCLUDE_PATH . '/plugins/TestRunner/Aws/config.ini.php');

        if (!empty($patchFile)) {
            $testRunner->applyPatch($patchFile);
        }

        // TODO: need to send the benchmark file & fixture file if running on AWS

        $thisCommand = $this->getConsoleCommandStringFromInput($input);
        $ssh->exec($thisCommand);
    }

    private function loadFixture(OutputInterface $output, $fixtureClass)
    {
        $output->writeln("Setting up fixture <comment>$fixtureClass</comment>...");

        $fixtureInput = new ArrayInput(array(
            'fixture' => $fixtureClass
        ));

        $setupFixture = new TestsSetupFixture();
        $setupFixture->run($fixtureInput, $output);
    }

    private function executeBenchmark(OutputInterface $output, $urlToTest, $benchmarkFile, $useXhprof)
    {
        if (!empty($benchmarkFile)) {
            $this->benchmarkPhpTestCase($output, $benchmarkFile, $useXhprof);
        } else {
            $this->benchmarkPiwikUrl($output, $urlToTest, $useXhprof);
        }
    }

    private function benchmarkPhpTestCase(OutputInterface $output, $urlToTest, $benchmarkFile, $useXhprof)
    {
        if ($useXhprof) {
            Profiler::setupProfilerXHProf($isMainRun = true);
            putenv('PIWIK_USE_XHPROF=1');

            $output->writeln("Using xhprof.");
        }

        $output->writeln("Running the test cases in <comment>$benchmarkFile</comment>...");

        $phpunitCommand = 'cd tests/PHPUnit && ../../vendor/phpunit/phpunit/phpunit "' . $benchmarkFile . '"';

        $startTime = microtime(true);
        passthru($phpunitCommand);
        $elapsed = microtime(true) - $startTime;

        $this->finishBenchmark($output, $elapsed);
    }

    private function benchmarkPiwikUrl(OutputInterface $output, $urlToTest, $useXhprof)
    {
        if ($useXhprof) {
            $urlToTest .= '&xhprof=1';
        }

        $urlToTest .= '&testmode=1';

        $output->writeln("Requesting <comment>$urlToTest</comment>...");

        $cliMulti = new CliMulti();

        $startTime = microtime(true);
        $requestOutput = $cliMulti->request(array($urlToTest));
        $elapsed = microtime(true) - $startTime;

        $this->finishBenchmark($output, $elapsed);

        $output->writeln("Url returned: <comment>" . reset($requestOutput) . "</comment>");
    }

    private function finishBenchmark(OutputInterface $output, $elapsed)
    {
        $output->writeln("");
        $output->writeln("Finished in <comment>{$elapsed}s</comment>.");
        $output->writeln("");
    }

    private function checkThereIsSomethingToTest($benchmarkFile, $urlToTest)
    {
        // TODO: split into multiple functions
        if (empty($benchmarkFile)
            && empty($urlToTest)
        ) {
            throw new Exception("Nothing to test: either the --benchmark-file or the --url must be specified.");
        }

        if (!empty($benchmarkFile)
            && !empty($urlToTest)
        ) {
            throw new Exception("Both --benchmark-file and --url specified, not sure which to test. Please use only one.");
        }

        if (!empty($benchmarkFile)) {
            if (!file_exists($benchmarkFile)) {
                throw new Exception("Cannot find benchmark file '$benchmarkFile'.");
            }
        }
    }

    private function checkTheFixtureCanBeFound($fixture, $fixtureFile)
    {
        if (empty($fixture)) {
            throw new Exception("The --fixture option is required. Set it to the fully qualified class name of the fixture to load.");
        }

        if (!empty($fixtureFile)) {
            if (!file_exists($fixtureFile)) {
                throw new Exception("Cannot find fixture file '$fixtureFile'.");
            }

            require_once $fixtureFile;
        }

        if (!class_exists($fixture)) {
            throw new Exception("Fixture '$fixture' does not exist.");
        }
    }

    // TODO: copied from other command, must refactor
    private function getCurrentGitHash()
    {
        return trim(`git rev-parse HEAD`);
    }
}
