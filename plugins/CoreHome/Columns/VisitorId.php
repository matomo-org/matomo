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
 * Dimension for the log_visit.idvisitor column. This column is added in the CREATE TABLE
 * statement, so this dimension exists only to configure a segment.
 */
class VisitorId extends VisitDimension
{
    protected function configureSegments()
    {
        parent::configureSegments();

        $segment = new Segment();
        $segment->setType('dimension');
        $segment->setCategory(Piwik::translate('General_Visit'));
        $segment->setName('General_VisitorID');
        $segment->setSegment('visitorId');
        $segment->setAcceptedValues('34c31e04394bdc63 - any 16 Hexadecimal chars ID, which can be fetched using the Tracking API function getVisitorId()');
        $segment->setSqlSegment('log_visit.idvisitor');
        $segment->setSqlFilterValue(array('Piwik\Common', 'convertVisitorIdToBin'));
        $segment->setRequiresAtLeastViewAccess(true);
        $this->addSegment($segment);
    }
}
