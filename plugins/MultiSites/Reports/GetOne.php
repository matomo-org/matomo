<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites\Reports;

use Piwik\Piwik;
use Piwik\Plugins\MultiSites\Columns\Website;

class GetOne extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Website();
        $this->name          = Piwik::translate('General_SingleWebsitesDashboard');
        $this->documentation = Piwik::translate('MultiSites_SingleWebsitesDashboardDocumentation');
        $this->constantRowsCount = false;
        $this->order = 5;
    }

}
