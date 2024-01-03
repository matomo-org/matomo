<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;

/**
 * Same as OneVisitorTwoVisits.test.php, but with cookie support, which incurs some slight changes
 * in the reporting data (more accurate unique visitor count, better referrer tracking for goals, etc.)
 *
 * @group OneVisitorTwoVisitsWithCookieSupportTest
 * @group Core
 */
class OneVisitorTwoVisitsWithCookieSupportTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'DevicesDetection',
            'UserCountry', 'Referrers', 'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome', 'DevicePlugins',
            'Actions', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array('idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime))
        );
    }

    public static function getOutputPrefix()
    {
        return 'OneVisitorTwoVisits_withCookieSupport';
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Config' => \Piwik\DI::decorate(function ($previous) {
                $general = $previous->General;
                $general['action_title_category_delimiter'] = "/";
                $previous->General = $general;
                return $previous;
            }),
        );
    }
}

OneVisitorTwoVisitsWithCookieSupportTest::$fixture = new OneVisitorTwoVisits();
OneVisitorTwoVisitsWithCookieSupportTest::$fixture->useThirdPartyCookies = true;
OneVisitorTwoVisitsWithCookieSupportTest::$fixture->useSiteSearch = true;
