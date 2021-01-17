<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\DevicesDetection\Columns\OsVersion;
use Piwik\Plugin\ReportsProvider;

class GetOsVersions extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new OsVersion();
        $this->name          = Piwik::translate('DevicesDetection_OperatingSystemVersions');
        $this->documentation = Piwik::translate('DevicesDetection_OperatingSystemVersionsReportDocumentation');
        $this->order = 2;

        $this->subcategoryId = 'DevicesDetection_Software';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->title = $this->name;
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelSystemVersion"));
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('DevicesDetection', 'getOsFamilies'),
        );
    }
}
