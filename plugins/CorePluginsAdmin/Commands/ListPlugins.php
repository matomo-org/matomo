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

/**
 * plugin:list console command.
 */
class ListPlugins extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:list');
        $this->setDescription('List installed plugins.');
        $this->addOptionalValueOption('filter-plugin', null, 'If given, prints only plugins that contain this term.');
    }

    protected function doExecute(): int
    {
        $pluginManager = Manager::getInstance();

        $plugins = $pluginManager->getInstalledPluginsName();

        $pluginFilter = $this->getInput()->getOption('filter-plugin');

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

        $this->renderTable(['Plugin', 'Core or optional?', 'Status'], $plugins);

        return self::SUCCESS;
    }
}
