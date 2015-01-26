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
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreHome\Columns\UserId;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->category      = 'VisitsSummary_VisitsSummary';
        $this->name          = Piwik::translate('VisitsSummary_VisitsSummary');
        $this->documentation = ''; // TODO
        $this->processedMetrics = array(
            new BounceRate(),
            new ActionsPerVisit(),
            new AverageTimeOnSite()
        );
        $this->metrics       = array(
            'nb_uniq_visitors',
            'nb_visits',
            'nb_users',
            'nb_actions',
            'max_actions'
        );
        // Used to process metrics, not displayed/used directly
//								'sum_visit_length',
//								'nb_visits_converted',
        $this->order = 1;
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (!empty($infos['idSites']) && !empty($infos['period']) && !empty($infos['date'])) {
            $userId = new UserId();
            $isUserIdUsed = $userId->isUsedInAtLeastOneSite($infos['idSites'], $infos['period'], $infos['date']);

            if (!$isUserIdUsed) {
                $key = array_search('nb_users', $this->metrics);
                if (false !== $key) {
                    unset($this->metrics[$key]);
                    $this->metrics = array_values($this->metrics);
                }
            };
        }

        parent::configureReportMetadata($availableReports, $infos);
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();

        $metrics['max_actions']      = Piwik::translate('General_ColumnMaxActions');

        return $metrics;
    }

    public function getProcessedMetrics()
    {
        $metrics = parent::getProcessedMetrics();

        $metrics['avg_time_on_site'] = Piwik::translate('General_VisitDuration');

        return $metrics;
    }
}