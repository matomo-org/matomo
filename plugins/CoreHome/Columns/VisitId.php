<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;

/**
 * Dimension for the log_visit.idvisit column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitId extends VisitDimension
{
    protected $columnName = 'idvisit';
    protected $acceptValues = 'Any integer.';
    protected $category = 'General_Visit';
    protected $name = 'General_VisitId';
    protected $segmentName = 'visitId';
    protected $allowAnonymous = false;

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setType(Segment::TYPE_DIMENSION);
        $this->addSegment($segment);
    }
}