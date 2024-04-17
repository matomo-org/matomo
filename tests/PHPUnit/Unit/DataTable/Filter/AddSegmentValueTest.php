<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group AddSegmentValueTest
 * @group DataTable
 * @group Filter
 */
class AddSegmentValueTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'AddSegmentValue';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->addRow(array('label' => 'http://piwik.org/test'));
        $this->addRow(array('label' => ''));
        $this->addRow(array('label' => 'search+test', 'other' => 'value'));
        $this->addRow(array('label' => 'keyword test'));
        $this->addRow(array('label' => false));
        $this->addRow(array('label' => 'play A movie', 'other' => 'value'));
        $this->addRow(array('nb_visits' => 15));
        $this->addRow(array('label' => 'Piwik'));
    }

    private function addRow($columns)
    {
        $this->table->addRow($this->buildRow($columns));
    }

    private function buildRow($columns)
    {
        return new Row(array(Row::COLUMNS => $columns));
    }

    public function test_filter_shouldCopyTheLabelToMetadata_IfValueIsGiven()
    {
        $this->table->filter($this->filter);

        $segmentValues = $this->table->getRowsMetadata('segmentValue');
        $expected = array(
            'http://piwik.org/test',
            '',
            'search+test',
            'keyword test',
            false,
            'play A movie',
            false,
            'Piwik');
        $this->assertSame($expected, $segmentValues);
    }

    public function test_filter_ShouldIgnoreSummaryRow()
    {
        $summaryRow = $this->buildRow(array('label' => 'my test'));
        $this->table->addSummaryRow($summaryRow);
        $this->table->filter($this->filter);

        $this->assertFalse($summaryRow->getMetadata('segmentValue'));
    }

    public function test_filter_ShouldCallACallbackPassingTheLabel()
    {
        $this->table->filter($this->filter, array(function ($label) {
            if ($label === false) {
                return 'was false';
            }

            return 'piwik_' . $label;
        }));

        $segmentValues = $this->table->getRowsMetadata('segmentValue');
        $expected = array(
            'piwik_http://piwik.org/test',
            'piwik_',
            'piwik_search+test',
            'piwik_keyword test',
            'was false',
            'piwik_play A movie',
            'was false',
            'piwik_Piwik');
        $this->assertSame($expected, $segmentValues);
    }

    public function test_filter_shouldNotGenerateASegmentValueIfReturnValueIsFalse()
    {
        $this->table->filter($this->filter, array(function ($label) {
            if ($label === false) {
                return 'was false';
            }

            return false;
        }));

        $segmentValues = $this->table->getRowsMetadata('segmentValue');
        $expected = array(
            false,
            false,
            false,
            false,
            'was false',
            false,
            'was false',
            false);
        $this->assertSame($expected, $segmentValues);
    }
}
