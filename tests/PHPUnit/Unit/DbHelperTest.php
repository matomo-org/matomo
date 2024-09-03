<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Config;
use Piwik\Db\Schema;
use Piwik\DbHelper;

/**
 * Class DbHelperTest
 * @group Core
 * @group Core_Unit
 * @group DbHelper
 */
class DbHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getVariousDbNames
     * @param string $dbName
     * @param bool $expectation
     */
    public function testCorrectNames($dbName, $expectation)
    {
        $this->assertSame(DbHelper::isValidDbname($dbName), $expectation);
    }

    public function getVariousDbNames()
    {
        return array(
            'simpleDbName' => array(
                'dbName' => 'FirstPiwikDb',
                'expectation' => true
            ),
            'containsNumbers' => array(
                'dbName' => 'FirstPiw1kDb',
                'expectation' => true
            ),
            'startsWithNumber' => array(
                'dbName' => '1stPiwikDb',
                'expectation' => true
            ),
            'containsAllowedSpecialCharacters' => array(
                'dbName' => 'MyPiwikDb-with.More+compleX_N4M3',
                'expectation' => true
            ),
            'containsSpace' => array(
                'dbName' => '1st PiwikDb',
                'expectation' => false
            ),
            'startWithNonAlphaNumericSign' => array(
                'dbName' => ';FirstPiwikDb',
                'expectation' => false
            ),
        );
    }

    /**
     * @dataProvider getTestQueries
     */
    public function testAddMaxExecutionTimeHintToQuery($expected, $query, $timeLimit, $schema)
    {
        Schema::unsetInstance();
        Config::getInstance()->database['schema'] = $schema;
        $result = DbHelper::addMaxExecutionTimeHintToQuery($query, $timeLimit);
        $this->assertEquals($expected, $result);
    }

    public function getTestQueries()
    {
        return [
            // MySql Schema
            ['SELECT  /*+ MAX_EXECUTION_TIME(1500) */  * FROM table', 'SELECT * FROM table', 1.5, 'Mysql'],
            ['SELECT  /*+ MAX_EXECUTION_TIME(20000) */  column FROM (SELECT * FROM table)', 'SELECT column FROM (SELECT * FROM table)', 20, 'Mysql'],
            ['SELECT * FROM table', 'SELECT * FROM table', 0, 'Mysql'],
            ['SELECT /*+ MAX_EXECUTION_TIME(1000) */ * FROM table', 'SELECT /*+ MAX_EXECUTION_TIME(1000) */ * FROM table', 3.5, 'Mysql'], // should not append/change MAX_EXECUTION_TIME hint if already present
            ['UPDATE table SET column = value', 'UPDATE table SET column = value', 150, 'Mysql'],
            // MariaDB Schema
            ['SET STATEMENT max_statement_time=2 FOR SELECT * FROM table', 'SELECT * FROM table', 1.5, 'Mariadb'],
            ['SET STATEMENT max_statement_time=20 FOR SELECT column FROM (SELECT * FROM table)', 'SELECT column FROM (SELECT * FROM table)', 20, 'Mariadb'],
            ['SELECT * FROM table', 'SELECT * FROM table', 0, 'Mariadb'],
            ['SET STATEMENT max_statement_time=2 FOR SELECT * FROM table', 'SET STATEMENT max_statement_time=2 FOR SELECT * FROM table', 3.5, 'Mariadb'], // should not append/change max_statement_time hint if already present
            ['UPDATE table SET column = value', 'UPDATE table SET column = value', 150, 'Mariadb'],
        ];
    }
}
