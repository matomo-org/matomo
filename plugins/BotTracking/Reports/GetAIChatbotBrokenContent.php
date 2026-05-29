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
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\BotTracking\Columns\ContentUrl;
use Piwik\Plugins\BotTracking\Columns\Metrics\PageNotFound404Requests;
use Piwik\Plugins\BotTracking\Columns\Metrics\ServerError5xxRequests;
use Piwik\Plugins\BotTracking\Columns\Metrics\TotalBrokenRequests;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetAIChatbotBrokenContent extends Report
{
    protected function init(): void
    {
        parent::init();

        $this->name              = Piwik::translate('BotTracking_AIChatbotsBrokenContentTitle');
        $this->documentation     = Piwik::translate('BotTracking_AIChatbotsBrokenContentDocumentation');
        $this->categoryId        = 'General_AIAssistants';
        $this->subcategoryId     = 'BotTracking_AIChatbotsContentRequests';
        $this->dimension         = new ContentUrl();
        $this->metrics           = [new TotalBrokenRequests(), new ServerError5xxRequests(), new PageNotFound404Requests()];
        $this->processedMetrics  = [];
        $this->order             = 30;
        $this->defaultSortColumn = Metrics::COLUMN_TOTAL_BROKEN_REQUESTS;
    }

    public function configureView(ViewDataTable $view): void
    {
        parent::configureView($view);

        $view->config->setDefaultColumnsToDisplay(
            [
                'label',
                Metrics::COLUMN_TOTAL_BROKEN_REQUESTS,
                Metrics::COLUMN_PAGE_NOT_FOUND_404_REQUESTS,
                Metrics::COLUMN_SERVER_ERROR_5XX_REQUESTS,
            ],
            false,
            false
        );

        // Disable the "show all columns" toggle: it switches the table to the Visitor Engagement
        // preset, which doesn't match the BotTracking column schema and would render empty data.
        $view->config->show_table_all_columns = false;

        SegmentNotSupportedMessageHelper::addSegmentNotSupportedMessage($view);
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        $widgetsList->addWidgetConfig($factory->createWidget()->setIsWide());
    }
}
