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
        $this->addNoValueOption('json', null, 'If given, outputs JSON formatted data.');
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

        $verbose = $this->getOutput()->isVerbose();

        $plugins = array_map(function ($plugin) use ($pluginManager, $verbose) {
            $pluginInformation = array(
                "plugin" => $plugin,
                "core" => $pluginManager->isPluginBundledWithCore($plugin),
                "activated" => !$pluginManager->isPluginInFilesystem($plugin) ? null : $pluginManager->isPluginActivated($plugin),
            );
            if ($verbose) {
                $pluginInformation["version"] = $pluginManager->getVersion($plugin);
            }
            return $pluginInformation;
        }, $plugins);

        if ($this->getInput()->getOption('json')) {
            $plugins = array_map(function ($plugin) {
                $plugin["comment"] = !isset($plugin["activated"]) ? 'Plugin not found in filesystem.' : '';
                if (isset($plugin["version"]) && !isset($plugin["activated"])) {
                    $plugin["version"] = '';
                }
                $plugin["activated"] = isset($plugin["activated"]);
                return $plugin;
            }, $plugins);

            // write JSON output
            $this->getOutput()->write(json_encode($plugins));
        } else {
            // Decorate the plugin information
            $plugins = array_map(function ($plugin) {
                $plugin["plugin"] = self::wrapInTag('info', $plugin["plugin"]);
                $plugin["core"] = $plugin["core"] ? 'Core' : 'Optional';
                if (isset($plugin["version"]) && !isset($plugin["activated"])) {
                    $plugin["version"] = '';
                }
                $plugin["activated"] = !isset($plugin["activated"]) ? self::wrapInTag('error', 'Not found') : ($plugin["activated"] ? 'Activated' : self::wrapInTag('comment', 'Not activated'));
                return $plugin;
            }, $plugins);

            // Sort Core plugins first
            uasort($plugins, function ($a, $b) {
                return strcmp($a["core"], $b["core"]);
            });
            if (!$verbose) {
                $this->renderTable(['Plugin', 'Core or optional?', 'Status'], $plugins);
            } else {
                $this->renderTable(['Plugin', 'Core or optional?', 'Status', 'Version'], $plugins);
            }
        }


        return self::SUCCESS;
    }
}
