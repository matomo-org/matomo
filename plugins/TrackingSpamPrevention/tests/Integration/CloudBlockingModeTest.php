<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\DI;
use Piwik\Plugins\TrackingSpamPrevention\BlockedGeoIp;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\VisitExcluded;

/**
 * Covers organisation blocking through the tracker entry point rather than through BlockedGeoIp
 * directly, because the blocking mode is what replaced the `block_clouds` guard there.
 *
 * The bindings live in their own test class: the geolocation double reports an organisation for
 * every IP, which would exclude the visits the other tests in this plugin rely on.
 *
 * @group TrackingSpamPrevention
 * @group CloudBlockingModeTest
 * @group Plugins
 */
class CloudBlockingModeTest extends IntegrationTestCase
{
    // a member of Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, matched case-insensitively
    private const BLOCKED_ORGANISATION = 'Hetzner Online GmbH';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-12-12 01:02:03');
        Fixture::createSuperUser();
    }

    public function provideContainerConfig()
    {
        // an anonymous class is a class of its own, so it cannot read this class's private constant
        $organisation = self::BLOCKED_ORGANISATION;

        return [
            BlockedGeoIp::class => DI::factory(function ($container) use ($organisation) {
                return new class ($container->get(SystemSettings::class), $organisation) extends BlockedGeoIp {
                    private $organisation;

                    public function __construct(SystemSettings $settings, string $organisation)
                    {
                        parent::__construct($settings);
                        $this->organisation = $organisation;
                    }

                    public function detectLocation($ip, $language)
                    {
                        // keep the country the real provider reports for this IP, so that only the
                        // organisation differs from the untouched behaviour
                        return [
                            LocationProvider::COUNTRY_CODE_KEY => 'xx',
                            LocationProvider::ORG_KEY => $this->organisation,
                        ];
                    }
                };
            }),
        ];
    }

    public function testDefaultListModeExcludesAMatchingOrganisation()
    {
        $this->setMode(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST);

        $this->assertTrue($this->makeExcluded()->isExcluded());
    }

    public function testOffModeDoesNotExcludeAMatchingOrganisation()
    {
        $this->setMode(SystemSettings::CLOUD_BLOCKING_OFF);

        $this->assertFalse($this->makeExcluded()->isExcluded());
    }

    public function testCustomListModeExcludesOnlyOrganisationsOnTheCustomList()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->organisationBlockList->setValue(['some other provider']);
        $this->setMode(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST);

        $this->assertFalse($this->makeExcluded()->isExcluded());

        $settings->organisationBlockList->setValue(['hetzner online']);
        Cache::clearCacheGeneral();

        $this->assertTrue($this->makeExcluded()->isExcluded());
    }

    public function testOffModeStillExcludesWhenTheIpIsOnTheBlockList()
    {
        $this->setMode(SystemSettings::CLOUD_BLOCKING_OFF);
        StaticContainer::get(SystemSettings::class)->ipBlockList->setValue(['22.22.22.22/32']);
        Cache::clearCacheGeneral();

        $this->assertTrue($this->makeExcluded()->isExcluded());
    }

    private function setMode(string $mode): void
    {
        StaticContainer::get(SystemSettings::class)->cloudBlockingMode->setValue($mode);
        Cache::clearCacheGeneral();
    }

    private function makeExcluded(): VisitExcluded
    {
        $request = new Request([
            'idsite' => 1,
            'cip' => '22.22.22.22',
            'token_auth' => Fixture::getTokenAuth(),
            'rec' => 1,
        ]);

        return new VisitExcluded($request);
    }
}
