<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable;

class Actions extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $table->filter(function (DataTable $dataTable) {
            foreach ($dataTable->getRows() as $row) {
                $url = $row->getMetadata('url');
                if ($url) {
                    $row->setMetadata('segmentValue', urldecode($url));
                }
            }
        });

        // TODO can we remove this one again?
        $table->queueFilter('GroupBy', array('label', function ($label) {
            return urldecode($label);
        }));

        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->filter($subtable);
            }
        }
    }
}