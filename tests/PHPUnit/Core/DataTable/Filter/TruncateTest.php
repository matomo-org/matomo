<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Filter_TruncateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Truncate
     */
    public function testUnrelatedDataTableNotFiltered()
    {
        // remark: this unit test would become invalid and would need to be rewritten if
        // AddSummaryRow filter stops calling getRowsCount() on the DataTable being filtered.
        $mockedDataTable = $this->getMock('Piwik_DataTable', array('getRowsCount'));
        $mockedDataTable->expects($this->never())->method('getRowsCount');

        $dataTableBeingFiltered = new Piwik_DataTable();
        $rowBeingFiltered = new Piwik_DataTable_Row();
        $dataTableBeingFiltered->addRow($rowBeingFiltered);

        // we simulate the fact that the value of Piwik_DataTable_Row::DATATABLE_ASSOCIATED retrieved
        // from the database is in conflict with one of the Piwik_DataTable_Manager managed table identifiers.
        // This is a rare but legitimate case as identifiers are not thoroughly synchronized
        // when the expanded parameter is false.
        $rowBeingFiltered->c[Piwik_DataTable_Row::DATATABLE_ASSOCIATED] = $mockedDataTable->getId();

        $filter = new Piwik_DataTable_Filter_Truncate($dataTableBeingFiltered, 1);
        $filter->filter($dataTableBeingFiltered);
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Truncate
     */
    public function testForInfiniteRecursion()
    {
        $dataTableBeingFiltered = new Piwik_DataTable();

        // remark: this unit test would become invalid and would need to be rewritten if
        // Truncate filter stops calling getIdSubDataTable() on rows associated with a SubDataTable
        $rowBeingFiltered = $this->getMock('Piwik_DataTable_Row', array('getIdSubDataTable'));
        $rowBeingFiltered->expects($this->never())->method('getIdSubDataTable');

        $dataTableBeingFiltered->addRow($rowBeingFiltered);

        // we simulate a legitimate but rare circular reference between a Piwik_DataTable_Row and its
        // enclosing Piwik_DataTable.
        // This can happen because identifiers are not thoroughly synchronized when the expanded parameter
        // is false.
        $rowBeingFiltered->c[Piwik_DataTable_Row::DATATABLE_ASSOCIATED] = $dataTableBeingFiltered->getId();

        $filter = new Piwik_DataTable_Filter_Truncate($dataTableBeingFiltered, 1);
        $filter->filter($dataTableBeingFiltered);
    }
}
