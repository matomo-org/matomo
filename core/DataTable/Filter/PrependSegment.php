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

/**
 * Executes a callback for each row of a {@link DataTable} and prepends each existing segment with the
 * given segment.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('PrependSegment', array('segmentName==segmentValue;'));
 *
 * @api
 */
class PrependSegment extends PrependValueToMetadata
{
    /**
     * @param DataTable $table
     * @param string $prependSegment The segment to prepend if a segment is already defined. Make sure to include
     *                               A condition, eg the segment should end with ';' or ','
     */
    public function __construct($table, $prependSegment = '')
    {
        parent::__construct($table, 'segment', $prependSegment);
    }
}
