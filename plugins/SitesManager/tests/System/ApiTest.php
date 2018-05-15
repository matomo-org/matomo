<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\System;

use Piwik\Plugins\SitesManager\tests\Fixtures\ManySites;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group SitesManager
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManySites
     */
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
        $apiToTest   = array();
        $apiToTest[] = array(array('SitesManager.getPatternMatchSites'),
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'otherRequestParameters' => array('pattern' => 'SiteTest1')
            )
        );
        $apiToTest[] = array(array('SitesManager.getPatternMatchSites'),
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'otherRequestParameters' => array('pattern' => 'SiteTest1', 'limit' => 2),
                'testSuffix' => 'withLimit'
            )
        );
        $apiToTest[] = array(array('SitesManager.getNumWebsitesToDisplayPerPage'),
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'otherRequestParameters' => array('pattern' => 'SiteTest1')
            )
        );
        $apiToTest[] = array(array('SitesManager.getSiteSettings'),
            array(
                'idSite' => 1
            )
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return 'SitesManager';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

ApiTest::$fixture = new ManySites();