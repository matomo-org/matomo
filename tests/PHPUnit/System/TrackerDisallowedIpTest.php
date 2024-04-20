<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Tracker
 * @group TrackerDisallowedIp
 */
class TrackerDisallowedIpTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-02-04');
        Fixture::createSuperUser(false);
    }

    public function test_authenticatedRequest_ShouldWorkWhenAuthenticationRequired()
    {
        // authentication required due to an older date and custom ip
        $tracker = Fixture::getTracker(1, '2021-02-02 16:00:00', $defaultInit = true, $useLocalTracker = false);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        Fixture::checkResponse($tracker->doTrackPageView('test'));

        $this->assertEquals(1, Db::fetchOne('SELECT count(*) FROM ' . Common::prefixTable('log_visit')));
    }

    public function test_unauthenticatedRequest_ShouldWorkWhenAuthenticationNotRequired()
    {
        $tracker = Fixture::getTracker(1, date('Y-m-d H:i:s'), $defaultInit = false, $useLocalTracker = false);
        Fixture::checkResponse($tracker->doTrackPageView('test'));

        $this->assertEquals(1, Db::fetchOne('SELECT count(*) FROM ' . Common::prefixTable('log_visit')));
    }

    public function test_unauthenticatedRequest_ShouldNotWorkWhenAuthenticationRequired()
    {
        // authentication required due to an older date
        $tracker = Fixture::getTracker(1, '2021-02-02 16:00:00', $defaultInit = false, $useLocalTracker = false);
        Fixture::checkTrackingFailureResponse($tracker->doTrackPageView('test'));

        $this->assertEquals(0, Db::fetchOne('SELECT count(*) FROM ' . Common::prefixTable('log_visit')));
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'observers.global' => \Piwik\DI::add(array(
                array('Environment.bootstrapped', \Piwik\DI::value(function () {
                    // ensure tracking request uses an IP that is not local or on allow list
                    $_SERVER['REMOTE_ADDR'] = '3.3.3.3';
                }))
            )),
            Config::class => \Piwik\DI::decorate(function (Config $config) {
                $config->General['login_allowlist_ip'] = ['1.1.1.1'];
                return $config;
            }),
        ];
    }
}
