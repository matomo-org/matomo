<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests the method API.getRowEvolution
 */
class Test_Piwik_Integration_RowEvolution extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        RowEvolution
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $idSite2 = self::$fixture->idSite2;
        $today = self::$fixture->today;
        $keywords = self::$fixture->keywords;

        $return = array();

        $config = array(
            'testSuffix'             => '_referrer1',
            'idSite'                 => $idSite,
            'date'                   => $today,
            'otherRequestParameters' => array(
                'date'      => '2010-02-06,2010-03-06',
                'period'    => 'day',
                'apiModule' => 'Referers',
                'apiAction' => 'getWebsites',
                'label'     => 'www.referrer2.com',
                'expanded'  => 0
            )
        );

        $return[] = array('API.getRowEvolution', $config);

        // Websites, hierarchical
        $config['testSuffix'] = '_referrer2';
        $referrerLabel = urlencode('www.referrer0.com') . '>' . urlencode('theReferrerPage1.html');
        $config['otherRequestParameters']['label'] = urlencode($referrerLabel);
        $return[] = array('API.getRowEvolution', $config);

        // Websites, multiple labels including one hierarchical
        $config['testSuffix'] = '_referrerMulti1';
        $referrerLabel = $referrerLabel . ',' . urlencode('www.referrer2.com');
        $config['otherRequestParameters']['label'] = urlencode($referrerLabel);
        $return[] = array('API.getRowEvolution', $config);

        // Keywords, label containing > and ,
        $config['otherRequestParameters']['apiAction'] = 'getKeywords';
        $config['testSuffix'] = '_LabelReservedCharacters';
        $keywordsStr = urlencode($keywords[0]) . ',' . urlencode($keywords[1]);
        $config['otherRequestParameters']['label'] = urlencode($keywordsStr);
        $return[] = array('API.getRowEvolution', $config);

        // Keywords, hierarchical
        $config['otherRequestParameters']['apiAction'] = 'getSearchEngines';
        $config['testSuffix'] = '_LabelReservedCharactersHierarchical';
        $keywordsStr = "Google>" . urlencode(strtolower($keywords[0]))
            . ',Google>' . urlencode(strtolower($keywords[1]))
            . ',Google>' . urlencode(strtolower($keywords[2]));
        // Test multiple labels search engines, Google should also have a 'logo' entry
        $config['otherRequestParameters']['label'] = urlencode($keywordsStr . ",Google");
        $return[] = array('API.getRowEvolution', $config);

        // Actions > Pages titles, standard label
        $config['testSuffix'] = '_pageTitles';
        $config['periods'] = array('day', 'week');
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getPageTitles';
        $config['otherRequestParameters']['label'] = urlencode('incredible title 0');
        $return[] = array('API.getRowEvolution', $config);

        // Actions > Page titles, multiple labels
        $config['testSuffix'] = '_pageTitlesMulti';
        $label = urlencode('incredible title 0') . ',' . urlencode('incredible title 2');
        $config['otherRequestParameters']['label'] = urlencode($label);
        $return[] = array('API.getRowEvolution', $config);

        // Actions > Page URLS, hierarchical label
        $config['testSuffix'] = '_pageUrls';
        $config['periods'] = array('range');
        $config['otherRequestParameters']['date'] = '2010-03-01,2010-03-06';
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getPageUrls';
        $config['otherRequestParameters']['label'] = urlencode('my>dir>' . urlencode('/page3?foo=bar&baz=bar'));
        $return[] = array('API.getRowEvolution', $config);

        // Goals > Visits Until Conversion, idGoal != 0
        $config['testSuffix'] = '_goals_visitsUntilConversion';
        $config['periods'] = array('day');
        $config['otherRequestParameters']['date'] = '2010-02-06,2010-03-06';
        $config['otherRequestParameters']['period'] = 'day';
        $config['otherRequestParameters']['apiModule'] = 'Goals';
        $config['otherRequestParameters']['apiAction'] = 'getVisitsUntilConversion';
        $config['otherRequestParameters']['label'] = urlencode('1 visit, 2 visits');
        $config['otherRequestParameters']['idGoal'] = '2';
        $return[] = array('API.getRowEvolution', $config);

        // Goals > Visits Until Conversion, idGoal != 0, without specifying labels
        $config['testSuffix'] = '_goals_visitsUntilConversion_WithoutLabels';
        $config['periods'] = array('day');
        $config['otherRequestParameters']['date'] = '2010-02-06,2010-03-06';
        $config['otherRequestParameters']['period'] = 'day';
        $config['otherRequestParameters']['apiModule'] = 'Goals';
        $config['otherRequestParameters']['apiAction'] = 'getVisitsUntilConversion';
        $config['otherRequestParameters']['label'] = false;
        $config['otherRequestParameters']['filter_limit'] = 2;
        $config['otherRequestParameters']['filter_sort_column'] = 'nb_conversions';
        $config['otherRequestParameters']['idGoal'] = '2';
        $return[] = array('API.getRowEvolution', $config);

        // test date range where most recent date has no data (for #3465)
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_multipleDates_lastNoData',
            'periods'                => 'month',
            'idSite'                 => $idSite,
            'date'                   => $today,
            'otherRequestParameters' => array(
                'date'      => '2010-02-01,2010-04-08',
                'period'    => 'month',
                'apiModule' => 'Referers',
                'apiAction' => 'getKeywords',
                // no label
            )
        ));

        // test that reports that process row labels are treated correctly
        $return[] = array('API.getRowEvolution', array(
            'testSuffix'             => '_processedRowLabel',
            'periods'                => 'day',
            'idSite'                 => $idSite2,
            'date'                   => $today,
            'otherRequestParameters' => array(
                'date'      => '2010-03-01,2010-03-06',
                'period'    => 'month',
                'apiModule' => 'UserSettings',
                'apiAction' => 'getBrowser',
                'label'     => 'Firefox,Chrome,Opera'
            )

        ));

        return $return;
    }

    public function getOutputPrefix()
    {
        return 'RowEvolution';
    }
}

Test_Piwik_Integration_RowEvolution::$fixture
    = new Test_Piwik_Fixture_TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers();

