<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Piwik\Profiler;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes PHP tests.
 */
class TestsRun extends ConsoleCommand
{
    private $returnVar = 0;

    protected function configure()
    {
        $this->setName('tests:run');
        $this->setDescription('Run Piwik PHPUnit tests one testsuite after the other');
        $this->addArgument('group', InputArgument::OPTIONAL, 'Run only a specific test group. Separate multiple groups by comma, for instance core,plugins', '');
        $this->addOption('options', 'o', InputOption::VALUE_OPTIONAL, 'All options will be forwarded to phpunit', '');
        $this->addOption('xhprof', null, InputOption::VALUE_NONE, 'Profile using xhprof.');
        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'Execute tests within this file. Should be a path relative to the tests/PHPUnit directory.');
        $this->addOption('testsuite', null, InputOption::VALUE_REQUIRED, 'Execute tests of a specific test suite, for instance UnitTests, IntegrationTests or SystemTests.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('options');
        $groups  = $input->getArgument('group');

        $groups = explode(",", $groups);
        $groups = array_filter($groups, 'strlen');

        $command = '../../vendor/phpunit/phpunit/phpunit';

        if (!$this->isCoverageEnabled($options) && $this->isXdebugLoaded()) {
            $output->writeln('<comment>Did you know? You can run tests faster by disabling xdebug</comment>');
        }

        // force xdebug usage for coverage options
        if ($this->isCoverageEnabled($options) && !$this->isXdebugLoaded()) {

            $output->writeln('<info>xdebug extension required for code coverage.</info>');

            $output->writeln('<info>searching for xdebug extension...</info>');

            $extensionDir = shell_exec('php-config --extension-dir');
            $xdebugFile   = trim($extensionDir) . DIRECTORY_SEPARATOR . 'xdebug.so';

            if (!file_exists($xdebugFile)) {

                $dialog = $this->getHelperSet()->get('dialog');

                $xdebugFile = $dialog->askAndValidate($output, 'xdebug not found. Please provide path to xdebug.so', function($xdebugFile) {
                    return file_exists($xdebugFile);
                });
            } else {

                $output->writeln('<info>xdebug extension found in extension path.</info>');
            }

            $output->writeln("<info>using $xdebugFile as xdebug extension.</info>");

            $phpunitPath = trim(shell_exec('which phpunit'));

            $command = sprintf('php -d zend_extension=%s %s', $xdebugFile, $phpunitPath);
        }

        if ($input->getOption('xhprof')) {
            Profiler::setupProfilerXHProf($isMainRun = true);

            putenv('PIWIK_USE_XHPROF=1');
        }

        $testFile = $input->getOption('file');
        if (!empty($testFile)) {
            $this->executeTestFile($testFile, $options, $command, $output);
        } else {
            $suite = $this->getTestsuite($input);
            $this->executeTestGroups($suite, $groups, $options, $command, $output);
        }

        return $this->returnVar;
    }

    private function executeTestFile($testFile, $options, $command, OutputInterface $output)
    {
        if ('/' !== substr($testFile, 0, 1)) {
            $testFile = '../../' . $testFile;
        }

        $params = $options . " " . $testFile;
        $this->executeTestRun($command, $params, $output);
    }

    private function executeTestGroups($suite, $groups, $options, $command, OutputInterface $output)
    {
        if (empty($suite) && empty($groups)) {
            foreach ($this->getTestsSuites() as $suite) {
                $suite = $this->buildTestSuiteName($suite);
                $this->executeTestGroups($suite, $groups, $options, $command, $output);
            }

            return;
        }

        $params = $this->buildPhpUnitCliParams($suite, $groups, $options);

        $this->executeTestRun($command, $params, $output);
    }

    private function executeTestRun($command, $params, OutputInterface $output)
    {
        $cmd = $this->getCommand($command, $params);
        $output->writeln('Executing command: <info>' . $cmd . '</info>');
        passthru($cmd, $returnVar);
        $output->writeln("");

        $this->returnVar += $returnVar;
    }

    private function getTestsSuites()
    {
        return array('unit', 'integration', 'system');
    }

    /**
     * @param $command
     * @param $params
     * @return string
     */
    private function getCommand($command, $params)
    {
        return sprintf('cd %s/tests/PHPUnit && %s %s', PIWIK_DOCUMENT_ROOT, $command, $params);
    }

    private function buildPhpUnitCliParams($suite, $groups, $options)
    {
        $params = $options . " ";

        if (!empty($groups)) {
            $groups  = implode(',', $groups);
            $params .= '--group ' . $groups . ' ';
        } else {
            $groups  = '';
        }

        if (!empty($suite)) {
            $params .= ' --testsuite ' . $suite;
        } else {
            $suite = '';
        }

        $params = str_replace('%suite%', $suite, $params);
        $params = str_replace('%group%', $groups, $params);

        return $params;
    }

    private function getTestsuite(InputInterface $input)
    {
        $suite = $input->getOption('testsuite');

        if (empty($suite)) {
            return;
        }

        $availableSuites = $this->getTestsSuites();

        if (!in_array($suite, $availableSuites)) {
            throw new \InvalidArgumentException('Invalid testsuite specified. Use one of: ' . implode(', ', $availableSuites));
        }

        $suite = $this->buildTestSuiteName($suite);

        return $suite;
    }

    private function buildTestSuiteName($suite)
    {
        return ucfirst($suite) . 'Tests';
    }

    private function isCoverageEnabled($options)
    {
        return false !== strpos($options, '--coverage');
    }

    private function isXdebugLoaded()
    {
        return extension_loaded('xdebug');
    }

}