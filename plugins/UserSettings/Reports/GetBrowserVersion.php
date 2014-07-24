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
use Piwik\Plugins\UserSettings\Columns\BrowserVersion;

class GetBrowserVersion extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserVersion();
        $this->name          = Piwik::translate('UserSettings_WidgetBrowserVersion');
        $this->documentation = ''; // TODO
        $this->order = 2;
        $this->widgetTitle  = 'UserSettings_WidgetBrowserVersion';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_ColumnBrowserVersion');
        $view->config->addTranslation('label', $this->dimension->getName());

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 7;
        }
    }

    public function getRelatedReports()
    {
        return array(
            new GetBrowser()
        );
    }

}
