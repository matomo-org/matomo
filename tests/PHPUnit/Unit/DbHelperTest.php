<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

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
    public function testAddMaxExecutionTimeHintToQuery($expected, $query, $timeLimit)
    {
        $result = DbHelper::addMaxExecutionTimeHintToQuery($query, $timeLimit);
        $this->assertEquals($expected, $result);
    }

    public function getTestQueries()
    {
        return [
            ['SELECT  /*+ MAX_EXECUTION_TIME(1500) */  * FROM table', 'SELECT * FROM table', 1.5],
            ['SELECT  /*+ MAX_EXECUTION_TIME(20000) */  column FROM (SELECT * FROM table)', 'SELECT column FROM (SELECT * FROM table)', 20],
            ['SELECT * FROM table', 'SELECT * FROM table', 0],
            ['SELECT /*+ MAX_EXECUTION_TIME(1000) */ * FROM table', 'SELECT /*+ MAX_EXECUTION_TIME(1000) */ * FROM table', 3.5], // should not append/change MAX_EXECUTION_TIME hint if already present
            ['UPDATE table SET column = value', 'UPDATE table SET column = value', 150],
        ];
    }
}
