<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewDaoRawRequestLogTest
 * @group Plugins
 */
class DaoRawRequestLogTest extends IntegrationTestCase
{
    /**
     * @var RawRequestLog
     */
    private $dao;

    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        $this->dao = new RawRequestLog();
    }

    public function testGetTableCreateDefinitionContainsAllColumns()
    {
        $definition = $this->dao->getTableCreateDefinition();

        foreach (['idrawrequest', 'idsite', 'idvisit', 'idlink_va', 'server_time', 'parameters'] as $column) {
            $this->assertStringContainsString($column, $definition);
        }
    }

    public function testInstallCanBeCalledTwice()
    {
        $this->dao->install();
        $this->dao->install();

        $this->assertNotFalse(Db::fetchOne(
            'SHOW TABLES LIKE "' . Common::prefixTable(RawRequestLog::TABLE) . '"'
        ));
    }

    public function testInsertAndGetForSiteReturnsRowsInInsertionOrder()
    {
        $now = time();
        $this->dao->insert(1, 11, 101, $now - 30, '{"query":{"n":"1"}}');
        $this->dao->insert(1, 12, null, $now - 20, '{"query":{"n":"2"}}');
        $this->dao->insert(2, 13, 103, $now - 10, '{"query":{"n":"other-site"}}');

        $rows = $this->dao->getForSite(1, $now - 3600, 0, 500);

        $this->assertCount(2, $rows);
        $this->assertSame('{"query":{"n":"1"}}', $rows[0]['parameters']);
        $this->assertSame('{"query":{"n":"2"}}', $rows[1]['parameters']);
        $this->assertSame('11', (string) $rows[0]['idvisit']);
        $this->assertSame('101', (string) $rows[0]['idlink_va']);
        $this->assertNull($rows[1]['idlink_va']);
    }

    public function testGetForSiteExcludesRowsOlderThanTheMinimumTimestamp()
    {
        $now = time();
        $this->dao->insert(1, null, null, $now - 3600, '{"query":{"n":"old"}}');
        $this->dao->insert(1, null, null, $now, '{"query":{"n":"new"}}');

        $rows = $this->dao->getForSite(1, $now - 60, 0, 500);

        $this->assertCount(1, $rows);
        $this->assertStringContainsString('new', $rows[0]['parameters']);
    }

    public function testGetForSiteWithMinIdReturnsOnlyStrictlyNewerRows()
    {
        $now = time();
        $this->dao->insert(1, null, null, $now, '{"query":{"n":"1"}}');
        $this->dao->insert(1, null, null, $now, '{"query":{"n":"2"}}');
        $this->dao->insert(1, null, null, $now, '{"query":{"n":"3"}}');

        $all = $this->dao->getForSite(1, $now - 60, 0, 500);
        $cursor = (int) $all[1]['idrawrequest'];

        $rows = $this->dao->getForSite(1, $now - 60, $cursor, 500);

        $this->assertCount(1, $rows);
        $this->assertStringContainsString('"n":"3"', $rows[0]['parameters']);
    }

    public function testDeleteOlderThanKeepsRowsAtTheExactBoundary()
    {
        $cutoff = time() - 600;
        $this->dao->insert(1, null, null, $cutoff - 1, '{"query":{"n":"older"}}');
        $this->dao->insert(1, null, null, $cutoff, '{"query":{"n":"boundary"}}');
        $this->dao->insert(1, null, null, $cutoff + 1, '{"query":{"n":"newer"}}');

        $deleted = $this->dao->deleteOlderThan($cutoff);

        $this->assertSame(1, $deleted);
        $remaining = $this->dao->getForSite(1, 0, 0, 500);
        $this->assertCount(2, $remaining);
    }

    public function testTrimToNewestPerSiteKeepsTheNewestRowsIndependentlyPerSite()
    {
        $now = time();
        for ($i = 0; $i < 5; $i++) {
            $this->dao->insert(1, null, null, $now, '{"query":{"n":"a' . $i . '"}}');
            $this->dao->insert(2, null, null, $now, '{"query":{"n":"b' . $i . '"}}');
        }

        $deleted = $this->dao->trimToNewestPerSite(2);

        $this->assertSame(6, $deleted);
        $site1 = $this->dao->getForSite(1, 0, 0, 500);
        $site2 = $this->dao->getForSite(2, 0, 0, 500);
        $this->assertCount(2, $site1);
        $this->assertCount(2, $site2);
        $this->assertStringContainsString('a3', $site1[0]['parameters']);
        $this->assertStringContainsString('a4', $site1[1]['parameters']);
    }

    public function testTrimToNewestPerSiteDoesNothingWhenUnderTheCap()
    {
        $now = time();
        $this->dao->insert(1, null, null, $now, '{"query":{"n":"only"}}');

        $this->assertSame(0, $this->dao->trimToNewestPerSite(2));
        $this->assertCount(1, $this->dao->getForSite(1, 0, 0, 500));
    }

    public function testGetForSiteWithLimitReturnsTheNewestRowsChronologically()
    {
        $this->dao->install();
        for ($i = 1; $i <= 5; $i++) {
            $this->dao->insert(1, null, null, time(), '{"n":"' . $i . '"}');
        }

        $rows = $this->dao->getForSite(1, 0, 0, 2);

        $this->assertCount(2, $rows);
        $this->assertSame('{"n":"4"}', $rows[0]['parameters']);
        $this->assertSame('{"n":"5"}', $rows[1]['parameters']);
    }

    public function testTrimSiteToNewestOnlyAffectsTheGivenSite()
    {
        $this->dao->install();
        foreach ([1, 1, 1, 2] as $idSite) {
            $this->dao->insert($idSite, null, null, time(), '{}');
        }

        $deleted = $this->dao->trimSiteToNewest(1, 2);

        $this->assertSame(1, $deleted);
        $this->assertCount(2, $this->dao->getForSite(1, 0, 0, 500));
        $this->assertCount(1, $this->dao->getForSite(2, 0, 0, 500));
    }

    public function testGetOptionValueReturnsFalseWhenTheOptionDoesNotExist()
    {
        $this->assertFalse($this->dao->getOptionValue('DebugView.doesNotExist'));
    }

    public function testGetOptionValueReturnsTheStoredValue()
    {
        Option::set('DebugView.testOption', 'stored-value');

        $this->assertSame('stored-value', $this->dao->getOptionValue('DebugView.testOption'));
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
