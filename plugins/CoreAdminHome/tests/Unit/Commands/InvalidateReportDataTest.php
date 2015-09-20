<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Unit\Commands;

use Piwik\Plugins\CoreAdminHome\Commands\InvalidateReportData;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Unit
 */
class InvalidateReportDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InvalidateReportData
     */
    private $command;

    public function setUp()
    {
        parent::setUp();

        $this->command = new InvalidateReportData();
    }

    /**
     * @dataProvider getTestDataForGetPeriodsToInvalidateFor
     */
    public function test_getPeriodsToInvalidateFor_CorrectlyDeterminesDatesForPeriodsToInvalidate(
        $periodTypes, $dateRanges, $cascade, $expected)
    {
        $result = $this->command->getPeriodsToInvalidateFor($periodTypes, $dateRanges, $cascade);
        $this->assertEquals($expected, $result);
    }

    public function getTestDataForGetPeriodsToInvalidateFor()
    {
        return array(

            // testing w/o cascading
            array(
                array('month', 'week'),
                array(array('2015-02-05','2015-03-11'), array('2015-02-05','2015-02-10')),
                false,
                array(
                    'month' => array('2015-02-01','2015-03-01'),
                    'week' => array(
                        '2015-02-02',
                        '2015-02-09',
                        '2015-02-16',
                        '2015-02-23',
                        '2015-03-02',
                        '2015-03-09',
                    ),
                ),
            ),

            // test w/ cascading
            array(
                array('month', 'day'),
                array(array('2015-02-05','2015-02-26')),
                true,
                array(
                    'month' => array('2015-02-01'),
                    'week' => array(
                        '2015-01-26',
                        '2015-02-02',
                        '2015-02-09',
                        '2015-02-16',
                        '2015-02-23',
                    ),
                    'day' => array(
                        '2015-01-26',
                        '2015-01-27',
                        '2015-01-28',
                        '2015-01-29',
                        '2015-01-30',
                        '2015-01-31',
                        '2015-02-01',
                        '2015-02-02',
                        '2015-02-03',
                        '2015-02-04',
                        '2015-02-05',
                        '2015-02-06',
                        '2015-02-07',
                        '2015-02-08',
                        '2015-02-09',
                        '2015-02-10',
                        '2015-02-11',
                        '2015-02-12',
                        '2015-02-13',
                        '2015-02-14',
                        '2015-02-15',
                        '2015-02-16',
                        '2015-02-17',
                        '2015-02-18',
                        '2015-02-19',
                        '2015-02-20',
                        '2015-02-21',
                        '2015-02-22',
                        '2015-02-23',
                        '2015-02-24',
                        '2015-02-25',
                        '2015-02-26',
                        '2015-02-27',
                        '2015-02-28',
                        '2015-03-01',
                    ),
                ),
            ),

        );
    }
}
