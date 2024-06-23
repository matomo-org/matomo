<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Insights\DataTable\Filter\Insight;

/**
 * @group Insights
 * @group FilterInsightTest
 * @group Unit
 * @group Core
 */
class FilterInsightTest extends BaseUnitTest
{
    /**
     * @var DataTable
     */
    private $currentTable;

    /**
     * @var DataTable
     */
    private $pastTable;

    public function setUp(): void
    {
        $this->currentTable = new DataTable();
        $this->currentTable->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', 'nb_visits' => 120)),
            array(Row::COLUMNS => array('label' => 'val2', 'nb_visits' => 70)),
            array(Row::COLUMNS => array('label' => 'val3', 'nb_visits' => 90)),
            array(Row::COLUMNS => array('label' => 'val4', 'nb_visits' => 99)),
            array(Row::COLUMNS => array('label' => 'val5', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val6', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val7', 'nb_visits' => 134)),
            array(Row::COLUMNS => array('label' => 'val8', 'nb_visits' => 100)),
            array(Row::COLUMNS => array('label' => 'val9', 'nb_visits' => 7)),
            array(Row::COLUMNS => array('label' => 'val10', 'nb_visits' => 89))
        ));

        $this->pastTable = new DataTable();
        $this->pastTable->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', 'nb_visits' => 102)),
            array(Row::COLUMNS => array('label' => 'val102', 'nb_visits' => 29)),
            array(Row::COLUMNS => array('label' => 'val4', 'nb_visits' => 120)),
            array(Row::COLUMNS => array('label' => 'val6', 'nb_visits' => 313)),
            array(Row::COLUMNS => array('label' => 'val109', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val8', 'nb_visits' => 140)),
            array(Row::COLUMNS => array('label' => 'val9', 'nb_visits' => 72)),
            array(Row::COLUMNS => array('label' => 'val107', 'nb_visits' => 415)),
            array(Row::COLUMNS => array('label' => 'val10', 'nb_visits' => 0))
        ));

        $this->table = new DataTable();
    }

    public function testConsiderOnlyMovers()
    {
        $this->applyInsight($considerMovers = true, $considerNew = false, $considerDisappeared = false);

        $this->assertOrder(array('val1', 'val4', 'val6', 'val8', 'val9', 'val10'));
    }

    public function testConsiderOnlyNew()
    {
        $this->applyInsight($considerMovers = false, $considerNew = true, $considerDisappeared = false);

        $this->assertOrder(array('val2', 'val3', 'val5', 'val7'));
    }

    public function testConsiderOnlyDisappeared()
    {
        $this->applyInsight($considerMovers = false, $considerNew = false, $considerDisappeared = true);

        $this->assertOrder(array('val102', 'val109', 'val107'));
    }

    public function testConsiderOnlyNewAndDisappeared()
    {
        $this->applyInsight($considerMovers = false, $considerNew = true, $considerDisappeared = true);

        $this->assertOrder(array('val2', 'val3', 'val5', 'val7', 'val102', 'val109', 'val107'));
    }

    public function testConsiderAll()
    {
        $this->applyInsightConsiderAll();

        $this->assertSame(13, $this->table->getRowsCount());
    }

    public function testShouldDetectCorrectOldAndNewValue()
    {
        $this->applyInsightConsiderAll();

        $values = array(
            array('label' => 'val1', 'value_old' => 102, 'value_new' => 120),
            array('label' => 'val2', 'value_old' => 0, 'value_new' => 70),
            array('label' => 'val3', 'value_old' => 0, 'value_new' => 90),
            array('label' => 'val4', 'value_old' => 120, 'value_new' => 99),
            array('label' => 'val5', 'value_old' => 0, 'value_new' => 0),
            array('label' => 'val6', 'value_old' => 313, 'value_new' => 0),
            array('label' => 'val7', 'value_old' => 0, 'value_new' => 134),
            array('label' => 'val8', 'value_old' => 140, 'value_new' => 100),
            array('label' => 'val9', 'value_old' => 72, 'value_new' => 7),
            array('label' => 'val10', 'value_old' => 0, 'value_new' => 89),
            array('label' => 'val102', 'value_old' => 29, 'value_new' => 0),
            array('label' => 'val109', 'value_old' => 0, 'value_new' => 0),
            array('label' => 'val107', 'value_old' => 415, 'value_new' => 0),
        );

        $this->assertColumnValues($values);
    }

    public function testShouldDetectWhetherColumGrownOrNot()
    {
        $this->applyInsightConsiderAll();

        $values = array(
            array('label' => 'val1', 'grown' => true),
            array('label' => 'val2', 'grown' => true),
            array('label' => 'val3', 'grown' => true),
            array('label' => 'val4', 'grown' => false),
            array('label' => 'val5', 'grown' => true),
            array('label' => 'val6', 'grown' => false),
            array('label' => 'val7', 'grown' => true),
            array('label' => 'val8', 'grown' => false),
            array('label' => 'val9', 'grown' => false),
            array('label' => 'val10', 'grown' => true),
            array('label' => 'val102', 'grown' => false),
            array('label' => 'val109', 'grown' => true),
            array('label' => 'val107', 'grown' => false),
        );

        $this->assertColumnValues($values);
    }

    public function testShouldDetectWhetherRowIsNewMoverOrDisappeared()
    {
        $this->applyInsightConsiderAll();

        $values = array(
            array('label' => 'val1', 'isNew' => false, 'isMover' => true, 'isDisappeared' => false),
            array('label' => 'val2', 'isNew' => true, 'isMover' => false, 'isDisappeared' => false),
            array('label' => 'val3', 'isNew' => true, 'isMover' => false, 'isDisappeared' => false),
            array('label' => 'val4', 'isNew' => false, 'isMover' => true, 'isDisappeared' => false),
            array('label' => 'val5', 'isNew' => true, 'isMover' => false, 'isDisappeared' => false),
            array('label' => 'val6', 'isNew' => false, 'isMover' => true, 'isDisappeared' => false),
            array('label' => 'val7', 'isNew' => true, 'isMover' => false, 'isDisappeared' => false),
            array('label' => 'val8', 'isNew' => false, 'isMover' => true, 'isDisappeared' => false),
            array('label' => 'val9', 'isNew' => false, 'isMover' => true, 'isDisappeared' => false),
            array('label' => 'val10', 'isNew' => false, 'isMover' => true, 'isDisappeared' => false),
            array('label' => 'val102', 'isNew' => false, 'isMover' => false, 'isDisappeared' => true),
            array('label' => 'val109', 'isNew' => false, 'isMover' => false, 'isDisappeared' => true),
            array('label' => 'val107', 'isNew' => false, 'isMover' => false, 'isDisappeared' => true)
        );

        $this->assertColumnValues($values);
    }

    public function testShouldCalculateDifferenceAndGrowthPercentage()
    {
        $this->applyInsightConsiderAll();

        $values = array(
            array('label' => 'val1', 'growth_percent' => '17.6%', 'growth_percent_numeric' => '17.6', 'difference' => 18),
            array('label' => 'val2', 'growth_percent' => '100%', 'growth_percent_numeric' => '100', 'difference' => 70),
            array('label' => 'val3', 'growth_percent' => '100%', 'growth_percent_numeric' => '100', 'difference' => 90),
            array('label' => 'val4', 'growth_percent' => '-17.5%', 'growth_percent_numeric' => '-17.5', 'difference' => -21),
            array('label' => 'val5', 'growth_percent' => '0%', 'growth_percent_numeric' => '0', 'difference' => 0),
            array('label' => 'val6', 'growth_percent' => '-100%', 'growth_percent_numeric' => '-100', 'difference' => -313),
            array('label' => 'val7', 'growth_percent' => '100%', 'growth_percent_numeric' => '100', 'difference' => 134),
            array('label' => 'val8', 'growth_percent' => '-28.6%', 'growth_percent_numeric' => '-28.6', 'difference' => -40),
            array('label' => 'val9', 'growth_percent' => '-90.3%', 'growth_percent_numeric' => '-90.3', 'difference' => -65),
            array('label' => 'val10', 'growth_percent' => '100%', 'growth_percent_numeric' => '100', 'difference' => 89),
            array('label' => 'val102', 'growth_percent' => '-100%', 'growth_percent_numeric' => '-100', 'difference' => -29),
            array('label' => 'val109', 'growth_percent' => '0%', 'growth_percent_numeric' => '0', 'difference' => 0),
            array('label' => 'val107', 'growth_percent' => '-100%', 'growth_percent_numeric' => '-100', 'difference' => -415),
        );

        $this->assertColumnValues($values);
    }

    public function testShouldCalculateImporance()
    {
        $this->applyInsightConsiderAll();

        $values = array(
            array('label' => 'val1', 'importance' => 18),
            array('label' => 'val2', 'importance' => 70),
            array('label' => 'val3', 'importance' => 90),
            array('label' => 'val4', 'importance' => 21),
            array('label' => 'val5', 'importance' => 0),
            array('label' => 'val6', 'importance' => 313),
            array('label' => 'val7', 'importance' => 134),
            array('label' => 'val8', 'importance' => 40),
            array('label' => 'val9', 'importance' => 65),
            array('label' => 'val10', 'importance' => 89),
            array('label' => 'val102', 'importance' => 29),
            array('label' => 'val109', 'importance' => 0),
            array('label' => 'val107', 'importance' => 415),
        );

        $this->assertColumnValues($values);
    }

    private function applyInsight($considerMovers, $considerNew, $considerDisappeared)
    {
        $filter = new Insight($this->table, $this->currentTable, $this->pastTable, 'nb_visits', $considerMovers, $considerNew, $considerDisappeared);
        $filter->filter($this->table);
    }

    private function applyInsightConsiderAll()
    {
        $this->applyInsight($considerMovers = true, $considerNew = true, $considerDisappeared = true);
    }
}
