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

class SearchEnginesFromKeywordId extends BaseFilter
{
    /**
     * @var DataTable
     */
    private $firstLevelKeywordTable;

    /**
     * @var int
     */
    private $idSubtable;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table, $firstLevelKeywordTable, $idSubtable = null)
    {
        parent::__construct($table);

        $this->firstLevelKeywordTable = $firstLevelKeywordTable;
        $this->idSubtable = $idSubtable;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $idSubtable  = $this->idSubtable ? : $table->getId();

        $table->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', 'Piwik\Plugins\Referrers\getSearchEngineUrlFromName'));
        $table->queueFilter('MetadataCallbackAddMetadata', array('url', 'logo', 'Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl'));

        // get the keyword and create the URL to the search result page
        $rootRow = $this->firstLevelKeywordTable->getRowFromIdSubDataTable($idSubtable);
        if ($rootRow) {
            $keyword = $rootRow->getColumn('label');
            $table->queueFilter('MetadataCallbackReplace', array('url', 'Piwik\Plugins\Referrers\getSearchEngineUrlFromUrlAndKeyword', array($keyword)));
            $table->queueFilter(function (DataTable $table) {
                $row = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
                if ($row) {
                    $row->deleteMetadata('url');
                }
            });
        }
    }
}