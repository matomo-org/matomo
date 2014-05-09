<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Common;
use Piwik\Db;

/**
 * Class Core_DbTest
 *
 * @group Core
 */
class Core_DbTest extends DatabaseTestCase
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