<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Db;
use Piwik\Plugin;
use Piwik\Profiler;
use Piwik\Plugin\ConsoleCommand;

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
        $this->addOptionalArgument('variables', 'Eg a path to a file or directory, the name of a testsuite, the name of a plugin, ... We will try to detect what you meant. You can define multiple values', [], true);
        $this->addOptionalValueOption('options', 'o', 'All options will be forwarded to phpunit', '');
        $this->addOptionalValueOption('filter', null, 'Adds the phpunit filter option to run only specific tests that start with the given name', '');
        $this->addNoValueOption('xhprof', null, 'Profile using xhprof.');
        $this->addRequiredValueOption('group', null, 'Run only a specific test group. Separate multiple groups by comma, for instance core,plugins', '');
        $this->addRequiredValueOption('file', null, 'Execute tests within this file. Should be a path relative to the tests/PHPUnit directory.');
        $this->addRequiredValueOption('testsuite', null, 'Execute tests of a specific test suite, for instance unit, integration or system.');
        $this->addNoValueOption('enable-logging', null, 'Enable logging to the configured log file during tests.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $options = $input->getOption('options');
        $groups  = $input->getOption('group');
        $magics  = $input->getArgument('variables');
        $matomoDomain = $input->getOption('matomo-domain');
        $enableLogging = $input->getOption('enable-logging');
        $filter = $input->getOption('filter');

        if (!empty($filter)) {
            $options .= ' --filter=' . escapeshellarg($filter);
        }

        $groups = $this->getGroupsFromString($groups);

        // bin is the composer executeable directory, where all vendors (should) place their executables
        $command = PIWIK_VENDOR_PATH . '/bin/phpunit';

        if (!$this->isCoverageEnabled($options) && $this->isXdebugLoaded()) {
            $message = 'Did you know? You can run tests faster by disabling xdebug';
            if ($this->isXdebugCodeCoverageEnabled()) {
                $message .= ' (if you need xdebug, speed up tests by setting xdebug.coverage_enable=0)</comment>';
            }
            $output->writeln('<comment>' . $message . '</comment>');
        }

        // force xdebug usage for coverage options
        if ($this->isCoverageEnabled($options) && !$this->isXdebugLoaded()) {
            $output->writeln('<info>xdebug extension required for code coverage.</info>');

            $output->writeln('<info>searching for xdebug extension...</info>');

            $extensionDir = shell_exec('php-config --extension-dir');
            $xdebugFile   = trim($extensionDir) . DIRECTORY_SEPARATOR . 'xdebug.so';

            if (!file_exists($xdebugFile)) {
                $xdebugFile = $this->askAndValidate(
                    'xdebug not found. Please provide path to xdebug.so',
                    function ($xdebugFile) {
                        return file_exists($xdebugFile);
                    }
                );
            } else {
                $output->writeln('<info>xdebug extension found in extension path.</info>');
            }

            $output->writeln("<info>using $xdebugFile as xdebug extension.</info>");

            $phpunitPath = trim(shell_exec('which phpunit') ?? '');

            $command = sprintf('php -d zend_extension=%s %s', $xdebugFile, $phpunitPath);
        }

        if ($input->getOption('xhprof')) {
            Profiler::setupProfilerXHProf($isMainRun = true);

            putenv('PIWIK_USE_XHPROF=1');
        }

        $suite    = $this->getTestsuite();
        $testFile = $this->getTestFile();

        if (!empty($magics)) {
            foreach ($magics as $magic) {
                if (empty($suite) && (in_array($magic, $this->getTestsSuites()))) {
                    $suite = $this->buildTestSuiteName($magic);
                } elseif (empty($testFile) && 'core' === $magic) {
                    $testFile = $this->fixPathToTestFileOrDirectory('tests/PHPUnit');
                } elseif (empty($testFile) && 'plugins' === $magic) {
                    $testFile = $this->fixPathToTestFileOrDirectory('plugins');
                } elseif (empty($testFile) && file_exists($magic)) {
                    $testFile = $this->fixPathToTestFileOrDirectory($magic);
                } elseif (empty($testFile) && $this->getPluginTestFolderName($magic)) {
                    $testFile = $this->getPluginTestFolderName($magic);
                } elseif (empty($groups)) {
                    $groups = $this->getGroupsFromString($magic);
                } else {
                    $groups[] = $magic;
                }
            }
        }

        // Tear down any DB that already exists
        Db::destroyDatabaseObject();

        $this->executeTests($matomoDomain, $suite, $testFile, $groups, $options, $command, $enableLogging);

        return $this->returnVar;
    }

    private function getPluginTestFolderName($name)
    {
        $pluginName = $this->getPluginName($name);

        $folder = '';
        if (!empty($pluginName)) {
            $path = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName;

            if (is_dir($path . '/tests')) {
                $folder = $this->fixPathToTestFileOrDirectory($path . '/tests');
            } elseif (is_dir($path . '/Tests')) {
                $folder = $this->fixPathToTestFileOrDirectory($path . '/Tests');
            }
        }

        return $folder;
    }

    private function getPluginName($name)
    {
        $pluginNames = Plugin\Manager::getInstance()->getAllPluginsNames();

        foreach ($pluginNames as $pluginName) {
            if (strtolower($pluginName) === strtolower($name)) {
                return $pluginName;
            }
        }
    }

    private function getTestFile()
    {
        $testFile = $this->getInput()->getOption('file');

        if (empty($testFile)) {
            return '';
        }

        return $this->fixPathToTestFileOrDirectory($testFile);
    }

    private function executeTests($piwikDomain, $suite, $testFile, $groups, $options, $command, $enableLogging)
    {
        if (empty($suite) && empty($groups) && empty($testFile)) {
            foreach ($this->getTestsSuites() as $suite) {
                $suite = $this->buildTestSuiteName($suite);
                $this->executeTests($piwikDomain, $suite, $testFile, $groups, $options, $command, $enableLogging);
            }

            return;
        }

        $params = $this->buildPhpUnitCliParams($suite, $groups, $options);

        if (!empty($testFile)) {
            $params = $params . " " . $testFile;
        }

        $this->executeTestRun($piwikDomain, $command, $params, $enableLogging);
    }

    private function executeTestRun($piwikDomain, $command, $params, $enableLogging)
    {
        $output = $this->getOutput();
        $envVars = '';
        if (!empty($piwikDomain)) {
            $envVars .= "PIWIK_DOMAIN=$piwikDomain";
        }
        if (!empty($enableLogging)) {
            $envVars .= " MATOMO_TESTS_ENABLE_LOGGING=1";
        }

        $cmd = $this->getCommand($envVars, $command, $params);
        $output->writeln('Executing command: <info>' . $cmd . '</info>');
        passthru($cmd, $returnVar);
        $output->writeln("");

        $this->returnVar += $returnVar;
    }

    private function getTestsSuites()
    {
        return array('unit', 'integration', 'system', 'plugin');
    }

    /**
     * @param $command
     * @param $params
     * @return string
     */
    private function getCommand($envVars, $command, $params)
    {
        return sprintf('cd %s/tests/PHPUnit && %s %s %s', PIWIK_DOCUMENT_ROOT, $envVars, $command, $params);
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

    private function getTestsuite()
    {
        $suite = $this->getInput()->getOption('testsuite');

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

    private function isXdebugCodeCoverageEnabled()
    {
        return (bool)ini_get('xdebug.coverage_enable');
    }

    private function fixPathToTestFileOrDirectory($testFile)
    {
        if ('/' !== substr($testFile, 0, 1)) {
            $testFile = '../../' . $testFile;
        }

        return $testFile;
    }

    private function getGroupsFromString($groups)
    {
        $groups = explode(",", $groups);
        $groups = array_filter($groups, 'strlen');

        return $groups;
    }
}
