<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary\Reports;

use Piwik\Piwik;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->category      = 'VisitsSummary_VisitsSummary';
        $this->name          = Piwik::translate('VisitsSummary_VisitsSummary');
        $this->documentation = ''; // TODO
        $this->processedMetrics = false;
        $this->metrics       = array(
            'nb_uniq_visitors',
            'nb_visits',
            'nb_users',
            'nb_actions',
            'nb_actions_per_visit',
            'bounce_rate',
            'avg_time_on_site',
            'max_actions'
        );
        // Used to process metrics, not displayed/used directly
//								'sum_visit_length',
//								'nb_visits_converted',
        $this->order = 1;
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();

        $metrics['avg_time_on_site'] = Piwik::translate('General_VisitDuration');
        $metrics['max_actions']      = Piwik::translate('General_ColumnMaxActions');

        return $metrics;
    }
}
