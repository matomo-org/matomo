<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Development;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\TestRunner\Aws\Config;
use Piwik\Plugins\TestRunner\Aws\Instance;
use Piwik\Plugins\TestRunner\Aws\Ssh;
use Piwik\Plugins\TestRunner\Runner\InstanceLauncher;
use Piwik\Plugins\TestRunner\Runner\Remote;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestsRunOnAws extends ConsoleCommand
{
    private $allowedTestSuites = array('integration', 'system', 'all', 'ui');

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('tests:run-aws');
        $this->addArgument('testsuite', InputArgument::OPTIONAL, 'Allowed values: ' . implode(', ', $this->allowedTestSuites));
        $this->addOption('launch-only', null, InputOption::VALUE_NONE, 'Only launches an instance and outputs the connection parameters. Useful if you want to connect via SSH.');
        $this->addOption('update-only', null, InputOption::VALUE_NONE, 'Launches an instance, outputs the connection parameters and prepares the instance for a test run but does not actually run the tests. It will also checkout the specified version.');
        $this->addOption('one-instance-per-testsuite', null, InputOption::VALUE_NONE, 'Launches an instance, outputs the connection parameters and prepares the instance for a test run but does not actually run the tests. It will also checkout the specified version.');
        $this->addOption('checkout', null, InputOption::VALUE_REQUIRED, 'Git hash, tag or branch to checkout. Defaults to current hash', $this->getCurrentGitHash());
        $this->setDescription('Run a specific testsuite on AWS');
        $this->setHelp('To use this command you have to configure the [tests]aws_* section in config/config.ini.php. See config/global.ini.php for all available options.

To run a test simply specify the testsuite you want to run: <comment>./console tests:run-aws system</comment>. This will launch a new instance on AWS or reuse an already running one. We start one instance per keyname. This makes sure two different developers do not use the same instance at the same time.

By default it will execute the tests of the git hash you are currently on. If this hash is not pushed yet or if you want to run tests of a specific git hash / branch / tag use the <comment>--checkout</comment> option: <comment>./console tests:run-aws --checkout="master" system</comment>.

If you want to debug a problem and access the AWS instance using SSH you can specify the <comment>--launch-only</comment> or <comment>--update-only</comment> option.

By default we will launch only one instance per keyname meaning you should not execute this command while another test is running. It would start the tests twice on the same instance and lead to errors. If you want to run two different testsuites at the same time (for instance <comment>system</comment> and <comment>ui</comment>) specify the <comment>one-instance-per-testsuite</comment> option. This will launch one instance for system tests and one for ui tests:
<comment>./console tests:run-aws system</comment>
<comment>./console tests:run-aws --one-instance-per-testsuite ui // will launch a new instance for ui testsuites</comment>
');
    }

    /**
     * Execute command like: ./console core:clear-caches
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $testSuite  = $this->getTestSuite($input);
        $launchOnly = $input->getOption('launch-only');
        $updateOnly = $input->getOption('update-only');
        $gitHash    = $input->getOption('checkout');
        $perTestsuite = $input->getOption('one-instance-per-testsuite');

        if (empty($testSuite) && empty($launchOnly) && empty($updateOnly)) {
            throw new \InvalidArgumentException('Either provide a testsuite argument or define <comment>--launch-only</comment> or <comment>--update-only</comment>');
        }

        $awsConfig = new Config();
        $awsConfig->validate();

        $host = $this->launchInstance($output, $perTestsuite, $awsConfig, $testSuite);

        if ($launchOnly) {
            return 0;
        }

        $ssh = Ssh::connectToAws($host, $awsConfig->getPemFile());
        $ssh->setOutput($output);

        $testRunner = new Remote($ssh);
        $testRunner->updatePiwik($gitHash);

        if ($updateOnly) {
            $ssh->disconnect();

            return 0;
        }

        $testRunner->runTests($host, $testSuite);

        if (in_array($testSuite, array('system', 'all'))) {
            $output->writeln("<info>Tests finished. You can browse processed files at </info><comment>http://$host/tests/PHPUnit/System/processed/</comment>");
        } elseif ('ui' === $testSuite) {
            $output->writeln("<info>Tests finished. You can browse processed screenshots at </info><comment>http://$host/tests/PHPUnit/UI/screenshot-diffs/diffviewer.html</comment>");
        } else {
            $output->writeln("<info>Tests finished</info>");
        }

        $ssh->disconnect();
    }

    private function launchInstance(OutputInterface $output, $useOneInstancePerTestSuite, Config $awsConfig, $testSuite)
    {
        $awsInstance = new Instance($awsConfig, $testSuite);

        if ($useOneInstancePerTestSuite) {
            $awsInstance->enableUseOneInstancePerTestSuite();
        }

        $launcher = new InstanceLauncher($awsInstance);
        $host     = $launcher->launchOrResumeInstance();

        $output->writeln(sprintf("Access instance using <comment>ssh -i %s ubuntu@%s</comment>", $awsConfig->getPemFile(), $host));
        $output->writeln("You can log in to Piwik via root:secure at <comment>http://$host</comment>");
        $output->writeln("You can access database via root:secure (<comment>mysql -uroot -psecure</comment>)");
        $output->writeln("Files are located in <comment>~/www/piwik</comment>");
        $output->writeln(' ');

        return $host;
    }

    private function getTestSuite(InputInterface $input)
    {
        $testsuite = $input->getArgument('testsuite');

        if (!empty($testsuite) && !in_array($testsuite, $this->allowedTestSuites)) {
            throw new \InvalidArgumentException('Test suite argument is wrong, use one of following: ' . implode(', ', $this->allowedTestSuites));
        }

        return $testsuite;
    }

    private function getCurrentGitHash()
    {
        return trim(`git rev-parse HEAD`);
    }

}
