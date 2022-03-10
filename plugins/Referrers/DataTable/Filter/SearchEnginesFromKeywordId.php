<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Plugins\Referrers\SearchEngine;

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

        $table->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', function ($url) { return SearchEngine::getInstance()->getUrlFromName($url); }));
        $table->queueFilter('MetadataCallbackAddMetadata', array('url', 'logo', function ($url) { return SearchEngine::getInstance()->getLogoFromUrl($url); }));

        // get the keyword and create the URL to the search result page
        $rootRow = $this->firstLevelKeywordTable->getRowFromIdSubDataTable($idSubtable);
        if ($rootRow) {
            $keyword = $rootRow->getColumn('label');
            $table->queueFilter('MetadataCallbackReplace', array('url', function ($url, $keyword) { return SearchEngine::getInstance()->getBackLinkFromUrlAndKeyword($url, $keyword); }, array($keyword)));
            $table->queueFilter(function (DataTable $table) {
                $row = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
                if ($row) {
                    $row->deleteMetadata('url');
                }
            });
        }
    }
}