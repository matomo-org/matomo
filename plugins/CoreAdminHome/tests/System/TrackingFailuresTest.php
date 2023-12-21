<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\System;

use Piwik\Plugins\CoreAdminHome\tests\Fixture\TrackingFailures;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CoreAdminHome
 * @group TrackingFailuresTest
 * @group Plugins
 */
class TrackingFailuresTest extends SystemTestCase
{
    /**
     * @var TrackingFailures
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $params['xmlFieldsToRemove'] = array('date_first_occurred', 'pretty_date_first_occurred', 'request_url');
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = array(
            'CoreAdminHome.getTrackingFailures',
        );

        $apiToTest   = array();
        $apiToTest[] = array($api,
            array(
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

TrackingFailuresTest::$fixture = new TrackingFailures();
