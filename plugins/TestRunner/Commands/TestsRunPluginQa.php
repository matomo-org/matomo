<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestsRunPluginQa extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-plugin-qa');
        $this->setDescription('Run Piwik Plugin QA tests for a specific plugin.');
        $this->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'The plugin to run tests on, eg, \'CustomAlerts\'.');
        $this->addOption('github-token', null, InputOption::VALUE_REQUIRED,
            'A valid github personal access token that has access to the plugin\'s repo. Needed for a few tests.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugin = $input->getOption('plugin');
        if (empty($plugin)) {
            throw new \Exception("The --plugin option is required.");
        }

        $pluginPath = PIWIK_INCLUDE_PATH . '/plugins/' . $plugin;
        if (!is_dir($pluginPath)) {
            throw new \Exception("Cannot find plugin directory for '$plugin'.");
        }

        $pluginGitPath = $pluginPath . '/.git';
        if (is_file($pluginGitPath)) {
            $pluginSlug = $this->findGithubRepoSlugFromSubmodule($plugin);
        } else if (is_dir($pluginGitPath)) {
            $pluginSlug = $this->findGithubRepoSlug($pluginGitPath);
        } else {
            throw new \Exception("Cannot find .git directory for plugin. Required in order to deduce whether the plugin is open source or not.");
        }

        $githubToken = $input->getOption('github-token');

        $output->writeln("<info>Detected plugin github slug: $pluginSlug</info>");

        return $this->runQaTests($output, $plugin, $githubToken, $pluginSlug);
    }

    private function findGithubRepoSlug($pluginGitPath)
    {
        $gitConfigPath = $pluginGitPath . '/config';
        $gitConfig = file_get_contents($gitConfigPath);

        if (!preg_match('/\[remote "origin"\]\s*url\s*=\s*(.*)/', $gitConfig, $firstMatches)) {
            throw new \Exception("Cannot deduce plugin repo slug from .git/config. Is there an 'origin' remote?");
        }

        $url = $firstMatches[1];
        if (!preg_match('/[:\/]([^\/]+\/.*?)\.git/', $url, $secondMatches)) {
            throw new \Exception("Cannot deduce github repo slug from git url in .git/config (url = '$url').");
        }

        return $secondMatches[1];
    }

    private function runQaTests(OutputInterface $output, $plugin, $githubToken, $pluginSlug)
    {
        $envVars = "PLUGIN_NAME=\"$plugin\" GITHUB_USER_TOKEN=\"$githubToken\" TRAVIS_REPO_SLUG=\"$pluginSlug\" ";
        $command = sprintf("%s/vendor/bin/phpunit ../travis/plugin_qa/PluginQualityTest.php", PIWIK_DOCUMENT_ROOT);
        $command = sprintf("cd %s/tests/PHPUnit && %s %s", PIWIK_DOCUMENT_ROOT, $envVars, $command);

        $output->writeln("");
        $output->writeln("Executing: <comment>$command</comment>");
        $output->writeln("");

        passthru($command, $returnCode);

        return $returnCode;
    }

    private function findGithubRepoSlugFromSubmodule($plugin)
    {
        $submoduleGitPath = PIWIK_INCLUDE_PATH . '/.git/modules/plugins/' . $plugin;
        if (!is_dir($submoduleGitPath)) {
            throw new \Exception("Cannot find .git submodule directory for $plugin. Expected it to be at: $submoduleGitPath");
        }

        return $this->findGithubRepoSlug($submoduleGitPath);
    }
}