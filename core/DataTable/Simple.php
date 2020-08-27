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
 * A {@link Piwik\DataTable} where every row has two columns: **label** and **value**.
 *
 * Simple DataTables are only used to slightly alter the output of some renderers
 * (notably the XML renderer).
 *
 * @api
 */
class Simple extends DataTable
{
    /**
     * Adds rows based on an array mapping label column values to value column
     * values.
     *
     * @param array $array Array containing the rows, eg,
     *
     *                         array(
     *                             'Label row 1' => $value1,
     *                             'Label row 2' => $value2,
     *                         )
     */
    public function addRowsFromArray($array)
    {
        $this->addRowsFromSimpleArray(array($array));
    }
}
