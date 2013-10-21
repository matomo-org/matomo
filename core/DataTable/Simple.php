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
 * A [DataTable](#) where every row has two columns: **label** and **value**.
 * 
 * Simple DataTables are only used to slightly alter the output of some renderers
 * (namely the XML renderer).
 *
 * @package Piwik
 * @subpackage DataTable
 */
class Simple extends DataTable
{
    /**
     * Adds  in the DataTable the array information
     *
     * @param array $array Array containing the rows information
     *                       array(
     *                             'Label row 1' => $value1,
     *                             'Label row 2' => $value2,
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
