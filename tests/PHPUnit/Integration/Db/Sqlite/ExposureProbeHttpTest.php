<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Db\Sqlite;

use Piwik\Config;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ExposureProbe;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Probes the test database over real HTTP. The test web server has no deny rules, so it serves the files.
 *
 * @group Core
 * @group Db
 */
class ExposureProbeHttpTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('Probes a SQLite database file');
        }
    }

    public function testFindsTheTestDatabaseServed(): void
    {
        $file = Connection::getDatabaseFile(Config::getInstance()->database);

        $result = (new ExposureProbe())->probe($file, Fixture::getRootUrl());

        $this->assertSame(ExposureProbe::EXPOSED, $result['status'], var_export($result, true));
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][Fixture::getRootUrl() . 'tmp/sqlite-tests/' . basename($file)]);
        $this->assertSame([], glob(dirname($file) . '/matomo-probe-*'), 'the canary is deleted');
    }
}
