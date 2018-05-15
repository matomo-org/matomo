<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
}
