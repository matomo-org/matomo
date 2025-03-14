<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\DevicesDetection\Columns\ClientType;

class GetClientType extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new ClientType();
        $this->name          = Piwik::translate('DevicesDetection_ClientTypes');
        $this->documentation = Piwik::translate('DevicesDetection_ClientTypeReportDocumentation');
        $this->order         = 15;
        $this->hasGoalMetrics = true;
        $this->subcategoryId = 'DevicesDetection_Software';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
    }
}
