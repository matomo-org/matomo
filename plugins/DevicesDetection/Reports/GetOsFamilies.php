<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\DevicesDetection\Columns\Os;

class GetOsFamilies extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Os();
        $this->name          = Piwik::translate('DevicesDetection_OperatingSystemFamilies');
        $this->documentation = ''; // TODO
        $this->order = 3;
        $this->widgetTitle  = 'DevicesDetection_OperatingSystemFamilies';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->title = $this->name;
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("UserSettings_OperatingSystemFamily"));
    }

    public function getRelatedReports()
    {
        return array(
            new GetOsVersions()
        );
    }

}
