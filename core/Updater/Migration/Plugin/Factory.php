<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Plugin;

use Piwik\Container\StaticContainer;

/**
 * Provides plugin migrations.
 *
 * @api
 */
class Factory
{
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @ignore
     */
    public function __construct()
    {
        $this->container = StaticContainer::getContainer();
    }

    /**
     * Activates the given plugin during an update.
     *
     * If the plugin is already activated or if any other error occurs it will be ignored.
     *
     * @param string $pluginName
     * @return Activate
     */
    public function activate($pluginName)
    {
        return $this->container->make('Piwik\Updater\Migration\Plugin\Activate', array(
            'pluginName' => $pluginName
        ));
    }

    /**
     * Deactivates the given plugin during an update.
     *
     * If the plugin is already deactivated or if any other error occurs it will be ignored.
     *
     * @param string $pluginName
     * @return Deactivate
     */
    public function deactivate($pluginName)
    {
        return $this->container->make('Piwik\Updater\Migration\Plugin\Deactivate', array(
            'pluginName' => $pluginName
        ));
    }

    /**
     * Uninstalls the given plugin during an update.
     *
     * If the plugin is still active or if any other error occurs it will be ignored.
     *
     * @param string $pluginName
     * @return Uninstall
     */
    public function uninstall($pluginName)
    {
        return $this->container->make('Piwik\Updater\Migration\Plugin\Uninstall', array(
            'pluginName' => $pluginName
        ));
    }
}
