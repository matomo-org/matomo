<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tracker\Action;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\OneVisitWithAbnormalPageviewUrls;

/**
 * Tests the URL normalization.
 *
 * @group Core
 * @group UrlNormalizationTest
 */
class UrlNormalizationTest extends SystemTestCase
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
            'segment'    => 'pageUrl==' . urlencode('example.org/foo/bar2.html'),
        ));
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_pagesSegmentedRef',
            'idSite'     => $idSite,
            'date'       => $dateTime,
            'segment'    => 'referrerUrl==http://www.google.com/search?q=piwik',
        ));
        return $return;
    }

    /**
     * @depends     testApi
     */
    public function testCheckPostConditions()
    {
        $sql = "SELECT count(*) FROM " . Common::prefixTable('log_action');
        $count = Db::get()->fetchOne($sql);
        $expected = 13; // 6 urls + 7 titles
        $this->assertEquals($expected, $count, "only $expected actions expected");

        $sql = "SELECT name, url_prefix FROM " . Common::prefixTable('log_action')
            . " WHERE type = " . Action::TYPE_PAGE_URL
            . " ORDER BY idaction ASC";
        $urls = Db::get()->fetchAll($sql);
        $expected = array(
            array('name' => 'example.org/foo/bar.html', 'url_prefix' => 0),
            array('name' => 'example.org/foo/bar2.html', 'url_prefix' => 3),
            array('name' => 'example.org/foo/bar3.html', 'url_prefix' => 1),
            array('name' => 'my.url/ꟽ碌㒧䊶亄ﶆⅅขκもኸόσशμεޖृ', 'url_prefix' => 1),
            array('name' => 'make.wordpress.org/?emoji=😎l&param=test', 'url_prefix' => 2),
            array('name' => 'example.org/foo/bar4.html', 'url_prefix' => 2),
        );
        $this->assertEquals($expected, $urls, "normalization went wrong");
    }

    public static function getOutputPrefix()
    {
        return 'UrlNormalization';
    }
}

UrlNormalizationTest::$fixture = new OneVisitWithAbnormalPageviewUrls();
