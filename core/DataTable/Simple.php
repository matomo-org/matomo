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
namespace Piwik\DataTable;

use Piwik\DataTable;

/**
 * The DataTable\Simple is used to provide an easy way to create simple DataGrid.
 * A DataTable\Simple is a DataTable with 2 columns: 'label' and 'value'.
 *
 * It is usually best to return a DataTable\Simple instead of
 * a PHP array (or other custom data structure) in API methods:
 * - the generic filters can be applied automatically (offset, limit, pattern search, sort, etc.)
 * - the renderer can be applied (XML, PHP, HTML, etc.)
 * So you don't have to write specific renderer for your data, it is already available in all the formats supported natively by Piwik.
 *
 * @package Piwik
 * @subpackage DataTable
 */
class Simple extends DataTable
{
    /**
     * Loads (append) in the DataTable the array information
     *
     * @param array $array Array containing the rows information
     *                       array(
     *                             'Label row 1' => Value row 1,
     *                             'Label row 2' => Value row 2,
     *                       )
     */
    public function addRowsFromArray($array)
    {
        $this->addRowsFromSimpleArray(array($array));
    }

    /**
     * Updates the given column with the given value
     *
     * @param string $columnName
     * @param mixed $value
     */
    public function setColumn($columnName, $value)
    {
        $this->getLastRow()->setColumn($columnName, $value);
    }
}
