<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

// empty plugin definition, otherwise plugin won't be installed during test run
class VisitTime extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Live.getAllVisitorDetails' => 'extendVisitorDetails',
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $visitor['visitLocalTime'] = $details['visitor_localtime'];
        $visitor['visitLocalHour'] = date('G', strtotime('2012-12-21 ' . $details['visitor_localtime']));
    }

}
