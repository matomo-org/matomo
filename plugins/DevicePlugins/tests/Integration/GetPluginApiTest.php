<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicePlugins\tests\Integration;

use Piwik\DataTable;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;
use Piwik\Plugins\DevicePlugins\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DevicePlugins
 * @group Plugins
 */
class GetPluginApiTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::createSuperUser();

        $this->trackVisit(1, '2024-01-16 10:00:00');
        $this->trackVisit(1, '2024-01-17 10:00:00');
        $this->trackVisit(2, '2024-01-16 11:00:00');
        $this->trackVisit(2, '2024-01-17 11:00:00');
    }

    public function testGetPluginShouldSupportMultiSiteAndMultiPeriodRequests(): void
    {
        $result = API::getInstance()->getPlugin('1,2', 'day', '2024-01-16,2024-01-17');

        self::assertInstanceOf(DataTable\Map::class, $result);

        $result->applyQueuedFilters();

        foreach ($result->getDataTables() as $siteTable) {
            self::assertInstanceOf(DataTable\Map::class, $siteTable);

            foreach ($siteTable->getDataTables() as $periodTable) {
                self::assertInstanceOf(DataTable::class, $periodTable);
                self::assertNotEmpty(
                    array_filter(
                        $periodTable->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [],
                        function ($metric) {
                            return $metric instanceof VisitsPercent;
                        }
                    )
                );
                self::assertGreaterThan(0, $periodTable->getRowsCount());
            }
        }
    }

    private function trackVisit(int $idSite, string $dateTime): void
    {
        $tracker = Fixture::getTracker($idSite, $dateTime, true, true);
        $tracker->setIp(sprintf('10.0.0.%d', $idSite));
        $tracker->setPlugins(true, false, true, false, true);
        $tracker->setUrl(sprintf('https://example.org/site-%d', $idSite));
        Fixture::checkResponse($tracker->doTrackPageView('Plugin Report Visit'));
    }
}
