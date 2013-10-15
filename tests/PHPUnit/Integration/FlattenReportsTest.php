<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests the flattening of reports.
 */
class Test_Piwik_Integration_FlattenReports extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * 
     */
    public function testApi($api, $params)
    {
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
        $return[] = array('CustomVariables.getCustomVariables', array(
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'                   => '2010-03-05,2010-03-08',
                'flat'                   => '1',
                'include_aggregate_rows' => '1',
                'expanded'               => '0'
            )
        ));

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

Test_Piwik_Integration_FlattenReports::$fixture =
    new Test_Piwik_Fixture_ManyVisitsWithSubDirReferrersAndCustomVars();

