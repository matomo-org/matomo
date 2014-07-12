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
use Piwik\Plugins\UserSettings\Columns\Resolution;

class GetResolution extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Resolution();
        $this->name          = Piwik::translate('UserSettings_WidgetResolutions');
        $this->documentation = ''; // TODO
        $this->order = 0;
        $this->widgetTitle  = 'UserSettings_WidgetResolutions';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslation('label', $this->dimension->getName());
    }

}
