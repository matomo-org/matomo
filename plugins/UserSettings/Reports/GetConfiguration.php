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
use Piwik\Plugins\UserSettings\Columns\Configuration;

class GetConfiguration extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Configuration();
        $this->name          = Piwik::translate('UserSettings_WidgetGlobalVisitors');
        $this->documentation = Piwik::translate('UserSettings_WidgetGlobalVisitorsDocumentation', '<br />');
        $this->order = 7;
        $this->widgetTitle  = 'UserSettings_WidgetGlobalVisitors';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 3;
    }

}
