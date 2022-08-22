<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\AssetManager\UIAssetFetcher\PluginUmdAssetFetcher;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends ConsoleCommand
{
    const RECOMMENDED_NODE_VERSION = '16.0.0';
    const RECOMMENDED_NPM_VERSION = '7.0.0';
    const RETRY_COUNT = 2;

    protected function configure()
    {
        $this->setName('vue:build');
        $this->setDescription('Builds vue modules for one or more plugins.');
        $this->addArgument('plugins', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Plugins whose vue modules to build. Defaults to all plugins.', []);
        $this->addOption('watch', null, InputOption::VALUE_NONE, 'If supplied, will watch for changes and automatically rebuild.');
        $this->addOption('clear-webpack-cache', null, InputOption::VALUE_NONE);
        $this->addOption('print-build-command', null, InputOption::VALUE_NONE);
    }

    public function isEnabled()
    {
        return \Piwik\Development::isEnabled();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        self::checkVueCliServiceAvailable();
        $this->checkNodeJsVersion($output);

        $clearWebpackCache = $input->getOption('clear-webpack-cache');
        if ($clearWebpackCache) {
            $this->clearWebpackCache();
        }

        $printBuildCommand = $input->getOption('print-build-command');
        $watch = $input->getOption('watch');

        $plugins = $input->getArgument('plugins');
        if (empty($plugins)) {
            $plugins = $this->getAllPluginsWithVueLibrary();
        } else {
            $plugins = $this->filterPluginsWithoutVueLibrary($plugins);
            if (empty($plugins)) {
                $output->writeln("<error>No plugins to build!</error>");
                return 1;
            }
        }

        $plugins = $this->ensureUntranspiledPluginDependenciesArePresent($plugins);
        $plugins = PluginUmdAssetFetcher::orderPluginsByPluginDependencies($plugins);

        // remove webpack cache since it can result in strange builds if present
        Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/node_modules/.cache', true);

        $failed = $this->build($output, $plugins, $printBuildCommand, $watch);
        return $failed;
    }

    private function ensureUntranspiledPluginDependenciesArePresent($plugins)
    {
        $pluginDependenciesToAdd = [];
        foreach ($plugins as $plugin) {
            $dependencies = PluginUmdAssetFetcher::getPluginDependencies($plugin);
            foreach ($dependencies as $dependency) {
                if (!$this->isTypeOutputPresent($dependency)) {
                    $pluginDependenciesToAdd[] = $dependency;
                }
            }
        }
        return array_unique(array_merge($plugins, $pluginDependenciesToAdd));
    }

    private function isTypeOutputPresent($dependency)
    {
        $typeDirectory = PIWIK_INCLUDE_PATH . '/@types/' . $dependency . '/index.d.ts';
        return is_file($typeDirectory);
    }

    private function build(OutputInterface $output, $plugins, $printBuildCommand, $watch = false)
    {
        if ($watch) {
            $this->watch($plugins, $printBuildCommand, $output);
            return;
        }

        $failed = 0;

        foreach ($plugins as $plugin) {
            $failed += (int) $this->buildFiles($output, $plugin, $printBuildCommand);
        }

        return $failed;
    }

    private function watch($plugins, $printBuildCommand, OutputInterface $output)
    {
        $commandSingle = "BROWSERSLIST_IGNORE_OLD_DATA=1 FORCE_COLOR=1 MATOMO_CURRENT_PLUGIN=%1\$s "
            . 'node ' . self::getVueCliServiceProxyBin() . ' build --mode=development --target lib --name '
            . "%1\$s --filename=%1\$s.development --no-clean ./plugins/%1\$s/vue/src/index.ts --dest ./plugins/%1\$s/vue/dist --watch &";

        $command = '';
        foreach ($plugins as $plugin) {
            $command .= sprintf($commandSingle, $plugin) . ' ';
        }

        if ($printBuildCommand) {
            $output->writeln("<comment>$command</comment>");
            return;
        }

        passthru($command);
    }

    private function buildFiles(OutputInterface $output, $plugin, $printBuildCommand)
    {
        $command = "BROWSERSLIST_IGNORE_OLD_DATA=1 FORCE_COLOR=1 MATOMO_CURRENT_PLUGIN=$plugin "
            . 'node ' . self::getVueCliServiceProxyBin() . ' build --target lib --name ' . $plugin
            . " ./plugins/$plugin/vue/src/index.ts --dest ./plugins/$plugin/vue/dist";

        if ($printBuildCommand) {
            $output->writeln("<comment>$command</comment>");
            return 0;
        }

        $this->clearPluginTypes($plugin);

        $output->writeln("<comment>Building $plugin...</comment>");
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            passthru($command);
        } else {
            $attempts = 0;
            while ($attempts < self::RETRY_COUNT) {
                exec($command, $cmdOutput, $returnCode);

                $concattedOutput = implode("\n", $cmdOutput);
                if ($this->isTypeScriptRaceConditionInOutput($plugin, $concattedOutput)) {
                    $output->writeln("<comment>The TypeScript compiler encountered a race condition when compiling "
                        . "files (files that exist were not found), retrying.</comment>");

                    ++$attempts;
                    continue;
                }

                if ($returnCode != 0
                    || stripos($concattedOutput, 'warning') !== false
                ) {
                    $output->writeln("<error>Failed:</error>\n");
                    $output->writeln($cmdOutput);
                    $output->writeln("");
                }

                break;
            }
        }

        @unlink(PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/$plugin.common.js");
        @unlink(PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/$plugin.common.js.map");
        @unlink(PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/demo.html");

        // delete cjs webpack chunks
        shell_exec("rm " . PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/$plugin.common.*.js* 2> /dev/null");

        return $returnCode != 0;
    }

    private function getAllPluginsWithVueLibrary()
    {
        $pluginsDir = PIWIK_INCLUDE_PATH . '/plugins';

        $plugins = scandir($pluginsDir);
        return $this->filterPluginsWithoutVueLibrary($plugins, $isAll = true);
    }

    private function filterPluginsWithoutVueLibrary($plugins, $isAll = false)
    {
        $pluginsDir = PIWIK_INCLUDE_PATH . '/plugins';

        $pluginsWithVue = [];

        $logger = StaticContainer::get(LoggerInterface::class);

        foreach ($plugins as $plugin) {
            $pluginDirPath = $pluginsDir . '/' . $plugin;
            $vueDir = $pluginDirPath . '/vue';
            if (!is_dir($vueDir)) {
                if (!$isAll) {
                    $logger->error("Cannot find vue library for plugin {plugin}, nothing to build.", ['plugin' => $plugin]);
                }
                continue;
            }

            $vueIndexFile = $vueDir . '/src/index.ts';
            if (!is_file($vueIndexFile)) {
                $logger->warning("NOTE: Plugin {plugin} has a vue folder but no index.ts, cannot build it.", ['plugin' => $plugin]);
                continue;
            }

            $pluginsWithVue[] = $plugin;
        }

        return $pluginsWithVue;
    }

    public static function getVueCliServiceBin()
    {
        return PIWIK_INCLUDE_PATH . "/node_modules/@vue/cli-service/bin/vue-cli-service.js";
    }

    public static function getVueCliServiceProxyBin()
    {
        return PIWIK_INCLUDE_PATH . "/plugins/CoreVue/scripts/cli-service-proxy.js";
    }

    public static function checkVueCliServiceAvailable()
    {
        $vueCliBin = self::getVueCliServiceBin();
        if (!is_file($vueCliBin)) {
            throw new \Exception("Cannot find vue cli bin file, did you forget to run `npm install`?");
        }
    }

    private function clearWebpackCache()
    {
        $path = PIWIK_INCLUDE_PATH . '/node_modules/.cache';
        Filesystem::unlinkRecursive($path, true);
    }

    private function clearPluginTypes($plugin)
    {
        $path = PIWIK_INCLUDE_PATH . '/@types/' . $plugin;
        Filesystem::unlinkRecursive($path, true);
    }

    private function checkNodeJsVersion(OutputInterface $output)
    {
        $nodeVersion = ltrim(trim(`node -v`), 'v');
        $npmVersion = ltrim(trim(`npm -v`), 'v');

        if (version_compare($nodeVersion, self::RECOMMENDED_NODE_VERSION, '<')) {
            $output->writeln(sprintf("<comment>The recommended node version for working with Vue is version %s or "
                . "greater and it looks like you're using %s. Building Vue files may not work with an older version, so "
                . "we recommend upgrading. nvm can be used to easily install new node versions.</comment>",
                self::RECOMMENDED_NODE_VERSION, $nodeVersion));
        }

        if (version_compare($npmVersion, self::RECOMMENDED_NPM_VERSION, '<')) {
            $output->writeln(sprintf("<comment>The recommended npm version for working with Vue is version %s "
                . "or greater and it looks like you're using %s. Using an older version may result in improper "
                . "dependencies being used, so we recommend upgrading. You can upgrade to the latest version with the "
                . "command %s</comment>",
                self::RECOMMENDED_NPM_VERSION, $npmVersion, 'npm install -g npm@latest'));
        }
    }

    private function isTypeScriptRaceConditionInOutput($plugin, $concattedOutput)
    {
        if (!preg_match('/^TS2307: Cannot find module \'([^\']+)\' or its corresponding type declarations./', $concattedOutput, $matches)) {
            return false;
        }

        $file = $matches[1];
        $filePath = PIWIK_INCLUDE_PATH . '/plugins/' . $plugin . '/vue/src/' . $file;
        $isTypeScriptCompilerBug = file_exists($filePath);
        return $isTypeScriptCompilerBug;
    }
}
