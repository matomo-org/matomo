<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * A filter is applied instantly to a given DataTable and can
 * - remove rows
 * - change columns values (lowercase the strings, truncate, etc.)
 * - add/remove columns or metadata (compute percentage values, add an 'icon' metadata based on the label, etc.)
 * - add/remove/edit sub DataTable associated to some rows
 * - whatever you can imagine
 *
 * The concept is very simple: the filter is given the DataTable
 * and can do whatever is necessary on the data (in the filter() method).
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
abstract class Piwik_DataTable_Filter
{
    /**
     * @var bool
     */
    protected $enableRecursive = false;

    /**
     * @throws Exception
     * @param Piwik_DataTable $table
     */
    public function __construct($table)
    {
        if (!($table instanceof Piwik_DataTable)) {
            throw new Exception("The filter accepts only a Piwik_DataTable object.");
        }
    }

    /**
     * Filters the given data table
     *
     * @param Piwik_DataTable $table
     */
    abstract public function filter($table);

    /**
     * Enables/Disables the recursive mode
     *
     * @param bool $bool
     */
    public function enableRecursive($bool)
    {
        $this->enableRecursive = (bool)$bool;
    }

    /**
     * Filters a subtable
     *
     * @param Piwik_DataTable_Row $row
     * @return mixed
     */
    public function filterSubTable(Piwik_DataTable_Row $row)
    {
        if (!$this->enableRecursive) {
            return;
        }
        if ($row->isSubtableLoaded()) {
            $subTable = Piwik_DataTable_Manager::getInstance()->getTable($row->getIdSubDataTable());
            $this->filter($subTable);
        }
    }
}
