<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker\Handler;

use Exception;
use Piwik\Piwik;
use Piwik\Tracker\Handler;

class Factory
{
    public static function make()
    {
        $handler = null;

        /**
         * Triggered before a new **handler tracking object** is created. Subscribers to this
         * event can force the use of a custom handler tracking object that extends from
         * {@link Piwik\Tracker\Handler} and customize any tracking behavior.
         *
         * @param \Piwik\Tracker\Handler &$handler Initialized to null, but can be set to
         *                                         a new handler object. If it isn't modified
         *                                         Piwik uses the default class.
         * @ignore This event is not public yet as the Handler API is not really stable yet
         */
        Piwik::postEvent('Tracker.newHandler', array(&$handler));

        if (is_null($handler)) {
            $handler = new Handler();
        } elseif (!($handler instanceof Handler)) {
            throw new Exception("The Handler object set in the plugin must be an instance of Piwik\\Tracker\\Handler");
        }

        return $handler;
    }
}
