<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Integration\Tracker\Db;

use Piwik\Config;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;

/**
 * Tracker DB test
 *
 * @group Core
 * @group TrackerDbTest
 */
class MysqliTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $config = Config::getInstance();
        $config->database['adapter'] = 'MYSQLI';
    }

    public function testConnectionThrowsOnInvalidCharset(): void
    {
        self::expectException(Tracker\Db\DbException::class);
        self::expectExceptionMessageMatches('/Set Charset failed/');

        $config = Config::getInstance();
        $config->database['collation'] = null;
        $config->database['charset'] = 'something really invalid';

        Tracker\Db::connectPiwikTrackerDb();
    }

    public function testConnectionThrowsOnInvalidConnectionCollation(): void
    {
        self::expectException(Tracker\Db\DbException::class);
        self::expectExceptionMessageMatches('/Set charset\/connection collation failed/');

        $config = Config::getInstance();
        $config->database['collation'] = 'something really invalid';

        Tracker\Db::connectPiwikTrackerDb();
    }
}
