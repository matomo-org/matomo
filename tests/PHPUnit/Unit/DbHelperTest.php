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
     * @dataProvider getMaxExecutionTimeTestData
     */
    public function testAddMaxExecutionTimeHintToQuery($expected, $query, $timeLimit, $schema): void
    {
        Schema::unsetInstance();
        Config::getInstance()->database['schema'] = $schema;
        $result = DbHelper::addMaxExecutionTimeHintToQuery($query, $timeLimit);
        $this->assertEquals($expected, $result);
    }

    public function getMaxExecutionTimeTestData(): iterable
    {
        return [
            // MySql Schema
            ['SELECT /*+ MAX_EXECUTION_TIME(1500) */ * FROM table', 'SELECT * FROM table', 1.5, 'Mysql'],
            ['SELECT /*+ MAX_EXECUTION_TIME(20000) */ column FROM (SELECT * FROM table)', 'SELECT column FROM (SELECT * FROM table)', 20, 'Mysql'],
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

    /**
     * @dataProvider getAddOptimizerHintTestData
     */
    public function testAddOptimizerHint($expected, $query, $hint): void
    {
        $result = DbHelper::addOptimizerHintToQuery($query, $hint);
        $this->assertEquals($expected, $result);
    }

    public function getAddOptimizerHintTestData(): iterable
    {
        yield 'no previous hints' => [
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'SELECT * FROM table',
            'HINT_ONE(1)',
        ];

        yield 'different previous hint' => [
            'SELECT /*+ HINT_TWO(2) HINT_ONE(1) */ * FROM table',
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'HINT_TWO(2)',
        ];

        yield 'comment before previous hint' => [
            'SELECT /* comment */ /*+ HINT_TWO(2) HINT_ONE(1) */ * FROM table',
            'SELECT /* comment */ /*+ HINT_ONE(1) */ * FROM table',
            'HINT_TWO(2)',
        ];

        yield 'comments around previous hint' => [
            'SELECT /* comment */ /*+ HINT_TWO(2) HINT_ONE(1) */ /* comment */ * FROM table',
            'SELECT /* comment */ /*+ HINT_ONE(1) */ /* comment */ * FROM table',
            'HINT_TWO(2)',
        ];

        yield 'multiline query with previous hint' => [
            'SELECT
                    /*+ HINT_TWO(2) HINT_ONE(1) */
                    * FROM table',
            'SELECT
                    /*+ HINT_ONE(1) */
                    * FROM table',
            'HINT_TWO(2)',
        ];

        yield 'multiline comment with previous hint' => [
            'SELECT
                    /*
                      comment
                    */
                    /*+ HINT_TWO(2) HINT_ONE(1) */
                    * FROM table',
            'SELECT
                    /*
                      comment
                    */
                    /*+ HINT_ONE(1) */
                    * FROM table',
            'HINT_TWO(2)',
        ];

        yield 'multiline hint with previous hint' => [
            'SELECT
                    /*+ HINT_TWO(2) HINT_ONE(1) */
                    * FROM table',
            'SELECT
                    /*+
                      HINT_ONE(1)
                    */
                    * FROM table',
            'HINT_TWO(2)',
        ];

        yield 'different previous hint (same start)' => [
            'SELECT /*+ HINT_ONE_OTHER(2) HINT_ONE(1) */ * FROM table',
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'HINT_ONE_OTHER(2)',
        ];

        yield 'different previous hint (same end)' => [
            'SELECT /*+ OTHER_HINT_ONE(2) HINT_ONE(1) */ * FROM table',
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'OTHER_HINT_ONE(2)',
        ];

        yield 'duplicate hint' => [
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'HINT_ONE("different value")',
        ];

        yield 'multiline hint with duplicate hint' => [
            'SELECT /*+
                      HINT_ONE(1)
                      HINT_TWO(2)
                    */ * FROM table',
            'SELECT /*+
                      HINT_ONE(1)
                      HINT_TWO(2)
                    */ * FROM table',
            'HINT_ONE("different value")',
        ];

        yield 'duplicate hint without parenthesis' => [
            'SELECT /*+ STRAIGHT_JOIN */ * FROM table',
            'SELECT /*+ STRAIGHT_JOIN */ * FROM table',
            'STRAIGHT_JOIN',
        ];

        yield 'multiline hint without parenthesis and extra whitespace' => [
            'SELECT /*+
                      STRAIGHT_JOIN    HINT_ONE(1)
                    */ * FROM table',
            'SELECT /*+
                      STRAIGHT_JOIN    HINT_ONE(1)
                    */ * FROM table',
            'STRAIGHT_JOIN',
        ];

        yield 'empty hint comment' => [
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'SELECT /*+*/ * FROM table',
            'HINT_ONE(1)',
        ];

        yield 'regular SQL comment' => [
            'SELECT /*+ HINT_ONE(1) */ /* comment */ * FROM table',
            'SELECT /* comment */ * FROM table',
            'HINT_ONE(1)',
        ];

        yield 'hint without parenthesis' => [
            'SELECT /*+ STRAIGHT_JOIN HINT_ONE(1) */ * FROM table',
            'SELECT /*+ HINT_ONE(1) */ * FROM table',
            'STRAIGHT_JOIN'
        ];

        yield 'not a SELECT query' => [
            'UPDATE table SET value = 1',
            'UPDATE table SET value = 1',
            'HINT_ONE(1)',
        ];

        yield 'only applies to queries starting with a SELECT' => [
            'DELETE FROM table WHERE value in (SELECT value FROM table)',
            'DELETE FROM table WHERE value in (SELECT value FROM table)',
            'HINT_ONE(1)',
        ];

        yield 'hints on multiple levels' => [
            'SELECT /*+ MAX_EXECUTION_TIME(100) */ * FROM (SELECT /*+ HINT_ONE(1) */ value FROM table)',
            'SELECT * FROM (SELECT /*+ HINT_ONE(1) */ value FROM table)',
            'MAX_EXECUTION_TIME(100)',
        ];

        yield 'repeated hint' => [
            'SELECT /*+ MAX_EXECUTION_TIME(100) HINT_ONE(1) */ * FROM (SELECT /*+ HINT_ONE(1) */ value FROM table)',
            'SELECT /*+ HINT_ONE(1) */ * FROM (SELECT /*+ HINT_ONE(1) */ value FROM table)',
            'MAX_EXECUTION_TIME(100)',
        ];

        yield 'hints on multiple levels with comments' => [
            'SELECT /*+ MAX_EXECUTION_TIME(100) */ /* comment */ * FROM (SELECT /*+ HINT_ONE(1) */ /* comment */ value FROM table)',
            'SELECT /* comment */ * FROM (SELECT /*+ HINT_ONE(1) */ /* comment */ value FROM table)',
            'MAX_EXECUTION_TIME(100)',
        ];
    }
}
