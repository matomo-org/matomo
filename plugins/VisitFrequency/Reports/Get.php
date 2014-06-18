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

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->category      = 'General_Visitors';
        $this->name          = Piwik::translate('VisitFrequency_ColumnReturningVisits');
        $this->documentation = ''; // TODO
        $this->metrics       = array('nb_visits_returning', 'nb_actions_returning', 'avg_time_on_site_returning', 'bounce_rate_returning', 'nb_actions_per_visit_returning', 'nb_uniq_visitors_returning');
        $this->processedMetrics = false;
        $this->order = 40;
    }
}
