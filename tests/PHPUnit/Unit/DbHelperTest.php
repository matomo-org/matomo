<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\DbHelper;

/**
 * Class DbHelperTest
 * @package Piwik\Tests\Unit
 * @group Core
 * @group Core_Unit
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
}
