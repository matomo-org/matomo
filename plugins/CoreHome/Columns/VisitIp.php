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
 * Dimension for the log_visit.location_ip column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitIp extends VisitDimension
{
    protected function configureSegments()
    {
        parent::configureSegments();

        $segment = new Segment();
        $segment->setType('metric');
        $segment->setCategory(Piwik::translate('General_Visit'));
        $segment->setName('General_VisitorIP');
        $segment->setSegment('visitIp');
        $segment->setAcceptedValues('13.54.122.1. </code>Select IP ranges with notation: <code>visitIp>13.54.122.0;visitIp<13.54.122.255');
        $segment->setSqlSegment('log_visit.location_ip');
        $segment->setSqlFilterValue(array('Piwik\Network\IPUtils', 'stringToBinaryIP'));
        $segment->setRequiresAtLeastViewAccess(true);
        $this->addSegment($segment);
    }
}
