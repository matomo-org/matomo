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
 * Executes a callback for each row of a {@link DataTable} and prepends each existing segmentFilter with the
 * given segment.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('PrependSegmentFilter', array('segmentName==segmentValue;'));
 *
 * @api
 */
class PrependSegmentFilter extends PrependValueToMetadata
{
    /**
     * @param DataTable $table
     * @param string $prependSegmentFilter The segment to prepend if a segmentFilter is defined. Make sure to include
     *                                     A condition, eg the segment should end with ';' or ','
     */
    public function __construct($table, $prependSegmentFilter = '')
    {
        parent::__construct($table, 'segmentFilter', $prependSegmentFilter);
    }
}
