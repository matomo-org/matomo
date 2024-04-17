<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers;

/**
 * Tests the method API.getRowEvolution
 *
 * @group RowEvolutionTest
 * @group Plugins
 */
class RowEvolutionTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $idSite2 = self::$fixture->idSite2;
        $dateTime = self::$fixture->dateTime;
        $keywords = self::$fixture->keywords;

        $return = array();

        $config = array(
            'testSuffix'             => '_referrer1',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'      => '2010-02-06,2010-03-06',
                'period'    => 'day',
                'apiModule' => 'Referrers',
                'apiAction' => 'getWebsites',
                'label'     => 'www.referrer2.com',
                'expanded'  => 0
            )
        );

        $return[] = array('API.getRowEvolution', $config);

        // Websites, hierarchical
        $config['testSuffix'] = '_referrer2';
        $referrerLabel = urlencode('www.referrer0.com') . '>' . urlencode('theReferrerPage1.html');
        $config['otherRequestParameters']['label'] = ($referrerLabel);
        $return[] = array('API.getRowEvolution', $config);

        // Websites, multiple labels including one hierarchical
        $config['testSuffix'] = '_referrerMulti1';
        $referrerLabel = $referrerLabel . ',' . urlencode('www.referrer2.com');
        $config['otherRequestParameters']['label'] = ($referrerLabel);
        $return[] = array('API.getRowEvolution', $config);

        // Keywords, label containing > and ,
        $config['otherRequestParameters']['apiAction'] = 'getKeywords';
        $config['testSuffix'] = '_LabelReservedCharacters';
        $keywordsStr = urlencode($keywords[0]) . ',' . urlencode($keywords[1]);
        $config['otherRequestParameters']['label'] = ($keywordsStr);
        $return[] = array('API.getRowEvolution', $config);

        // Keywords, hierarchical
        $config['otherRequestParameters']['apiAction'] = 'getSearchEngines';
        $config['testSuffix'] = '_LabelReservedCharactersHierarchical';
        $keywordsStr = "Google>" . urlencode(strtolower($keywords[0]))
            . ',Google>' . urlencode(strtolower($keywords[1]))
            . ',Google>' . urlencode(strtolower($keywords[2]));
        // Test multiple labels search engines, Google should also have a 'logo' entry
        $config['otherRequestParameters']['label'] = ($keywordsStr . ",Google");
        $config['otherRequestParameters']['filter_limit'] = 1; // should have no effect
        $return[] = array('API.getRowEvolution', $config);

        // Actions > Pages titles, standard label
        $config['testSuffix'] = '_pageTitles';
        $config['periods'] = array('day', 'week');
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getPageTitles';
        $config['otherRequestParameters']['label'] = ('incredible title 0');
        $config['otherRequestParameters']['filter_limit'] = 1; // should have no effect
        $return[] = array('API.getRowEvolution', $config);

        // Actions > Page titles, multiple labels
        $config['testSuffix'] = '_pageTitlesMulti';
        $label = urlencode('incredible title 0') . ',' . urlencode('incredible title 2');
        $config['otherRequestParameters']['label'] = ($label);
        $return[] = array('API.getRowEvolution', $config);

        // standard label, entry page titles
        $config['testSuffix'] = '_entryPageTitles';
        $config['periods'] = 'day';
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getEntryPageTitles';
        $config['otherRequestParameters']['label'] = urlencode('incredible title 0');
        $return[] = array('API.getRowEvolution', $config);

        // Actions > Page URLS, hierarchical label
        $config['testSuffix'] = '_pageUrls';
        $config['periods'] = array('range');
        $config['otherRequestParameters']['date'] = '2010-03-01,2010-03-06';
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getPageUrls';
        $config['otherRequestParameters']['label'] = ('my>dir>' . urlencode('/page3?foo=bar&baz=bar'));
        $return[] = array('API.getRowEvolution', $config);

        // Goals > Visits Until Conversion, idGoal != 0
        $config['testSuffix'] = '_goals_visitsUntilConversion';
        $config['periods'] = array('day');
        $config['idGoal'] = '2';
        $config['otherRequestParameters']['date'] = '2010-02-06,2010-03-06';
        $config['otherRequestParameters']['period'] = 'day';
        $config['otherRequestParameters']['apiModule'] = 'Goals';
        $config['otherRequestParameters']['apiAction'] = 'getVisitsUntilConversion';
        $config['otherRequestParameters']['label'] = ('1 visit, 2 visits');
        $return[] = array('API.getRowEvolution', $config);

        // Goals > Visits Until Conversion, idGoal != 0, without specifying labels
        $config['testSuffix'] = '_goals_visitsUntilConversion_WithoutLabels';
        $config['periods'] = array('day');
        $config['idGoal'] = '2';
        $config['otherRequestParameters']['date'] = '2010-02-06,2010-03-06';
        $config['otherRequestParameters']['period'] = 'day';
        $config['otherRequestParameters']['apiModule'] = 'Goals';
        $config['otherRequestParameters']['apiAction'] = 'getVisitsUntilConversion';
        $config['otherRequestParameters']['label'] = false;
        $config['otherRequestParameters']['filter_limit'] = 2;
        $config['otherRequestParameters']['filter_sort_column'] = 'nb_conversions';
        $return[] = array('API.getRowEvolution', $config);

        // test date range where most recent date has no data (for #3465)
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_multipleDates_lastNoData',
            'periods'                => 'month',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'      => '2010-02-01,2010-04-08',
                'period'    => 'month',
                'apiModule' => 'Referrers',
                'apiAction' => 'getKeywords',
                // no label
            )
        ));

        // test that reports that process row labels are treated correctly
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_processedRowLabel',
            'periods'                => 'day',
            'idSite'                 => $idSite2,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'      => '2010-03-01,2010-03-06',
                'period'    => 'month',
                'apiModule' => 'DevicesDetection',
                'apiAction' => 'getBrowsers',
                'label'     => 'Firefox,Chrome,Opera'
            )
        ));

        // test Row Evolution on Desktop VS Mobile, special "view" report
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_mobileDesktop',
            'periods'                => 'day',
            'idSite'                 => $idSite2,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'      => '2010-03-01,2010-03-06',
                'period'    => 'month',
                'apiModule' => 'DevicesDetection',
                'apiAction' => 'getType',
                'label'     => 'Desktop,Mobile'
            )
        ));

        // test multi row evolution w/ filter_limit to limit all available labels
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_multiWithFilterLimit',
            'periods'                => 'day',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'         => '2010-03-01,2010-03-06',
                'period'       => 'day',
                'apiModule'    => 'Referrers',
                'apiAction'    => 'getWebsites',
                'filter_limit' => 3, // only 3 labels should show up
            )
        ));

        // test multi row evolution when there is no data
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_multiWithNoData',
            'periods'                => 'day',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
            'otherRequestParameters' => array(
                'date'      => '2010-04-01,2010-04-06',
                'period'    => 'day',
                'apiModule' => 'Referrers',
                'apiAction' => 'getWebsites',
                // no label
            )
        ));

        // (non-rowevolution test) test flattener w/ search engines to make sure
        // queued filters are not applied twice
        $return[] = array('Referrers.getSearchEngines', array(
            'testSuffix'             => '_flatFilters',
            'periods'                => 'month',
            'idSite'                 => $idSite,
            'date'                   => '2010-02-01',
            'otherRequestParameters' => array(
                'flat'               => 1,
                'expanded'           => '0'
            )
        ));

        return $return;
    }

    public static function getOutputPrefix()
    {
        return 'RowEvolution';
    }
}

RowEvolutionTest::$fixture = new TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers();
