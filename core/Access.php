<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Authorization\Authorization;

/**
 * Singleton that manages user access to Piwik resources.
 *
 * To check whether a user has access to a resource, use one of the {@link Piwik Piwik::checkUser...}
 * methods.
 *
 * In Piwik there are four different access levels:
 *
 * - **no access**: Users with this access level cannot view the resource.
 * - **view access**: Users with this access level can view the resource, but cannot modify it.
 * - **admin access**: Users with this access level can view and modify the resource.
 * - **Super User access**: Only the Super User has this access level. It means the user can do
 *                          whatever he/she wants.
 *
 *                          Super user access is required to set some configuration options.
 *                          All other options are specific to the user or to a website.
 *
 * Access is granted per website. Uses with access for a website can view all
 * data associated with that website.
 *
 * This class is a Singleton proxy to \Piwik\Authorization\Authorization
 *
 * @deprecated Use \Piwik\Authorization\Authorization instead
 *
 */
class Access extends Authorization
{
    /**
     * @var Authorization
     */
    private static $instance;

    /**
     * Gets the singleton instance. Creates it if necessary.
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Authorization();

            Piwik::postEvent('Access.createAccessSingleton', array(&self::$instance));
        }
        return self::$instance;
    }

    /**
     * Sets the singleton instance. For testing purposes.
     */
    public static function setSingletonInstance($instance)
    {
        self::$instance = $instance;
    }
}

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * @deprecated Use \Piwik\Authorization\NoAccessException instead
 * @api
 */
class NoAccessException extends \Exception
{
}
