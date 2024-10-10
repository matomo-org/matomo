<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Commands;

use Piwik\Config as PiwikConfig;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CorePluginsAdmin\PluginInstaller;

/**
 * marketplace:install-or-update-plugin console command
 */
class PluginInstall extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('marketplace:install-or-update-plugin');
        $this->setDescription('Installs or updates a plugin from the Marketplace.');
        $this->addOptionalArgument('plugin', 'The name of the plugin you want to install or update. Multiple plugin names can be specified separated by a space.', null, true);
    }

    protected function doExecute(): int
    {
        PiwikConfig::getInstance()->checkConfigIsWritable();

        $installer = StaticContainer::get(PluginInstaller::class);
        $plugins = $this->getInput()->getArgument('plugin');

        foreach ($plugins as $plugin) {
            $installer->installOrUpdatePluginFromMarketplace($plugin);
            $this->getOutput()->writeln("Installed or updated plugin <info>$plugin</info>");
        }

        return self::SUCCESS;
    }
}
