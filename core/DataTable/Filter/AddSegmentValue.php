<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;

/**
 * Executes a filter for each row of a {@link DataTable} and generates a segment filter for each row.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddSegmentValue', array());
 *     $dataTable->filter('AddSegmentValue', array(function ($label) {
 *        $transformedValue = urldecode($transformedValue);
 *        return $transformedValue;
 *    });
 *
 * @api
 */
class AddSegmentValue extends ColumnCallbackAddMetadata
{
    public function __construct($table, $callback = null)
    {
        parent::__construct($table, 'label', 'segmentValue', $callback, null, false);
    }
}
