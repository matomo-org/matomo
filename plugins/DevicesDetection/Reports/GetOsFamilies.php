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
use Piwik\Plugin\ReportsProvider;

class GetOsFamilies extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Os();
        $this->name          = Piwik::translate('DevicesDetection_OperatingSystemFamilies');
        $this->documentation = ''; // TODO
        $this->order = 8;

        $this->subcategoryId = 'DevicesDetection_Software';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->title = $this->name;
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('DevicesDetection', 'getOsVersions'),
        );
    }

}
