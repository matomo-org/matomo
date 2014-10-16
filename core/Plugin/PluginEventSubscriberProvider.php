<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\EventDispatcher\SubscriberProviderInterface;

/**
 * Plugins are event subscribers. This class provides the plugins to the event dispatcher.
 */
class PluginEventSubscriberProvider implements SubscriberProviderInterface
{
    /**
     * @var Manager
     */
    private $pluginManager;

    public function __construct(Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventSubscribers(array $eventSubscribers = array())
    {
        $plugins = empty($eventSubscribers) ? $this->pluginManager->getPluginsLoadedAndActivated() : $eventSubscribers;

        $subscribers = array();

        foreach ($plugins as $plugin) {
            if (is_string($plugin)) {
                $plugin = $this->pluginManager->getLoadedPlugin($plugin);
            }

            if (empty($plugin)) {
                continue; // may happen in unit tests
            }

            $subscribers[] = $plugin;
        }

        return $subscribers;
    }
}
