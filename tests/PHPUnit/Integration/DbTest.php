<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DbTest extends IntegrationTestCase
{
    public function test_getColumnNamesFromTable()
    {
        $this->assertColumnNames('access', array('login', 'idsite', 'access'));
        $this->assertColumnNames('option', array('option_name', 'option_value', 'autoload'));
    }

    private function assertColumnNames($tableName, $expectedColumnNames)
    {
        $colmuns = Db::getColumnNamesFromTable(Common::prefixTable($tableName));

        $this->assertEquals($expectedColumnNames, $colmuns);
    }
}
