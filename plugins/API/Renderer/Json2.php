<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API\Renderer;

use Piwik\Piwik;

/**
 * Correct API output renderer for JSON. Includes bug fixes for bugs in the old JSON API
 * format.
 */
class Json2 extends Json
{
    public function renderArray($array)
    {
        $result = parent::renderArray($array);

        // if $array is a simple associative array, remove the JSON root array that is added by renderDataTable
        if (!empty($array)
            && Piwik::isAssociativeArray($array)
            && !Piwik::isMultiDimensionalArray($array)
        ) {
            $result = substr($result, 1, strlen($result) - 2);
        }

        return $result;
    }
}