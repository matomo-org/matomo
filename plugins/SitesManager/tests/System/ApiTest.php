<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\System;

use Piwik\Db\Schema\Mysql;
use Piwik\Option;
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

    public function test_InstalledBeforeMatomo37()
    {
        $this->setInstallVersion('3.6.0');
        $this->runApiTests(array('SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'), array(
            'idSite' => 1,
            'testSuffix' => '_prior3_7_0'
        ));
    }

    public function test_InstalledBeforeMatomo37ButForced()
    {
        $this->setInstallVersion('3.6.0');
        $this->runApiTests(array('SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'), array(
            'idSite' => 1,
            'otherRequestParameters' => array('forceMatomoEndpoint' => 1),
            'testSuffix' => '_prior3_7_0_but_forced'
        ));
    }

    public function test_InstalledAfterMatomo37()
    {
        $this->setInstallVersion('3.7.0');
        $this->runApiTests(array('SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'), array(
            'idSite' => 1,
            'testSuffix' => '_after3_7_0'
        ));
    }

    private function setInstallVersion($installVersion)
    {
        Option::set(Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION, $installVersion);
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