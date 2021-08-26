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
        $failed = 0;

        foreach ($plugins as $plugin) {
            if ($watch) {
                $this->watch($plugin);
            } else {
                $failed += (int) $this->buildFiles($output, $plugin);
            }
        }

        return $failed;
    }

    private function watch($plugin)
    {
        $command = $this->getVueCliServiceBin() . ' build --mode=development --target lib --name ' . $plugin . " ./plugins/$plugin/vue/src/index.ts --dest ./plugins/$plugin/vue/dist --watch &";
        passthru($command);
    }

    private function buildFiles(OutputInterface $output, $plugin)
    {
        $command = $this->getVueCliServiceBin() . ' build --target lib --name ' . $plugin . " ./plugins/$plugin/vue/src/index.ts --dest ./plugins/$plugin/vue/dist";

        $output->writeln("<comment>Building $plugin...</comment>");
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

        @unlink(PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/$plugin.common.js");
        @unlink(PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/$plugin.common.js.map");
        @unlink(PIWIK_INCLUDE_PATH . "/plugins/$plugin/vue/dist/demo.html");

        return $returnCode != 0;
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

            $vueIndexFile = $vueDir . '/src/index.ts';
            if (!is_file($vueIndexFile)) {
                $logger = StaticContainer::get(LoggerInterface::class);
                $logger->warning("NOTE: Plugin {plugin} has a vue folder but no webpack config, cannot build it.", ['plugin' => $plugin]);
                continue;
            }

            $pluginsWithVue[] = $plugin;
        }

        return $pluginsWithVue;
    }

    public static function getVueCliServiceBin()
    {
        return PIWIK_INCLUDE_PATH . "/node_modules/.bin/vue-cli-service";
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
