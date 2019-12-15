<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\ArchiveProcessor\ArchivingStatus;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchivingStatusTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2010-02-03 00:00:00');
        Fixture::createWebsite('2010-02-03 00:00:00');
    }

    public function test_archiveStartedAndArchiveFinished_workflow()
    {
        /** @var ArchivingStatus $archivingStatus */
        $archivingStatus = StaticContainer::get(ArchivingStatus::class);

        $params = new Parameters(new Site(1), Factory::build('month', '2012-02-04'), new Segment('', [1]));
        $lock = $archivingStatus->archiveStarted($params);

        $this->assertNotEmpty($lock);
        $this->assertTrue($lock->isLocked());

        $this->assertEquals([
            [
                'key' => 'Archiving.1.fab0afcc3a068ecd91b14d50f3bc911d.' . getmypid(),
                'expiry_time' => time() + ArchivingStatus::DEFAULT_ARCHIVING_TTL,
            ],
        ], $this->getLockKeysAndTtls());

        $archivingStatus->archiveFinished($lock);

        $this->assertEquals([], $this->getLockKeysAndTtls());
    }

    public function test_getSitesCurrentlyArchiving_returnsAllSitesArchiving()
    {
        /** @var ArchivingStatus $archivingStatus */
        $archivingStatus = StaticContainer::get(ArchivingStatus::class);

        $params = new Parameters(new Site(1), Factory::build('month', '2012-02-04'), new Segment('', [1]));
        $lock1 = $archivingStatus->archiveStarted($params);

        $params = new Parameters(new Site(1), Factory::build('month', '2012-02-04'), new Segment('browserCode==ff', [1]));
        $lock2 = $archivingStatus->archiveStarted($params);

        $params = new Parameters(new Site(2), Factory::build('month', '2012-02-04'), new Segment('', [1]));
        $lock3 = $archivingStatus->archiveStarted($params);

        $this->assertEquals([
            1, 2,
        ], $archivingStatus->getSitesCurrentlyArchiving());

        $archivingStatus->archiveFinished($lock2);
        $archivingStatus->archiveFinished($lock3);

        $this->assertEquals([
            1,
        ], $archivingStatus->getSitesCurrentlyArchiving());
    }

    private function getLockKeysAndTtls()
    {
        return Db::fetchAll("SELECT `key`, expiry_time FROM `" . Common::prefixTable('locks') . '`');
    }
}