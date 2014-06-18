<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\UserSettings\Columns\Browser;

class GetBrowser extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Browser();
        $this->name          = Piwik::translate('UserSettings_WidgetBrowsers');
        $this->documentation = Piwik::translate('UserSettings_WidgetBrowsersDocumentation', '<br />');
        $this->order = 1;
        $this->widgetTitle  = 'UserSettings_WidgetBrowsers';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_Browsers');
        $view->config->addTranslation('label', $this->dimension->getName());

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 7;
        }
    }

    public function getRelatedReports()
    {
        return array(
            new GetBrowserVersion()
        );
    }
}
