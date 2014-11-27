<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency\Reports;

use Piwik\Piwik;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\VisitFrequency\Columns\Metrics\ReturningMetric;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->category      = 'General_Visitors';
        $this->name          = Piwik::translate('VisitFrequency_ColumnReturningVisits');
        $this->documentation = ''; // TODO
        $this->processedMetrics = array(
            new ReturningMetric(new AverageTimeOnSite()),
            new ReturningMetric(new ActionsPerVisit()),
            new ReturningMetric(new BounceRate())
        );
        $this->metrics       = array(
            'nb_visits_returning',
            'nb_actions_returning',
            'nb_uniq_visitors_returning',
            'nb_users_returning',
            'max_actions_returning'
        );
        $this->order = 40;
    }
}
