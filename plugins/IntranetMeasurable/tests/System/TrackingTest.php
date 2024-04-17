<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable\tests\System;

use Piwik\Plugins\IntranetMeasurable\tests\Fixtures\IntranetSitesWithVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group IntranetMeasurable
 * @group TrackingTest
 * @group Plugins
 */
class TrackingTest extends SystemTestCase
{
    /**
     * @var IntranetSitesWithVisits
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
        $api = array(
            'API.get',
        );

        $apiToTest   = array();
        $apiToTest[] = array($api,
            array(
                'idSite'     => self::$fixture->idSite,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'testSuffix' => '_intranet'
            )
        );
        $apiToTest[] = array($api,
            array(
                'idSite'     => self::$fixture->idSiteNotIntranet,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'testSuffix' => '_notIntranet'
            )
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TrackingTest::$fixture = new IntranetSitesWithVisits();
