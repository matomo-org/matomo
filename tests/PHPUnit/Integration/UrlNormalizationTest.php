<?php
/**
 * Tests the URL normalization.
 */
class Test_Piwik_Integration_UrlNormalization extends IntegrationTestCase
{
    protected static $dateTime = '2010-03-06 11:22:33';
    protected static $idSite   = 1;

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
     * @group        UrlNormalization
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $return   = array();
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_urls',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
        ));
        $return[] = array('Actions.getPageTitles', array(
            'testSuffix' => '_titles',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
        ));
        
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
            'segment'    => 'pageUrl==https://WWw.example.org/foo/bar2.html',
        ));
        // Testing entryPageUrl  with AND segment
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
            'segment'    => 'entryPageUrl==http://example.org/foo/bar.html;pageUrl==https://WWw.example.org/foo/bar2.html',
        ));
        // Testing exitPageUrl with AND segment
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
            'segment'    => 'exitPageUrl==example.org/foo/bar4.html;pageUrl==https://WWw.example.org/foo/bar2.html',
        ));
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
            'segment'    => 'pageUrl==example.org/foo/bar2.html',
        ));
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmentedRef',
            'idSite'     => self::$idSite,
            'date'       => self::$dateTime,
            'segment'    => 'referrerUrl==http://www.google.com/search?q=piwik',
        ));
        $return[] = array('Referers.getKeywordsForPageUrl', array(
            'testSuffix'             => '_keywords',
            'idSite'                 => self::$idSite,
            'date'                   => self::$dateTime,
            'otherRequestParameters' => array(
                'url' => 'http://WWW.example.org/foo/bar.html'
            )
        ));
        return $return;
    }

    /**
     * @@depends     testApi
     * @group        Integration
     * @group        UrlNormalization
     */
    public function testCheckPostConditions()
    {
        $sql      = "SELECT count(*) FROM " . Piwik_Common::prefixTable('log_action');
        $count    = Zend_Registry::get('db')->fetchOne($sql);
        $expected = 9; // 4 urls + 5 titles
        $this->assertEquals($expected, $count, "only $expected actions expected");

        $sql      = "SELECT name, url_prefix FROM " . Piwik_Common::prefixTable('log_action')
            . " WHERE type = " . Piwik_Tracker_Action::TYPE_ACTION_URL
            . " ORDER BY idaction ASC";
        $urls     = Zend_Registry::get('db')->fetchAll($sql);
        $expected = array(
            array('name' => 'example.org/foo/bar.html', 'url_prefix' => 0),
            array('name' => 'example.org/foo/bar2.html', 'url_prefix' => 3),
            array('name' => 'example.org/foo/bar3.html', 'url_prefix' => 1),
            array('name' => 'example.org/foo/bar4.html', 'url_prefix' => 2)
        );
        $this->assertEquals($expected, $urls, "normalization went wrong");
    }

    public function getOutputPrefix()
    {
        return 'UrlNormalization';
    }

    protected static function setUpWebsitesAndGoals()
    {
        self::createWebsite(self::$dateTime);
    }

    protected static function trackVisits()
    {
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;
        $t        = self::getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);

        $t->setUrlReferrer('http://www.google.com/search?q=piwik');
        $t->setUrl('http://example.org/foo/bar.html');
        self::checkResponse($t->doTrackPageView('http://incredible.title/'));

        $t->setUrl('https://example.org/foo/bar.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        self::checkResponse($t->doTrackPageView('https://incredible.title/'));

        $t->setUrl('https://wWw.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackPageView('http://www.incredible.title/'));

        $t->setUrl('http://WwW.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackPageView('https://www.incredible.title/'));

        $t->setUrl('http://www.example.org/foo/bar3.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.5)->getDatetime());
        self::checkResponse($t->doTrackPageView('incredible.title/'));

        $t->setUrl('https://example.org/foo/bar4.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.6)->getDatetime());
        self::checkResponse($t->doTrackPageView('incredible.title/'));
    }
}

