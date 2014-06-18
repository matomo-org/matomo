<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites\Reports;

use Piwik\Piwik;
use Piwik\Plugins\MultiSites\Columns\Website;

class GetAll extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Website();
        $this->name          = Piwik::translate('General_AllWebsitesDashboard');
        $this->documentation = ''; // TODO
        $this->processedMetrics = false;
        $this->constantRowsCount = false;
        $this->order = 4;
    }
}
