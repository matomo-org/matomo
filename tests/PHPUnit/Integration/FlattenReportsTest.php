<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * Tests the flattening of reports.
 */
class Test_Piwik_Integration_FlattenReports extends IntegrationTestCase
{
    protected static $dateTime = '2010-03-06 11:22:33';
    protected static $idSite   = 1;

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        FlattenReports
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $return = array();

        // referrers
        $return[] = array(
            'Referers.getWebsites',
            array(
                'idSite'                 => self::$idSite,
                'date'                   => self::$dateTime,
                'otherRequestParameters' => array(
                    'flat'     => '1',
                    'expanded' => '0'
                )
            ));

        // urls
        $return[] = array(
            'Actions.getPageUrls',
            array(
                'idSite'                 => self::$idSite,
                'date'                   => self::$dateTime,
                'otherRequestParameters' => array(
                    'flat'     => '1',
                    'expanded' => '0'
                )
            ));
        $return[] = array(
            'Actions.getPageUrls',
            array(
                'idSite'                 => self::$idSite,
                'date'                   => self::$dateTime,
                'testSuffix'             => '_withAggregate',
                'otherRequestParameters' => array(
                    'flat'                   => '1',
                    'include_aggregate_rows' => '1',
                    'expanded'               => '0'
                )
            ));

        // custom variables for multiple days
        $return[] = array('CustomVariables.getCustomVariables', array(
            'idSite'                 => self::$idSite,
            'date'                   => self::$dateTime,
            'otherRequestParameters' => array(
                'date'                   => '2010-03-06,2010-03-08',
                'flat'                   => '1',
                'include_aggregate_rows' => '1',
                'expanded'               => '0'
            )
        ));

        return $return;
    }

    public function getOutputPrefix()
    {
        return 'FlattenReports';
    }

    protected function setUpWebsitesAndGoals()
    {
        $this->createWebsite(self::$dateTime);
    }

    protected function trackVisits()
    {
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;

        for ($referrerSite = 1; $referrerSite < 4; $referrerSite++) {
            for ($referrerPage = 1; $referrerPage < 3; $referrerPage++) {
                $offset = $referrerSite * 3 + $referrerPage;
                $t      = $this->getTracker($idSite, Piwik_Date::factory($dateTime)->addHour($offset)->getDatetime());
                $t->setUrlReferrer('http://www.referrer' . $referrerSite . '.com/sub/dir/page' . $referrerPage . '.html');
                $t->setCustomVariable(1, 'CustomVarVisit', 'CustomVarValue' . $referrerPage, 'visit');
                for ($page = 0; $page < 3; $page++) {
                    $t->setUrl('http://example.org/dir' . $referrerSite . '/sub/dir/page' . $page . '.html');
                    $t->setCustomVariable(1, 'CustomVarPage', 'CustomVarValue' . $page, 'page');
                    $this->checkResponse($t->doTrackPageView('title'));
                }
            }
        }

        $t = $this->getTracker($idSite, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime());
        $t->setCustomVariable(1, 'CustomVarVisit', 'CustomVarValue1', 'visit');
        $t->setUrl('http://example.org/sub/dir/dir1/page1.html');
        $t->setCustomVariable(1, 'CustomVarPage', 'CustomVarValue1', 'page');
        $this->checkResponse($t->doTrackPageView('title'));
    }
}

