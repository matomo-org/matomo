<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\ArchivingMetrics\Timer;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_ArchiveReportsMetricsTimer
 * @group Plugins
 */
class ArchiveReportsMetricsTimerTest extends IntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$fixture->extraPluginsToLoad[] = 'ArchivingMetrics';

        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        Timer::resetInstanceForTests();
    }

    public function testArchiveReportsWritesMetricsOnceAndDoesNotWriteAgainWhenReusingDbArchive(): void
    {
        Fixture::createSuperUser(true);
        $_GET['trigger'] = 'archivephp';

        $idSite = Fixture::createWebsite('2024-01-01 00:00:00');

        $t = Fixture::getTracker($idSite, '2024-01-01 12:00:00');
        $t->setUrl('http://example.com/');
        Fixture::checkResponse($t->doTrackPageView('test'));

        CoreAdminHomeAPI::getInstance()->archiveReports($idSite, 'day', '2024-01-01');
        $this->assertSame(1, $this->getMetricsCount(), 'Expected archiving_metrics to have 1 row after first archiveReports call.');

        CoreAdminHomeAPI::getInstance()->archiveReports($idSite, 'day', '2024-01-01');
        $this->assertSame(1, $this->getMetricsCount(), 'Expected archiving_metrics to still have 1 row after reusing the same DB archive.');
    }

    private function getMetricsCount(): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archiving_metrics'));
    }
}
