<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webpackBinPath = PIWIK_INCLUDE_PATH . '/node_modules/.bin/webpack';
        if (!is_file($webpackBinPath)) {
            $output->writeln("<comment>Cannot find webpack bin file, did you forget to run `npm install`?</comment>");
            return -1;
        }

        $watch = $input->getOption('watch');

        $plugins = $input->getArgument('plugins');
        if (empty($plugins)) {
            $plugins = $this->getAllPluginsWithVueLibrary();
        } else {
            $plugins = $this->filterPluginsWithoutVueLibrary($plugins);
        }

        $failed = $this->build($output, $plugins, $watch);
        return $failed;
    }

    private function build(OutputInterface $output, $plugins, $watch = false)
    {
        $pluginsDir = PIWIK_INCLUDE_PATH . '/plugins';

        $failed = 0;

        foreach ($plugins as $plugin) {
            $pluginDirPath = $pluginsDir . '/' . $plugin;
            $vueDir = $pluginDirPath . '/vue';

            if ($watch) {
                $this->watch($output, $vueDir);
            } else {
                $failed += $this->buildDevAndProd($output, $plugin, $vueDir);
            }
        }

        return $failed;
    }

    private function watch(OutputInterface $output, $vueDir)
    {
        $command = "cd '$vueDir' && NODE_ENV=development " . $this->getWebpackBin() . ' --watch &';
        passthru($command);
    }

    private function buildDevAndProd(OutputInterface $output, $plugin, $vueDir)
    {
        $failed = 0;

        foreach (['development', 'production'] as $env) {
            $command = "cd '$vueDir' && NODE_ENV=$env " . $this->getWebpackBin();

            $output->writeln("<comment>Building $plugin for $env...</comment>");
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                passthru($command, $returnCode);
            } else {
                exec($command, $cmdOutput, $returnCode);
                if ($returnCode != 0) {
                    $output->writeln("<error>Failed:</error>\n");
                    $output->writeln($cmdOutput);
                    $output->writeln("");
                }
            }

            if ($returnCode != 0) {
                ++$failed;
            }
        }

        return $failed;
    }

    private function getAllPluginsWithVueLibrary()
    {
        $pluginsDir = PIWIK_INCLUDE_PATH . '/plugins';

        $plugins = scandir($pluginsDir);
        return $this->filterPluginsWithoutVueLibrary($plugins);
    }

    private function filterPluginsWithoutVueLibrary($plugins)
    {
        $pluginsDir = PIWIK_INCLUDE_PATH . '/plugins';

        $pluginsWithVue = [];

        foreach ($plugins as $plugin) {
            $pluginDirPath = $pluginsDir . '/' . $plugin;
            $vueDir = $pluginDirPath . '/vue';
            if (!is_dir($vueDir)) {
                continue;
            }

            $webpackFile = $vueDir . '/webpack.config.js';
            if (!is_file($webpackFile)) {
                $logger = StaticContainer::get(LoggerInterface::class);
                $logger->warning("NOTE: Plugin {plugin} has a vue folder but no webpack config, cannot build it.");
                continue;
            }

            $pluginsWithVue[] = $plugin;
        }

        return $pluginsWithVue;
    }

    private function getWebpackBin()
    {
        return PIWIK_INCLUDE_PATH . '/node_modules/.bin/webpack';
    }
}
