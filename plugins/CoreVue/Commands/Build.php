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
use Piwik\Log\LoggerInterface;
use Piwik\Plugin\Manager;

class Build extends ConsoleCommand
{
    const RECOMMENDED_NODE_VERSION = '16.0.0';
    const RECOMMENDED_NPM_VERSION = '7.0.0';
    const RETRY_COUNT = 2;

    protected function configure()
    {
        $this->setName('vue:build');
        $this->setDescription('Builds vue modules for one or more plugins.');
        $this->addOptionalArgument('plugins', 'Plugins whose vue modules to build. Defaults to all plugins.', [], true);
        $this->addNoValueOption('bail', null, 'If supplied, will exit immediately.');
        $this->addNoValueOption('watch', null, 'If supplied, will watch for changes and automatically rebuild.');
        $this->addNoValueOption('clear-webpack-cache');
        $this->addNoValueOption('print-build-command');
    }

    public function isEnabled()
    {
        return \Piwik\Development::isEnabled();
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        self::checkVueCliServiceAvailable();
        $this->checkNodeJsVersion();

        $clearWebpackCache = $input->getOption('clear-webpack-cache');
        if ($clearWebpackCache) {
            $this->clearWebpackCache();
        }

        $printBuildCommand = $input->getOption('print-build-command');
        $watch = $input->getOption('watch');

        $allPluginNames = $this->getAllPlugins();
        $allPluginNames = $this->filterPluginsWithoutVueLibrary($allPluginNames, true);

        $plugins = $input->getArgument('plugins');
        if (empty($plugins)) {
            $plugins = $allPluginNames;
            $output->writeln("<info>Going to build all plugins with Vue libraries: "
                . implode(', ', $plugins));
        } else {
            $plugins = $this->filterPluginsWithoutVueLibrary($plugins);
        }

        if (empty($plugins)) {
            $output->writeln("<error>No plugins to build!</error>");
            return self::FAILURE;
        }

        $plugins = $this->ensureUntranspiledPluginDependenciesArePresent($plugins);
        $plugins = PluginUmdAssetFetcher::orderPluginsByPluginDependencies($plugins);

        // remove webpack cache since it can result in strange builds if present
        Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/node_modules/.cache', true);

        $bail = $input->getOption('bail');

        $failed = $this->build($plugins, $printBuildCommand, $allPluginNames, $watch, $bail);
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function ensureUntranspiledPluginDependenciesArePresent(array $plugins): array
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

    private function isTypeOutputPresent(string $dependency): bool
    {
        $typeDirectory = PIWIK_INCLUDE_PATH . '/@types/' . $dependency . '/index.d.ts';
        return is_file($typeDirectory);
    }

    private function build(array $plugins, bool $printBuildCommand, array $allPluginNames, bool $watch = false, bool $bail = false): int
    {
        if ($watch) {
            $this->watch($plugins, $printBuildCommand, $allPluginNames);
            return 0;
        }

        $failed = 0;

        foreach ($plugins as $plugin) {
            $buildFailed = (int) $this->buildFiles($plugin, $printBuildCommand, $allPluginNames);
            if ($buildFailed && $bail) {
                $this->getOutput()->writeln("<error>Build failed, bailing.</error>");
                return $failed;
            }

            $failed += $buildFailed;
        }

        return $failed;
    }

    private function watch(array $plugins, bool $printBuildCommand, array $allPluginNames): void
    {
        $commandSingle = 'cd ' . PIWIK_INCLUDE_PATH . ' && '
            . "BROWSERSLIST_IGNORE_OLD_DATA=1 FORCE_COLOR=1 MATOMO_CURRENT_PLUGIN=%2\$s "
            . 'MATOMO_ALL_PLUGINS=' . implode(',', $allPluginNames) . ' '
            . 'node ' . self::getVueCliServiceProxyBin() . ' build --mode=development --target lib --name '
            . "%1\$s --filename=%1\$s.development --no-clean %2\$s/vue/src/index.ts --dest %2\$s/vue/dist --watch &";

        $command = '';
        foreach ($plugins as $plugin) {
            $pluginDirPath = Manager::getRelativePluginDirectory($plugin);
            $command .= sprintf($commandSingle, $plugin, $pluginDirPath) . ' ';
        }

        if ($printBuildCommand) {
            $this->getOutput()->writeln("<comment>$command</comment>");
            return;
        }

        passthru($command);
    }

    private function buildFiles(string $plugin, bool $printBuildCommand, array $allPluginNames): int
    {
        $output = $this->getOutput();
        $pluginDirPath = Manager::getRelativePluginDirectory($plugin);

        $command = 'cd ' . PIWIK_INCLUDE_PATH . ' && '
            . "BROWSERSLIST_IGNORE_OLD_DATA=1 FORCE_COLOR=1 MATOMO_CURRENT_PLUGIN=$pluginDirPath "
            . 'MATOMO_ALL_PLUGINS=' . implode(',', $allPluginNames) . ' '
            . 'node ' . self::getVueCliServiceProxyBin() . ' build --target lib --name ' . $plugin
            . " $pluginDirPath/vue/src/index.ts --dest $pluginDirPath/vue/dist";

        if ($printBuildCommand) {
            $output->writeln("<comment>$command</comment>");
            return 0;
        }

        $this->clearPluginTypes($plugin);

        $output->writeln("<comment>Building $plugin...</comment>");
        if ($output->isVerbose()) {
            passthru($command, $returnCode);
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

        @unlink("$pluginDirPath/vue/dist/$plugin.common.js");
        @unlink("$pluginDirPath/vue/dist/$plugin.common.js.map");
        @unlink("$pluginDirPath/vue/dist/demo.html");

        // delete cjs webpack chunks
        shell_exec("rm " . "$pluginDirPath/vue/dist/$plugin.common.*.js* 2> /dev/null");

        return $returnCode != 0;
    }

    private function filterPluginsWithoutVueLibrary(array $plugins, bool $isAll = false): array
    {
        $pluginsWithVue = [];

        $logger = StaticContainer::get(LoggerInterface::class);

        foreach ($plugins as $plugin) {
            $pluginDirPath = Manager::getPluginDirectory($plugin);
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

    public static function getVueCliServiceBin(): string
    {
        return PIWIK_INCLUDE_PATH . "/node_modules/@vue/cli-service/bin/vue-cli-service.js";
    }

    public static function getVueCliServiceProxyBin(): string
    {
        return PIWIK_INCLUDE_PATH . "/plugins/CoreVue/scripts/cli-service-proxy.js";
    }

    public static function checkVueCliServiceAvailable(): void
    {
        $vueCliBin = self::getVueCliServiceBin();
        if (!is_file($vueCliBin)) {
            throw new \Exception("Cannot find vue cli bin file, did you forget to run `npm install`?");
        }
    }

    private function clearWebpackCache(): void
    {
        $path = PIWIK_INCLUDE_PATH . '/node_modules/.cache';
        Filesystem::unlinkRecursive($path, true);
    }

    private function clearPluginTypes($plugin): void
    {
        $path = PIWIK_INCLUDE_PATH . '/@types/' . $plugin;
        Filesystem::unlinkRecursive($path, true);
    }

    private function checkNodeJsVersion(): void
    {
        $output = $this->getOutput();
        $nodeVersion = ltrim(trim(`node -v`), 'v');
        $npmVersion = ltrim(trim(`npm -v`), 'v');

        if (version_compare($nodeVersion, self::RECOMMENDED_NODE_VERSION, '<')) {
            $output->writeln(sprintf(
                "<comment>The recommended node version for working with Vue is version %s or "
                . "greater and it looks like you're using %s. Building Vue files may not work with an older version, so "
                . "we recommend upgrading. nvm can be used to easily install new node versions.</comment>",
                self::RECOMMENDED_NODE_VERSION,
                $nodeVersion
            ));
        }

        if (version_compare($npmVersion, self::RECOMMENDED_NPM_VERSION, '<')) {
            $output->writeln(sprintf(
                "<comment>The recommended npm version for working with Vue is version %s "
                . "or greater and it looks like you're using %s. Using an older version may result in improper "
                . "dependencies being used, so we recommend upgrading. You can upgrade to the latest version with the "
                . "command %s</comment>",
                self::RECOMMENDED_NPM_VERSION,
                $npmVersion,
                'npm install -g npm@latest'
            ));
        }
    }

    private function isTypeScriptRaceConditionInOutput(string $plugin, string $concattedOutput): bool
    {
        if (!preg_match('/^TS2307: Cannot find module \'([^\']+)\' or its corresponding type declarations./', $concattedOutput, $matches)) {
            return false;
        }

        $file = $matches[1];
        $filePath = Manager::getPluginDirectory($plugin) . '/vue/src/' . $file;
        $isTypeScriptCompilerBug = file_exists($filePath);
        return $isTypeScriptCompilerBug;
    }

    private function getAllPlugins(): array
    {
        $pluginDirectories = array_merge(
            $GLOBALS['MATOMO_PLUGIN_DIRS'] ?? [],
            [
                [
                    'pluginsPathAbsolute' => PIWIK_INCLUDE_PATH . '/plugins',
                    'webrootDirRelativeToMatomo' => '.',
                ],
            ]
        );

        $allPlugins = [];

        foreach ($pluginDirectories as $pluginDirectoryInfo) {
            $absolutePath = $pluginDirectoryInfo['pluginsPathAbsolute'];
            foreach (scandir($absolutePath) as $subdirectory) {
                $wholePath = $absolutePath . DIRECTORY_SEPARATOR . $subdirectory;
                if (is_dir($wholePath)
                    && $subdirectory !== '.'
                    && $subdirectory !== '..'
                ) {
                    $allPlugins[] = $subdirectory;
                }
            }
        }

        return $allPlugins;
    }
}
