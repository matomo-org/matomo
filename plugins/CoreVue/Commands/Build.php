<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\MobileMessaging\SMSProvider\Development;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends ConsoleCommand
{
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

        // remove webpack cache since it can result in strange builds if present
        Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/node_modules/.cache', true);

        $failed = $this->build($output, $plugins, $printBuildCommand, $watch);
        return $failed;
    }

    private function build(OutputInterface $output, $plugins, $printBuildCommand, $watch = false)
    {
        $failed = 0;

        foreach ($plugins as $plugin) {
            if ($watch) {
                $this->watch($plugin, $printBuildCommand, $output);
            } else {
                $failed += (int) $this->buildFiles($output, $plugin, $printBuildCommand);
            }
        }

        return $failed;
    }

    private function watch($plugin, $printBuildCommand, OutputInterface $output)
    {
        $command = "FORCE_COLOR=1 " . self::getVueCliServiceBin() . ' build --mode=development --target lib --name '
            . $plugin . " ./plugins/$plugin/vue/src/index.ts --dest ./plugins/$plugin/vue/dist --watch &";
        if ($printBuildCommand) {
            $output->writeln("<comment>$command</comment>");
            return;
        }
        passthru($command);
    }

    private function buildFiles(OutputInterface $output, $plugin, $printBuildCommand)
    {
        $command = "FORCE_COLOR=1 " . self::getVueCliServiceBin() . ' build --target lib --name ' . $plugin
            . " ./plugins/$plugin/vue/src/index.ts --dest ./plugins/$plugin/vue/dist";

        if ($printBuildCommand) {
            $output->writeln("<comment>$command</comment>");
            return 0;
        }

        $output->writeln("<comment>Building $plugin...</comment>");
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            passthru($command, $returnCode);
        } else {
            exec($command, $cmdOutput, $returnCode);
            if ($returnCode != 0
                || stripos(implode("\n", $cmdOutput), 'warning') !== false
            ) {
                $output->writeln("<error>Failed:</error>\n");
                $output->writeln($cmdOutput);
                $output->writeln("");
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
                $logger->warning("NOTE: Plugin {plugin} has a vue folder but no webpack config, cannot build it.", ['plugin' => $plugin]);
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
}
