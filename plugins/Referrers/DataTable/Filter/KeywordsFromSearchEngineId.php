<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable;

class KeywordsFromSearchEngineId extends BaseFilter
{
    /**
     * @var DataTable
     */
    private $firstLevelSearchEnginesTable;

    /**
     * @var int
     */
    private $idSubtable;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table, $firstLevelSearchEnginesTable, $idSubtable = null)
    {
        parent::__construct($table);

        $this->firstLevelSearchEnginesTable = $firstLevelSearchEnginesTable;
        $this->idSubtable = $idSubtable;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $idSubtable  = $this->idSubtable ? : $table->getId();
        $subTableRow = $this->firstLevelSearchEnginesTable->getRowFromIdSubDataTable($idSubtable);

        if (!empty($subTableRow)) {
            $searchEngineUrl = $subTableRow->getMetadata('url');
            $table->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', 'Piwik\Plugins\Referrers\getSearchEngineUrlFromKeywordAndUrl', array($searchEngineUrl)));
            $table->queueFilter(function (DataTable $table) {
                $row = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
                if ($row) {
                    $row->deleteMetadata('url');
                }
            });
        }

        $table->queueFilter('Piwik\Plugins\Referrers\DataTable\Filter\KeywordNotDefined');
    }
}