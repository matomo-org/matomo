<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Plugin;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\PluginEventSubscriberProvider;

/**
 * This class is a singleton wrapper around {@link EventDispatcherInterface}.
 *
 * @see \Piwik\EventDispatcher\EventDispatcher
 */
final class EventDispatcher
{
    protected static $instance;

    /**
     * Returns the singleton instance for the derived class. If the singleton instance
     * has not been created, this method will create it.
     *
     * @return \Piwik\EventDispatcher\EventDispatcher
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = self::createInstance();
        }

        return self::$instance;
    }

    public static function unsetInstance()
    {
        self::$instance = null;
    }

    /**
     * @return \Piwik\EventDispatcher\EventDispatcher
     */
    private static function createInstance()
    {
        // This class creation and configuration should be moved into a DI container later
        $subscriberProvider = new PluginEventSubscriberProvider(PluginManager::getInstance());

        return new \Piwik\EventDispatcher\EventDispatcher($subscriberProvider);
    }

    final private function __construct()
    {
    }

    final private function __clone()
    {
    }
}
