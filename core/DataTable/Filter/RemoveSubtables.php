<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

/**
 * Delete all existing subtables from rows.
 *
 * **Basic example usage**
 *
 *     $dataTable->filter('RemoveSubtables');
 *
 * @api
 */
class RemoveSubtables extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will be filtered eventually.
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * See {@link Limit}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $rows = $table->getRows();
        foreach ($rows as $row) {
            $row->removeSubtable();
        }
    }
}
