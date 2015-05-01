<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TypeMobileApp;

class Type extends \Piwik\Plugin\Type
{
    protected $type = 'mobileapp';
    protected $name = 'Mobile App';
    protected $namePlural = 'Mobile Apps';
    protected $management = array(
        'name'         => 'App name',
        'urls'         => 'App identifiers',
        'customFields' => array()
    );
    protected $reports = array(
        'disable' => array('Referrers', 'DevicesDetection.getBrowser*'),
        'enable'  => array(),
        'rename'  => array(
            'VisitorInterest.getNumberOfVisitsByDaysSinceLast' => 'Sessions by days since last session',
        )
    );
    protected $metrics = array(
        'nb_visitors' => 'Users',
        'nb_visitor' => 'User',
        'nb_unique_visitors' => 'Unique Users',
        'nb_visits' => 'Sessions',
        'nb_pageviews' => 'Screens'
    );
}
