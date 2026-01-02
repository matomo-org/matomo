<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\BotTracking\Columns\Metrics\Requests;
use Piwik\Plugins\BotTracking\Columns\PageUrl;

class GetPageUrlsForAIAssistant extends Report
{
    protected function init(): void
    {
        parent::init();

        $this->name             = Piwik::translate('BotTracking_AIAssistantsReportTitle');
        $this->categoryId       = 'General_AIAssistants';
        $this->metrics          = [new Requests()];
        $this->processedMetrics = [];
        $this->dimension        = new PageUrl();
        $this->isSubtableReport = true;
    }
}
