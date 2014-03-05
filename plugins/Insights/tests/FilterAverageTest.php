<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Insights\DataTable\Filter\Average;

/**
 * @group Insights
 * @group FilterAverageTest
 * @group Unit
 * @group Core
 */
class FilterAverageTest extends BaseUnitTest
{
    public function setUp()
    {
        $this->table = new DataTable();
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', 'growth' => 22)),
            array(Row::COLUMNS => array('label' => 'val2', 'growth' => 14)),
            array(Row::COLUMNS => array('label' => 'val3', 'growth' => 18)),
            array(Row::COLUMNS => array('label' => 'val4', 'growth' => 20)),
            array(Row::COLUMNS => array('label' => 'val5', 'growth' => 25)),
            array(Row::COLUMNS => array('label' => 'val6', 'growth' => 17)),
            array(Row::COLUMNS => array('label' => 'val7', 'growth' => 0)),
            array(Row::COLUMNS => array('label' => 'val8', 'growth' => 4)),
            array(Row::COLUMNS => array('label' => 'val9', 'growth' => -4)),
            array(Row::COLUMNS => array('label' => 'val10', 'growth' => null)),
            array(Row::COLUMNS => array('label' => 'val11', 'growth' => false)),
        ));
    }

    public function testShouldNotChangeAnythingIfAverageIsZeroOrOne()
    {
        $rowsBefore = $this->table->getRows();

        $this->calculateAverage(0);
        $this->assertSame($rowsBefore, $this->table->getRows());

        $this->calculateAverage(1);
        $this->assertSame($rowsBefore, $this->table->getRows());
    }

    public function testShouldDivideNumericValuesByDivisorAndRound()
    {
        $this->calculateAverage(4);

        $this->assertColumnValues(array(
            array('label' => 'val1', 'growth' => 6),
            array('label' => 'val2', 'growth' => 4),
            array('label' => 'val3', 'growth' => 5),
            array('label' => 'val4', 'growth' => 5),
            array('label' => 'val5', 'growth' => 6),
            array('label' => 'val6', 'growth' => 4),
            array('label' => 'val7', 'growth' => 0),
            array('label' => 'val8', 'growth' => 1),
            array('label' => 'val9', 'growth' => -1),
            array('label' => 'val10', 'growth' => null),
            array('label' => 'val11', 'growth' => false),
        ));
    }

    private function calculateAverage($divisor)
    {
        $filter = new Average($this->table, 'growth', $divisor);
        $filter->filter($this->table);
    }

}
