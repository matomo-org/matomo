<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * plugin:list console command.
 */
class ListPlugins extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:list');
        $this->setDescription('List installed plugins.');
        $this->addOption('filter-plugin', null, InputOption::VALUE_OPTIONAL, 'If given, prints only plugins that contain this term.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginManager = Manager::getInstance();

        $plugins = $pluginManager->getInstalledPluginsName();

        $pluginFilter = $input->getOption('filter-plugin');

        if (!empty($pluginFilter)) {
            $plugins = array_filter($plugins, function ($pluginName) use ($pluginFilter) {
                return strpos($pluginName, $pluginFilter) !== false;
            });
        }

        $plugins = array_map(function ($plugin) use ($pluginManager) {
            return array(
                '<info>' . $plugin . '</info>',
                $pluginManager->isPluginBundledWithCore($plugin) ? 'Core' : 'Optional',
                $pluginManager->isPluginActivated($plugin) ? 'Activated' : '<comment>Not activated</comment>',
            );
        }, $plugins);

        // Sort Core plugins first
        usort($plugins, function ($a, $b) {
            return strcmp($a[1], $b[1]);
        });

        $table = new Table($output);
        $table
            ->setHeaders(array('Plugin', 'Core or optional?', 'Status'))
            ->setRows($plugins)
        ;
        $table->render();
    }
}
