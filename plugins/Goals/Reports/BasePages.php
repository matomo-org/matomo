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
use Piwik\Plugins\Goals\Visualizations\GoalsEntryPages;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

class BasePages extends Base
{

    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->categoryId = 'Pages';
        $this->hasGoalMetrics = true;
        $this->parameters = null;
        $this->orderGoal = 51;
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
