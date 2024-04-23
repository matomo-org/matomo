<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration\Db;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration\Db\DropColumns;

/**
 * @group Core
 * @group Updater
 * @group Migration
 * @group DropColumns
 * @group DropColumnsTest
 */
class DropColumnsTest extends IntegrationTestCase
{
    private $tableName;
    public function setUp(): void
    {
        parent::setUp();

        $this->tableName = 'foobar';
        DbHelper::createTable($this->tableName, 'barbaz VARCHAR(1), foobaz VARCHAR(1), foobaz2 VARCHAR(1)');
    }

    public function tearDown(): void
    {
        Db::exec('DROP TABLE IF EXISTS ' . Common::prefixTable($this->tableName));
        parent::tearDown();
    }

    public function testValidAndInvalidColumnsAndDuplicateColumns()
    {
        $sql = $this->dropColumns(array('barbaz', 'notexstis', 'barbaz'));

        $this->assertQueryWorks($sql, 'ALTER TABLE `foobar` DROP COLUMN `barbaz`;');
    }

    public function testOnlyInvalidColumns()
    {
        $sql = $this->dropColumns(array('notexstis', 'foobar1234'));

        $this->assertQueryWorks($sql, '');
    }

    public function testOnlyOneColumn()
    {
        $sql = $this->dropColumns(array('foobaz'));

        $this->assertQueryWorks($sql, 'ALTER TABLE `foobar` DROP COLUMN `foobaz`;');
    }

    public function testMultipleColumns()
    {
        $sql = $this->dropColumns(array('barbaz', 'notexstis', 'barbaz', 'foobaz'));

        $this->assertQueryWorks($sql, 'ALTER TABLE `foobar` DROP COLUMN `barbaz`, DROP COLUMN `foobaz`;');
    }

    private function assertQueryWorks(DropColumns $dropColumns, $expectedQuery)
    {
        $this->assertSame($dropColumns->__toString(), $expectedQuery);
        $this->assertNull($dropColumns->exec()); // query should be valid
    }

    private function dropColumns($columnNames)
    {
        return new DropColumns(Common::prefixTable($this->tableName), $columnNames);
    }
}
