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

class UrlsFromWebsiteId extends BaseFilter
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
        // the htmlspecialchars_decode call is for BC for before 1.1
        // as the Referrer URL was previously encoded in the log tables, but is now recorded raw
        $table->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', function ($label) {
            return htmlspecialchars_decode($label);
        }));
        $table->queueFilter('ColumnCallbackReplace', array('label', 'Piwik\Plugins\Referrers\getPathFromUrl'));

        foreach ($table->getRows() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->filter($subtable);
            }
        }
    }
}