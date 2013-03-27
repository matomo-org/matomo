<?php
/**
 * Tests the URL normalization.
 */
class Test_Piwik_Integration_UrlNormalization extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

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
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $return = array();
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_urls',
            'idSite'     => $idSite,
            'date'       => $dateTime,
        ));
        $return[] = array('Actions.getPageTitles', array(
            'testSuffix' => '_titles',
            'idSite'     => $idSite,
            'date'       => $dateTime,
        ));

        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => $idSite,
            'date'       => $dateTime,
            'segment'    => 'pageUrl==https://WWw.example.org/foo/bar2.html',
        ));
        // Testing entryPageUrl  with AND segment
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => $idSite,
            'date'       => $dateTime,
            'segment'    => 'entryPageUrl==http://example.org/foo/bar.html;pageUrl==https://WWw.example.org/foo/bar2.html',
        ));
        // Testing exitPageUrl with AND segment
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => $idSite,
            'date'       => $dateTime,
            'segment'    => 'exitPageUrl==example.org/foo/bar4.html;pageUrl==https://WWw.example.org/foo/bar2.html',
        ));
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmented',
            'idSite'     => $idSite,
            'date'       => $dateTime,
            'segment'    => 'pageUrl==example.org/foo/bar2.html',
        ));
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmentedRef',
            'idSite'     => $idSite,
            'date'       => $dateTime,
            'segment'    => 'referrerUrl==http://www.google.com/search?q=piwik',
        ));
        $return[] = array('Referers.getKeywordsForPageUrl', array(
            'testSuffix'             => '_keywords',
            'idSite'                 => $idSite,
            'date'                   => $dateTime,
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
        $sql = "SELECT count(*) FROM " . Piwik_Common::prefixTable('log_action');
        $count = Zend_Registry::get('db')->fetchOne($sql);
        $expected = 9; // 4 urls + 5 titles
        $this->assertEquals($expected, $count, "only $expected actions expected");

        $sql = "SELECT name, url_prefix FROM " . Piwik_Common::prefixTable('log_action')
            . " WHERE type = " . Piwik_Tracker_Action::TYPE_ACTION_URL
            . " ORDER BY idaction ASC";
        $urls = Zend_Registry::get('db')->fetchAll($sql);
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
}

Test_Piwik_Integration_UrlNormalization::$fixture = new Test_Piwik_Fixture_OneVisitWithAbnormalPageviewUrls();

