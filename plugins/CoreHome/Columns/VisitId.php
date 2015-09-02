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
 * TODO
 */
class VisitId extends VisitDimension
{
    protected function configureSegments()
    {
        parent::configureSegments();

        $segment = new Segment();
        $segment->setType('dimension');
        $segment->setCategory(Piwik::translate('General_Visit'));
        $segment->setName(Piwik::translate('General_Visit') . " ID");
        $segment->setSegment('visitId');
        $segment->setAcceptedValues('Any integer.');
        $segment->setSqlSegment('log_visit.idvisit');
        $segment->setRequiresAtLeastViewAccess(true);
        $this->addSegment($segment);
    }
}