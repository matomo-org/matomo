<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Piwik\View;

/**
 * Command to generate an self-updating github action yml file either for Matomo Core or
 * an individual Matomo plugin.
 */
class GenerateGitHubTestActionFile extends ConsoleCommand
{
    const COMMAND_NAME = 'generate:test-action';
    const LATEST_PHP_VERSION = '8.1';

    protected $plugin = null;
    protected $phpVersions = null;
    protected $dependentPlugins = null;
    protected $repoRootDirOverride = null;
    protected $forcePHPTests = false;
    protected $forceUITests = false;
    protected $forceClientTests = false;
    protected $protectArtifacts = false;
    protected $enableRedis = true;
    protected $setupScript = null;
    protected $hasSubmodules = null;
    protected $scheduleCron = null;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
             ->setDescription('Generates a github action workflow file for a plugin. The file can be auto-updating based on the parameters supplied.')
             ->addRequiredValueOption('plugin', null, 'The plugin for whom a action yml file should be generated. If not provided yml file for core will be generated.')
             ->addOptionalValueOption('php-versions', null, "List of PHP versions to test against, ie, 7.2,8.1.")
             ->addOptionalValueOption('dependent-plugins', null, 'List of additional plugins that need to be checked out before running tests. Comma separated list. e.g. "matomo-org/plugin-CustomVariables,nickname/PluginName"')
             ->addRequiredValueOption('repo-root-dir', null, "Path to the repo for whom a action yml file will be generated for.")
             ->addNoValueOption('force-php-tests', null, "Forces the presence of the PHP tests jobs for plugin builds.")
             ->addNoValueOption('force-ui-tests', null, "Forces the presence of the UI tests jobs for plugin builds.")
             ->addNoValueOption('force-client-tests', null, "Forces the presence of the Client tests jobs for plugin builds.")
             ->addNoValueOption('protect-artifacts', null, "Indicates if artifacts should be stored protected on artifact server.")
             ->addOptionalValueOption('setup-script', null, "Shell script to run (after setup, before tests), relative to plugins directory. .i.e .github/scripts/setup.sh")
             ->addNoValueOption('has-submodules', null, "Defines if the repo has submodules that need to be checked out.")
             ->addNoValueOption('enable-redis', null, "Defines if a redis service should be set up for PHP and UI testing.")
             ->addOptionalValueOption('schedule-cron', null, "Value to schedule a cron. eg \"0 2 * * 6\" will run the job at 02:00 on Saturday.");
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        global $piwik_minimumPHPVersion;

        $this->plugin = $input->getOption('plugin');
        $this->phpVersions = array_filter(explode(',', $input->getOption('php-versions') ?? ''));
        $this->dependentPlugins = array_filter(explode(',', $input->getOption('dependent-plugins') ?? ''));
        $this->repoRootDirOverride = $input->getOption('repo-root-dir');
        $this->forcePHPTests = !!$input->getOption('force-php-tests');
        $this->forceUITests = !!$input->getOption('force-ui-tests');
        $this->forceClientTests = !!$input->getOption('force-client-tests');
        $this->enableRedis = !!$input->getOption('enable-redis') || empty($this->plugin);
        $this->protectArtifacts = !!$input->getOption('protect-artifacts');
        $this->setupScript = $input->getOption('setup-script');
        $this->hasSubmodules = $input->getOption('setup-script');
        $this->scheduleCron = $input->getOption('schedule-cron');

        if (!empty($this->plugin)) {
            if (!file_exists($this->getRepoRootDir())) {
                $output->writeln('Plugin directory could not be found. Please check the plugins name or provide --repo-root-dir option');
                return 1;
            }

            $minimalPHPVersion = $this->getPluginMinimumPhpVersion($output);
        }

        if (empty($minimalPHPVersion)) {
            $minimalPHPVersion = $piwik_minimumPHPVersion;
        }

        // remove patch version
        if (substr_count($minimalPHPVersion, '.') > 1) {
            $minimalPHPVersion = substr($minimalPHPVersion, 0, strpos($minimalPHPVersion, '.', strpos($minimalPHPVersion, '.') + 1));
        }

        if (empty($this->phpVersions)) {
            $this->phpVersions = array_unique([$minimalPHPVersion, self::LATEST_PHP_VERSION]);
        } else {
            $filteredVersions = [];
            foreach ($this->phpVersions as $version) {
                if (version_compare($version, $minimalPHPVersion, '>=')) {
                    $filteredVersions[] = $version;
                }
            }
            if (empty($filteredVersions[0])) {
                array_unshift($filteredVersions, $minimalPHPVersion);
            }
            $this->phpVersions = $filteredVersions;
        }

        if (empty($this->phpVersions)) {
            $output->writeln('No php versions detectable to test against');
            return 1;
        }

        $template = new View('@TestRunner/matomo-tests.yml.twig');

        $template->assign('phpVersions', $this->phpVersions);
        $template->assign('enableRedis', $this->enableRedis);
        $template->assign('dependentPlugins', $this->dependentPlugins);
        $template->assign('plugin', $this->plugin);
        $template->assign('protectArtifacts', $this->protectArtifacts);
        $template->assign('setupScript', $this->setupScript);
        $template->assign('hasSubmodules', $this->hasSubmodules);
        $template->assign('hasJavaScriptTests', $this->isTargetPluginContainsJavaScriptTests());
        $template->assign('hasClientTests', $this->isTargetPluginContainsClientTests());
        $template->assign('hasUITests', $this->isTargetPluginContainsUITests());
        $template->assign('hasPluginTests', $this->isTargetPluginContainsPluginTests());
        $template->assign('scheduleCron', $this->scheduleCron);

        $basePath = empty($this->plugin) ? $this->getPiwikRootDir() : $this->getRepoRootDir();
        $filePath = $basePath . '/.github/workflows/matomo-tests.yml';

        Filesystem::mkdir(dirname($filePath));

        if (!file_put_contents($filePath, $template->render())) {
            $output->writeln("$filePath can not be written");
            return 1;
        }

        $output->writeln('Action file written to ' . $filePath);

        return 0;
    }

    private function getPluginMinimumPhpVersion($output)
    {
        $pluginJsonPath = $this->getPluginJsonRootDir();
        if (empty($pluginJsonPath) || !file_exists($pluginJsonPath)) {
            $output->writeln("No plugin.json file found, cannot detect minimum PHP version.");
            return null;
        }

        $pluginJsonContents = file_get_contents($pluginJsonPath);
        $pluginJsonContents = json_decode($pluginJsonContents, $assoc = true);
        if (empty($pluginJsonContents['require']['php'])) {
            $output->writeln("No PHP version requirement in plugin.json");
            return null;
        }

        $phpRequirement = $pluginJsonContents['require']['php'];
        if (!preg_match('/>=([0-9]+\.[0-9]+\.[0-9]+)/', $phpRequirement, $matches)) {
            $output->writeln("Cannot detect minimum php version from php requirement: '$phpRequirement'");
            return null;
        }

        $phpRequirement = $matches[1];

        $output->writeln("Detected minimum PHP version: '$phpRequirement'");

        return $phpRequirement;
    }

    private function getPluginJsonRootDir()
    {
        return $this->getRepoRootDir() . '/plugin.json';
    }

    private function isTargetPluginContainsPluginTests()
    {
        if ($this->forcePHPTests) {
            return true;
        }

        $pluginPath = $this->getRepoRootDir();
        return $this->doesFolderContainPluginTests($pluginPath . "/tests")
            || $this->doesFolderContainPluginTests($pluginPath . "/Test");
    }

    private function doesFolderContainPluginTests($folderPath)
    {
        return $this->folderContains($folderPath, '/.*Test\.php/');
    }

    private function isTargetPluginContainsUITests()
    {
        if ($this->forceUITests) {
            return true;
        }

        $pluginPath = $this->getRepoRootDir();
        return $this->doesFolderContainUITests($pluginPath . "/tests")
            || $this->doesFolderContainUITests($pluginPath . "/Test");
    }

    private function isTargetPluginContainsClientTests()
    {
        if ($this->forceClientTests) {
            return true;
        }

        $pluginPath = $this->getRepoRootDir();
        return $this->doesFolderContainClientTests($pluginPath);
    }

    private function isTargetPluginContainsJavaScriptTests()
    {
        $pluginPath = $this->getRepoRootDir();
        return file_exists($pluginPath . "/tests/javascript/index.php")
            || file_exists($pluginPath . "/Test/javascript/index.php");
    }

    private function doesFolderContainClientTests($folderPath)
    {
        return $this->folderContains($folderPath, '/.*\.spec\.js/')
            || $this->folderContains($folderPath, '/.*\.spec\.ts/');
    }

    private function doesFolderContainUITests($folderPath)
    {
        return $this->folderContains($folderPath, '/.*_spec\.js/');
    }

    private function folderContains($folderPath, $filePattern)
    {
        if (!is_dir($folderPath)) {
            return false;
        }

        $directoryIterator = new \RecursiveDirectoryIterator($folderPath);
        $flatIterator = new \RecursiveIteratorIterator($directoryIterator);
        $fileIterator = new \RegexIterator($flatIterator, $filePattern, \RegexIterator::GET_MATCH);
        $fileIterator->rewind();

        return $fileIterator->valid();
    }

    protected function getRepoRootDir()
    {
        return $this->repoRootDirOverride ?: ($this->getPiwikRootDir() . "/plugins/{$this->plugin}");
    }

    protected function getPiwikRootDir()
    {
        return __DIR__ . "/../../..";
    }
}
