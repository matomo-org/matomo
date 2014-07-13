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
use Piwik\Plugins\UserSettings\Columns\TypeOfScreen;

class GetWideScreen extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new TypeOfScreen();
        $this->name          = Piwik::translate('UserSettings_WidgetWidescreen');
        $this->documentation = ''; // TODO
        $this->order = 5;
        $this->widgetTitle  = 'UserSettings_WidgetWidescreen';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_ColumnTypeOfScreen');
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            new GetMobileVsDesktop()
        );
    }
}
