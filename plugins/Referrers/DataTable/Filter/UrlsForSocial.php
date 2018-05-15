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
use Piwik\Metrics;

class UrlsForSocial extends BaseFilter
{
    /**
     * @var bool
     */
    private $sortRecursive;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     * @param bool $sortRecursive Whether to sort recursive or not
     */
    public function __construct($table, $sortRecursive)
    {
        parent::__construct($table);

        $this->sortRecursive = $sortRecursive;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        // make url labels clickable
        $table->filter('ColumnCallbackAddMetadata', array('label', 'url'));

        // prettify the DataTable
        $table->filter('ColumnCallbackReplace', array('label', 'Piwik\Plugins\Referrers\removeUrlProtocol'));
        $table->queueFilter('ReplaceColumnNames');
    }
}