<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;

/**
 * Tracker DB test
 *
 * @group Core
 * @group TrackerDbTest
 */
class DbTest extends IntegrationTestCase
{
    public function test_rowCount_whenUpdating_returnsAllMatchedRowsNotOnlyUpdatedRows()
    {
        $db = Tracker::getDatabase();
        // insert one record
        $db->query("INSERT INTO `" . Common::prefixTable('option') . "` VALUES ('rowid', '1', false)");

        // We will now UPDATE this table and check rowCount() value
        $sqlUpdate = "UPDATE `" . Common::prefixTable('option') . "` SET option_value = 2";

        // when no record was updated, return 0
        $result = $db->query($sqlUpdate . " WHERE option_name = 'NOT FOUND'");
        $this->assertSame(0, $db->rowCount($result));

        // when one record was found and updated, returns 1
        $result = $db->query($sqlUpdate . " WHERE option_name = 'rowid'");
        $this->assertSame(1, $db->rowCount($result));

        // when one record was found but NOT actually updated (as values have not changed), we make sure to return 1
        // testing for MYSQLI_CLIENT_FOUND_ROWS and MYSQL_ATTR_FOUND_ROWS
        $result = $db->query($sqlUpdate . " WHERE option_name = 'rowid'");
        $this->assertSame(1, $db->rowCount($result));
    }
}
