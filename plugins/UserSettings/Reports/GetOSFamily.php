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
use Piwik\Plugins\UserSettings\Columns\OperatingsystemFamily;

class GetOSFamily extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new OperatingsystemFamily();
        $this->name          = Piwik::translate('UserSettings_OperatingSystemFamily');
        $this->documentation = ''; // TODO
        $this->order = 8;
        $this->widgetTitle  = 'UserSettings_OperatingSystemFamily';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = $this->name;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            new GetOS()
        );
    }

}
