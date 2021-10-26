<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\EntryPageUrl;
use Piwik\Plugins\Goals\Archiver;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

class GetPagesEntry extends Base
{
    protected $defaultSortColumn = '';

    protected $processedMetrics = array('nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate', 'conversion_entry_rate');

    protected function init()
    {
        parent::init();

        $this->categoryId = 'Pages';
        $this->parameters = null;
        $this->name = Piwik::translate('Goals_EntryPages');
        $this->documentation = Piwik::translate('Goals_EntryPagesReportDocumentation');
        $this->dimension = new EntryPageUrl();
        $this->metrics = array( 'nb_conversions', 'nb_visits_converted', 'revenue', 'entry_nb_visits');
        $this->hasGoalMetrics = true;
        $this->order = 5;
        $this->orderGoal = 51;
    }

    public function configureView(ViewDataTable $view)
    {

        $view->config->show_exclude_low_population = false;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }

        $view->requestConfig->filter_sort_column = 'entry_nb_visits';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit       = 25;

        $view->config->addTranslations(array('label' => $this->dimension->getName(),
                                             'entry_nb_visits' => Piwik::translate('General_ColumnEntrances')));

    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (null !== $this->getIdSiteFromInfos($infos)) {
            parent::configureReportMetadata($availableReports, $infos);
        }

        $name = $this->name;

        $this->addReportMetadataForEachGoal($availableReports, $infos, function ($goal) use ($name) {
            return $goal['name'] . ' - ' . $name;
        });
    }

}
