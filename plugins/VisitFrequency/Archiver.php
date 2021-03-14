<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;

class Archiver extends \Piwik\Plugin\Archiver
{
    public function aggregateDayReport()
    {
        $this->getProcessor()->processDependentArchive('VisitsSummary', VisitFrequencyAPI::NEW_VISITOR_SEGMENT);
        $this->getProcessor()->processDependentArchive('VisitsSummary', VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT);
    }

    public function aggregateMultipleReports()
    {
        $this->getProcessor()->processDependentArchive('VisitsSummary', VisitFrequencyAPI::NEW_VISITOR_SEGMENT);
        $this->getProcessor()->processDependentArchive('VisitsSummary', VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT);
    }
}