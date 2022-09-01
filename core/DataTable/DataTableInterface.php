<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable;

/**
 * The DataTable Interface
 *
 */
interface DataTableInterface
{
    public function getRowsCount();
    public function queueFilter($className, $parameters = array());
    public function applyQueuedFilters();
    public function filter($className, $parameters = array());
    public function getFirstRow();
    public function __toString();
    public function enableRecursiveSort();
    public function renameColumn($oldName, $newName);
    public function deleteColumns($columns, $deleteRecursiveInSubtables = false);
    public function deleteRow($id);
    public function deleteColumn($name);
    public function getColumn($name);
    public function getColumns();
    public function deleteRowsMetadata($name, $deleteRecursiveInSubtables = false);
}
