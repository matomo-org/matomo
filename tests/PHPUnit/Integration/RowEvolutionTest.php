<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * Tests the method API.getRowEvolution
 */
class Test_Piwik_Integration_RowEvolution extends IntegrationTestCase
{
    protected static $today = '2010-03-06 11:22:33';
    protected static $idSite = 1;
    protected static $keywords = array(
        'free > proprietary', // testing a keyword containing >
        'peace "," not war', // testing a keyword containing ,
        'justice )(&^#%$ NOT corruption!',
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

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
        $return = array();

        $config = array(
            'testSuffix'             => '_referrer1',
            'idSite'                 => self::$idSite,
            'date'                   => self::$today,
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
        $config['testSuffix']                      = '_referrer2';
        $referrerLabel                             = urlencode('www.referrer0.com') . '>' . urlencode('theReferrerPage1.html');
        $config['otherRequestParameters']['label'] = urlencode($referrerLabel);
        $return[]                                  = array('API.getRowEvolution', $config);

        // Websites, multiple labels including one hierarchical
        $config['testSuffix']                      = '_referrerMulti1';
        $referrerLabel                             = urlencode($referrerLabel) . ',' . urlencode('www.referrer2.com');
        $config['otherRequestParameters']['label'] = urlencode($referrerLabel);
        $return[]                                  = array('API.getRowEvolution', $config);

        // Keywords, label containing > and ,
        $config['otherRequestParameters']['apiAction'] = 'getKeywords';
        $config['testSuffix']                          = '_LabelReservedCharacters';
        $keywords                                      = urlencode(self::$keywords[0]) . ',' . urlencode(self::$keywords[1]);
        $config['otherRequestParameters']['label']     = urlencode($keywords);
        $return[]                                      = array('API.getRowEvolution', $config);

        // Keywords, hierarchical
        $config['otherRequestParameters']['apiAction'] = 'getSearchEngines';
        $config['testSuffix']                          = '_LabelReservedCharactersHierarchical';
        $keywords                                      = "Google>" . urlencode(strtolower(self::$keywords[0]))
            . ',Google>' . urlencode(strtolower(self::$keywords[1]))
            . ',Google>' . urlencode(strtolower(self::$keywords[2]));
        // Test multiple labels search engines, Google should also have a 'logo' entry
        $config['otherRequestParameters']['label'] = urlencode($keywords . ",Google");
        $return[]                                  = array('API.getRowEvolution', $config);

        // Actions > Pages titles, standard label
        $config['testSuffix']                          = '_pageTitles';
        $config['periods']                             = array('day', 'week');
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getPageTitles';
        $config['otherRequestParameters']['label']     = urlencode('incredible title 0');
        $return[]                                      = array('API.getRowEvolution', $config);

        // Actions > Page titles, multiple labels
        $config['testSuffix']                      = '_pageTitlesMulti';
        $label                                     = urlencode('incredible title 0') . ',' . urlencode('incredible title 2');
        $config['otherRequestParameters']['label'] = urlencode($label);
        $return[]                                  = array('API.getRowEvolution', $config);

        // Actions > Page URLS, hierarchical label
        $config['testSuffix']                          = '_pageUrls';
        $config['periods']                             = array('range');
        $config['otherRequestParameters']['date']      = '2010-03-01,2010-03-06';
        $config['otherRequestParameters']['apiModule'] = 'Actions';
        $config['otherRequestParameters']['apiAction'] = 'getPageUrls';
        $config['otherRequestParameters']['label']     = urlencode('my>dir>' . urlencode('/page3?foo=bar&baz=bar'));
        $return[]                                      = array('API.getRowEvolution', $config);

        // Goals > Visits Until Conversion, idGoal != 0
        $config['testSuffix']                          = '_goals_visitsUntilConversion';
        $config['periods']                             = array('day');
        $config['otherRequestParameters']['date']      = '2010-02-06,2010-03-06';
        $config['otherRequestParameters']['period']    = 'day';
        $config['otherRequestParameters']['apiModule'] = 'Goals';
        $config['otherRequestParameters']['apiAction'] = 'getVisitsUntilConversion';
        $config['otherRequestParameters']['label']     = urlencode('1 visit, 2 visits');
        $config['otherRequestParameters']['idGoal']    = '2';
        $return[]                                      = array('API.getRowEvolution', $config);

        // Goals > Visits Until Conversion, idGoal != 0, without specifying labels
        $config['testSuffix']                                   = '_goals_visitsUntilConversion_WithoutLabels';
        $config['periods']                                      = array('day');
        $config['otherRequestParameters']['date']               = '2010-02-06,2010-03-06';
        $config['otherRequestParameters']['period']             = 'day';
        $config['otherRequestParameters']['apiModule']          = 'Goals';
        $config['otherRequestParameters']['apiAction']          = 'getVisitsUntilConversion';
        $config['otherRequestParameters']['label']              = false;
        $config['otherRequestParameters']['filter_limit']       = 2;
        $config['otherRequestParameters']['filter_sort_column'] = 'nb_conversions';
        $config['otherRequestParameters']['idGoal']             = '2';
        $return[]                                               = array('API.getRowEvolution', $config);

        return $return;
    }

    public function getOutputPrefix()
    {
        return 'RowEvolution';
    }

    protected static function setUpWebsitesAndGoals()
    {
        self::createWebsite('2010-02-01 11:22:33');
		Piwik_Goals_API::getInstance()->addGoal(self::$idSite, 'triggered php', 'manually', '', '');
		Piwik_Goals_API::getInstance()->addGoal(self::$idSite, 'another triggered php', 'manually', '', '', false, false, true);
	}

    protected static function trackVisits()
    {
        $dateTime = self::$today;
        $idSite   = self::$idSite;

        for ($daysIntoPast = 30; $daysIntoPast >= 0; $daysIntoPast--)
        {
            // Visit 1: referrer website + test page views
            $visitDateTime = Piwik_Date::factory($dateTime)->subDay($daysIntoPast)->getDatetime();
            $t             = self::getTracker($idSite, $visitDateTime, $defaultInit = true);
            $t->setUrlReferrer('http://www.referrer' . ($daysIntoPast % 5) . '.com/theReferrerPage' . ($daysIntoPast % 2) . '.html');
            $t->setUrl('http://example.org/my/dir/page' . ($daysIntoPast % 4) . '?foo=bar&baz=bar');
            $t->setForceVisitDateTime($visitDateTime);
            self::checkResponse($t->doTrackPageView('incredible title ' . ($daysIntoPast % 3)));

			// Trigger goal n°1 once
			self::checkResponse($t->doTrackGoal(1));

			// Trigger goal n°2 twice
			self::checkResponse($t->doTrackGoal(2));
			$t->setForceVisitDateTime(Piwik_Date::factory($visitDateTime)->addHour(0.1)->getDatetime());
			self::checkResponse($t->doTrackGoal(2));

            // VISIT 2: search engine
            $t->setForceVisitDateTime(Piwik_Date::factory($visitDateTime)->addHour(3)->getDatetime());
            $t->setUrlReferrer('http://google.com/search?q=' . urlencode(self::$keywords[$daysIntoPast % 3]));
            self::checkResponse($t->doTrackPageView('not an incredible title '));
        }
    }
}

