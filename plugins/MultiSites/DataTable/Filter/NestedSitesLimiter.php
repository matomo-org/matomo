<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable;

/**
 * Makes sure to not have any subtables anymore and applies the limit to the flattened table.
 *
 * So if $table is
 * array(
 *    site1
 *    group2
 *        subtable => site3
 *                    site4
 *                    site5
 *    site6
 *    site7
 * )
 *
 * it will format this to
 *
 * array(
 *    site1
 *    group2
 *    site3
 *    site4
 *    site5
 *    site6
 *    site7
 * )
 *
 * and then apply the limit filter.
 *
 * Each group will not count into the limit/offset. This way, if one requests a limit of 50 sites,
 * we make sure to return 50 sites.
 *
 * @param $sites
 * @return array
 */
class NestedSitesLimiter extends BaseFilter
{
    private $offset = 0;
    private $limit  = 0;
    /**
     * @var Row[]
     */
    private $rows   = array();

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table, $offset, $limit)
    {
        parent::__construct($table);
        $this->offset = $offset;
        $this->limit  = $limit;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $numRows = 0;
        $lastGroupFromPreviousPage = null;

        foreach ($table->getRows() as $row) {

            $this->addRowIfNeeded($row, $numRows);
            $numRows++;

            $subtable = $row->getSubtable();
            if ($subtable) {
                if (!$this->hasRows()) {
                    $lastGroupFromPreviousPage = $row;
                }
                foreach ($subtable->getRows() as $subRow) {
                    $this->addRowIfNeeded($subRow, $numRows);
                    $numRows++;
                }
                $row->removeSubtable();
            }

            if ($this->hasNumberOfRequestedRowsFound()) {
                break;
            }
        }

        $this->prependGroupIfFirstSiteBelongsToAGroupButGroupIsMissingInRows($lastGroupFromPreviousPage);

        $table->setRows($this->rows);
    }

    private function hasNumberOfRequestedRowsFound()
    {
        return count($this->rows) >= $this->limit;
    }

    private function hasRows()
    {
        return count($this->rows) !== 0;
    }

    private function addRowIfNeeded(Row $row, $numRows)
    {
        $inOffset = $numRows >= $this->offset;

        if ($inOffset && !$this->hasNumberOfRequestedRowsFound()) {
            $this->rows[] = $row;
        }
    }

    /**
     * @param Row $lastGroupFromPreviousPage
     */
    private function prependGroupIfFirstSiteBelongsToAGroupButGroupIsMissingInRows($lastGroupFromPreviousPage)
    {
        if ($lastGroupFromPreviousPage && !empty($this->rows)) {
            // the result starts with a row that does belong to a group, we make sure to show this group before that site
            $group = reset($this->rows)->getMetadata('group');
            if ($group && $lastGroupFromPreviousPage->getColumn('label') === $group) {
                array_unshift($this->rows, $lastGroupFromPreviousPage);
                // we do not remove the last item as it could result in errors, instead we show $limit+1 entries
            }
        }
    }
}