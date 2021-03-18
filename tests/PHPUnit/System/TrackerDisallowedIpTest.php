<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Config;
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

    public function test_authenticateSuperUserOrAdmin_ShouldWorkWithIpNotOnLoginWhitelist()
    {
        $tracker = Fixture::getTracker(1, '2021-02-02 16:00:00', $defaultInit = true, $useLocalTracker = false);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        Fixture::checkResponse($tracker->doTrackPageView('test'));
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'observers.global' => \DI\add(array(
                array('Environment.bootstrapped', \DI\value(function () {
                    // ensure tracking request uses an IP that is not local or on allow list
                    $_SERVER['REMOTE_ADDR'] = '3.3.3.3';
                }))
            )),
            Config::class => \DI\decorate(function (Config $config) {
                $config->General['login_allowlist_ip'] = ['1.1.1.1'];
                return $config;
            }),
        ];
    }

}