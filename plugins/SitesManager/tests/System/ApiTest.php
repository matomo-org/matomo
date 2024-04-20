<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        $apiToTest   = [];
        $apiToTest[] = [['SitesManager.getPatternMatchSites'],
            [
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['day'],
                'otherRequestParameters' => ['pattern' => 'SiteTest1']
            ]
        ];
        $apiToTest[] = [['SitesManager.getPatternMatchSites'],
            [
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['day'],
                'otherRequestParameters' => ['pattern' => 'SiteTest1', 'limit' => 2],
                'testSuffix' => 'withLimit'
            ]
        ];
        $apiToTest[] = [['SitesManager.getNumWebsitesToDisplayPerPage'],
            [
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['day'],
                'otherRequestParameters' => ['pattern' => 'SiteTest1']
            ]
        ];
        $apiToTest[] = [['SitesManager.getSiteSettings'],
            [
                'idSite' => 1
            ]
        ];

        return $apiToTest;
    }

    public function testInstalledBeforeMatomo37()
    {
        $this->setInstallVersion('3.6.0');
        $this->runApiTests(['SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'], [
            'idSite' => 1,
            'testSuffix' => '_prior3_7_0'
        ]);
    }

    public function testInstalledBeforeMatomo37ButForced()
    {
        $this->setInstallVersion('3.6.0');
        $this->runApiTests(['SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'], [
            'idSite' => 1,
            'otherRequestParameters' => ['forceMatomoEndpoint' => 1],
            'testSuffix' => '_prior3_7_0_but_forced'
        ]);
    }

    public function testInstalledAfterMatomo37()
    {
        $this->setInstallVersion('3.7.0');
        $this->runApiTests(['SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'], [
            'idSite' => 1,
            'testSuffix' => '_after3_7_0'
        ]);
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
