<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PagePerformance\tests\System;

use Piwik\Plugins\PagePerformance\tests\Fixtures\VisitsWithPagePerformanceMetrics;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group PagePerformance
 * @group APITest
 * @group Plugins
 */
class APITest extends SystemTestCase
{
    /**
     * @var VisitsWithPagePerformanceMetrics
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
            'PagePerformance.get',
            'Actions.getPageUrls',
            'Actions.getPageTitles',
        );

        $apiToTest   = array();
        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day', 'month'),
                'testSuffix' => ''
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

APITest::$fixture = new VisitsWithPagePerformanceMetrics();
