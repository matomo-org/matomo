<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Exception;
use Piwik\Piwik;

class VisitorFactory
{
    /**
     * Returns Visitor object.
     * This method can be overwritten to use a different Visitor object
     *
     * @param array $visitorRawData
     * @throws \Exception
     * @return \Piwik\Plugins\Live\VisitorInterface
     * @ignore
     */
    public function create(array $visitorRawData = array())
    {
        $visitor = null;

        /**
         * Triggered while visit is filtering in live plugin. Subscribers to this
         * event can force the use of a custom visitor object that extends from
         * {@link Piwik\Plugins\Live\VisitorInterface}.
         *
         * @param \Piwik\Plugins\Live\VisitorInterface &$visitor Initialized to null, but can be set to
         *                                              a new visitor object. If it isn't modified
         *                                              Piwik uses the default class.
         * @param array $visitorRawData Raw data using in Visitor object constructor.
         */
        Piwik::postEvent('Live.makeNewVisitorObject', array(&$visitor, $visitorRawData));

        if (is_null($visitor)) {
            $visitor = new Visitor($visitorRawData);
        } elseif (!($visitor instanceof VisitorInterface)) {
            throw new Exception("The Visitor object set in the plugin must implement VisitorInterface");
        }

        return $visitor;
    }
}
