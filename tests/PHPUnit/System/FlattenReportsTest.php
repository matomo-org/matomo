<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\EventDispatcher;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Referrers\Reports\GetWebsites;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManyVisitsWithSubDirReferrersAndCustomVars;

/**
 * Tests the flattening of reports.
 *
 * @group FlattenReportsTest
 * @group Core
 */
class FlattenReportsTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        EventDispatcher::getInstance()->addObserver('Report.filterReports', function (&$reports) {
            $newReports = [];
            foreach ($reports as $report) {
                if ($report instanceof GetWebsites) {
                    continue;
                }
                $newReports[] = $report;
            }
            $newReports[] = new DimensionLessReport();
            $reports = $newReports;
        });
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $return = array();

        // referrers
        $return[] = array(
            'Referrers.getWebsites',
            array(
                'idSite'                 => $idSite,
                'date'                   => $dateTime,
                'otherRequestParameters' => array(
                    'flat'     => '1',
                    'expanded' => '0'
                )
            ));

        // urls
        $return[] = array(
            'Actions.getPageUrls',
            array(
                'idSite'                 => $idSite,
                'date'                   => $dateTime,
                'period'                 => 'week',
                'otherRequestParameters' => array(
                    'flat'     => '1',
                    'expanded' => '0'
                )
            ));
        $return[] = array(
            'Actions.getPageUrls',
            array(
                'idSite'                 => $idSite,
                'date'                   => $dateTime,
                'period'                 => 'week',
                'testSuffix'             => '_withAggregate',
                'otherRequestParameters' => array(
                    'flat'                   => '1',
                    'include_aggregate_rows' => '1',
                    'expanded'               => '0'
                )
            ));

        // custom variables for multiple days
        if (Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $return[] = array(
                'CustomVariables.getCustomVariables', array(
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'otherRequestParameters' => array(
                        'date'                   => '2010-03-05,2010-03-08',
                        'flat'                   => '1',
                        'include_aggregate_rows' => '1',
                        'expanded'               => '0'
                    )
                )
            );
        }

        // test expanded=1 w/ idSubtable=X
        $return[] = array('Actions.getPageUrls', array('idSite'                 => $idSite,
                                                       'date'                   => $dateTime,
                                                       'periods'                => array('week'),
                                                       'apiModule'              => 'Actions',
                                                       'apiAction'              => 'getPageUrls',
                                                       'supertableApi'          => 'Actions.getPageUrls',
                                                       'testSuffix'             => '_expandedSubtable',
                                                       'otherRequestParameters' => array('expanded' => '1')));

        // test expanded=1 & depth=1
        $return[] = array('Actions.getPageUrls', array('idSite'                 => $idSite,
                                                       'date'                   => $dateTime,
                                                       'periods'                => array('week'),
                                                       'testSuffix'             => '_expandedWithDepth',
                                                       'otherRequestParameters' => array('expanded' => '1',
                                                                                         'depth' => '1')));

        // test flat=1 w/ filter_pattern_recursive
        $return[] = array('Actions.getPageUrls', array('idSite'                 => $idSite,
                                                       'date'                   => $dateTime,
                                                       'periods'                => array('week'),
                                                       'apiModule'              => 'Actions',
                                                       'apiAction'              => 'getPageUrls',
                                                       'testSuffix'             => '_flatFilterPatternRecursive',
                                                       'otherRequestParameters' => array(
                                                           'flat'                     => '1',
                                                           'expanded'                 => '0',
                                                           'filter_pattern_recursive' => 'dir2/'
                                                       )));

        return $return;
    }

    public static function getOutputPrefix()
    {
        return 'FlattenReports';
    }
}

FlattenReportsTest::$fixture = new ManyVisitsWithSubDirReferrersAndCustomVars();


class DimensionLessReport extends GetWebsites
{
    protected function init()
    {
        parent::init();
        $this->dimension     = null;
        $this->module        = 'Referrers';
        $this->action        = 'GetWebsites';
    }
}
