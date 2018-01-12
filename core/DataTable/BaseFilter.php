<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable;

use Piwik\DataTable;

/**
 * A filter is set of logic that manipulates a DataTable. Existing filters do things like,
 *
 * - add/remove rows
 * - change column values (change string to lowercase, truncate, etc.)
 * - add/remove columns or metadata (compute percentage values, add an 'icon' metadata based on the label, etc.)
 * - add/remove/edit subtable associated with rows
 * - etc.
 *
 * Filters are called with a DataTable instance and extra parameters that are specified
 * in {@link Piwik\DataTable::filter()} and {@link Piwik\DataTable::queueFilter()}.
 *
 * To see examples of Filters look at the existing ones in the Piwik\DataTable\BaseFilter
 * namespace.
 *
 * @api
 */
abstract class BaseFilter
{
    /**
     * @var bool
     */
    protected $enableRecursive = false;

    /**
     * Constructor.
     *
     * @param DataTable $table
     */
    public function __construct(DataTable $table)
    {
        // empty
    }

    /**
     * Manipulates a {@link DataTable} in some way.
     *
     * @param DataTable $table
     */
    abstract public function filter($table);

    /**
     * Enables/Disables recursive filtering. Whether this property is actually used
     * is up to the derived BaseFilter class.
     *
     * @param bool $enable
     */
    public function enableRecursive($enable)
    {
        $this->enableRecursive = (bool)$enable;
    }

    /**
     * Filters a row's subtable, if one exists and is loaded in memory.
     *
     * @param Row $row The row whose subtable should be filter.
     */
    public function filterSubTable(Row $row)
    {
        if (!$this->enableRecursive) {
            return;
        }
        $subTable = $row->getSubtable();
        if ($subTable) {
            $this->filter($subTable);
        }
    }
}
