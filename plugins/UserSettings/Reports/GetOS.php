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
use Piwik\Plugins\UserSettings\Columns\Operatingsystem;

class GetOS extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Operatingsystem();
        $this->name          = Piwik::translate('UserSettings_WidgetOperatingSystems');
        $this->documentation = ''; // TODO
        $this->order = 6;
        $this->widgetTitle  = 'UserSettings_WidgetOperatingSystems';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_OperatingSystems');
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            new GetOSFamily()
        );
    }
}
