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
use Piwik\Plugins\Actions\Columns\PageTitle;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

class GetPagesTitles extends Base
{
    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();

        $this->categoryId = 'Pages';
        $this->name = Piwik::translate('Goals_PageTitles');
        $this->documentation = Piwik::translate('Goals_PageTitlesReportDocumentation');
        $this->dimension = new PageTitle();
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

        $view->requestConfig->filter_sort_column = 'nb_visits';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit       = 25;

        $view->config->addTranslations(array('label' => $this->dimension->getName(),
                                             'nb_visits' => Piwik::translate('General_ColumnUniquePageviews')));
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
