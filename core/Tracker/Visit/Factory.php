<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker\Visit;

use Piwik\Piwik;
use Piwik\Tracker\Visit;
use Piwik\Tracker\VisitInterface;
use Exception;

class Factory
{
    /**
     * Returns the Tracker_Visit object.
     * This method can be overwritten to use a different Tracker_Visit object
     *
     * @throws Exception
     * @return \Piwik\Tracker\Visit
     */
    public static function make()
    {
        $visit = null;

        /**
         * Triggered before a new **visit tracking object** is created. Subscribers to this
         * event can force the use of a custom visit tracking object that extends from
         * {@link Piwik\Tracker\VisitInterface}.
         *
         * @param \Piwik\Tracker\VisitInterface &$visit Initialized to null, but can be set to
         *                                              a new visit object. If it isn't modified
         *                                              Piwik uses the default class.
         */
        Piwik::postEvent('Tracker.makeNewVisitObject', array(&$visit));

        if (!isset($visit)) {
            $visit = new Visit();
        } elseif (!($visit instanceof VisitInterface)) {
            throw new Exception("The Visit object set in the plugin must implement VisitInterface");
        }

        return $visit;
    }
}
