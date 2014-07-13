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
use Piwik\Plugins\UserSettings\Columns\MobilevsDesktop;

class GetMobileVsDesktop extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new MobilevsDesktop();
        $this->name          = Piwik::translate('UserSettings_MobileVsDesktop');
        $this->documentation = ''; // TODO
        $this->constantRowsCount = true;
        $this->order = 9;
        $this->widgetTitle  = 'UserSettings_MobileVsDesktop';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_MobileVsDesktop');
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            new GetWideScreen()
        );
    }

}
