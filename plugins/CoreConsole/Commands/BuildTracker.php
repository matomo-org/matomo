<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Piwik\Unzip;
use Matomo\Dependencies\Symfony\Component\Console\Input\InputInterface;
use Matomo\Dependencies\Symfony\Component\Console\Input\InputOption;
use Matomo\Dependencies\Symfony\Component\Console\Output\OutputInterface;

class BuildTracker extends ConsoleCommand
{
    const YUI_COMPRESSOR_URL = 'https://github.com/yui/yuicompressor/releases/download/v2.4.8/yuicompressor-2.4.8.zip';

    protected function configure()
    {
        $this->setName('development:build-tracker-js');
        $this->setDescription('Minifies tracker JavaScript for Matomo core or a single plugin.');
        $this->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'The plugin to minify. If not supplied, minifies core tracker JS.');
    }

    public function isEnabled()
    {
        return \Piwik\Development::isEnabled();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugin = $input->getOption('plugin');
        if ($plugin && !Manager::getInstance()->isPluginInFilesystem($plugin)) {
            throw new \InvalidArgumentException("Invalid plugin '$plugin'");
        }

        $this->installYuiCompressorIfNeeded($output);
        $this->compress($plugin, $output);

        return self::SUCCESS;
    }

    private function compress($plugin, OutputInterface $output)
    {
        $output->writeln("Minifying...");

        $jsPath = PIWIK_INCLUDE_PATH . '/js';
        if (!$plugin) {
            $command = "cd $jsPath && sed '/<DEBUG>/,/<\\/DEBUG>/d' < piwik.js | sed 's/eval/replacedEvilString/' | java -jar yuicompressor-2.4.8.jar --type js --line-break 1000 | sed 's/replacedEvilString/eval/' | sed 's/^[/][*]/\\/*!/' > piwik.min.js && cp piwik.min.js ../piwik.js && cp piwik.min.js ../matomo.js";
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln("Command: $command");
            }
            passthru($command);
            return;
        }

        $pluginTrackerJs = PIWIK_INCLUDE_PATH . '/plugins/' . $plugin . '/tracker.js';
        $pluginTrackerMinJs = PIWIK_INCLUDE_PATH . '/plugins/' . $plugin . '/tracker.min.js';

        $command = "cd $jsPath && cat $pluginTrackerJs | java -jar yuicompressor-2.4.8.jar --type js --line-break 1000 | sed 's/^[/][*]/\\/*!/' > $pluginTrackerMinJs";
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("Command: $command");
        }
        passthru($command);

        $output->writeln("Done.");
    }

    private function installYuiCompressorIfNeeded(OutputInterface $output)
    {
        $zipPath = PIWIK_INCLUDE_PATH . '/js/yuicompressor-2.4.8.zip';
        $jarPath = PIWIK_INCLUDE_PATH . '/js/yuicompressor-2.4.8.jar';
        if (is_file($jarPath)) {
            return;
        }

        $output->writeln("Downloading YUI compressor...");
        Http::fetchRemoteFile(self::YUI_COMPRESSOR_URL, $zipPath);

        $unzip = Unzip::factory('PclZip', $zipPath);
        $unzip->extract(PIWIK_INCLUDE_PATH . '/js/');
    }
}