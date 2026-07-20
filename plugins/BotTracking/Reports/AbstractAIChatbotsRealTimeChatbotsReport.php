<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\BotTracking\Columns\AIChatbotName;
use Piwik\Plugins\BotTracking\Columns\Metrics\Requests;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;

abstract class AbstractAIChatbotsRealTimeChatbotsReport extends AbstractAIChatbotsRealTimeReport
{
    protected function init(): void
    {
        parent::init();

        $this->dimension = new AIChatbotName();
        $this->metrics   = [
            new Requests(),
            Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS,
            Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS,
            Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS,
        ];
    }

    public function configureView(ViewDataTable $view): void
    {
        parent::configureView($view);

        $view->config->setDefaultColumnsToDisplay(
            [
                'label',
                Metrics::COLUMN_REQUESTS,
                Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS,
                Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS,
                Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS,
            ],
            false,
            false
        );
    }

    protected function getReportRowLimit(): int
    {
        return BotRequestsDao::getAIChatbotActivityForDateRangeLimit();
    }
}
