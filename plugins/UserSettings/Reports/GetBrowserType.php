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
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Plugins\UserSettings\Columns\BrowserFamily;

class GetBrowserType extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new BrowserFamily();
        $this->name          = Piwik::translate('UserSettings_WidgetBrowserFamilies');
        $this->documentation = Piwik::translate('UserSettings_WidgetBrowserFamiliesDocumentation', '<br />');
        $this->order = 3;
        $this->widgetTitle  = 'UserSettings_WidgetBrowserFamilies';
    }

    public function getDefaultTypeViewDataTable()
    {
        return Pie::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslation('label', $this->dimension->getName());
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
    }

}
