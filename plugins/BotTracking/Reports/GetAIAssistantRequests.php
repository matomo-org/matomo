<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\BotTracking\Columns\AIAssistantName;
use Piwik\Plugins\BotTracking\Columns\Metrics\AcquiredVisits;
use Piwik\Plugins\BotTracking\Columns\Metrics\DocumentRequests;
use Piwik\Plugins\BotTracking\Columns\Metrics\PageRequests;
use Piwik\Plugins\BotTracking\Columns\Metrics\Requests;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetAIAssistantRequests extends Report
{
    protected function init(): void
    {
        parent::init();

        $this->name              = Piwik::translate('BotTracking_AIAssistantsReportTitle');
        $this->documentation     = Piwik::translate('BotTracking_AIAssistantsReportDocumentation');
        $this->categoryId        = 'General_AIAssistants';
        $this->subcategoryId     = 'BotTracking_AIBotsOverview';
        $this->dimension         = new AIAssistantName();
        $this->metrics           = [
            new Requests(),
            new PageRequests(),
            new DocumentRequests(),
            new AcquiredVisits(),
        ];
        $this->processedMetrics  = [];
        $this->order             = 30;
        $this->defaultSortColumn = Metrics::COLUMN_ACQUIRED_VISITS;
        if (\Piwik\Request::fromRequest()->getStringParameter('secondaryDimension', '') === 'documents') {
            $this->actionToLoadSubTables = 'getDocumentUrlsForAIAssistant';
        } else {
            $this->actionToLoadSubTables = 'getPageUrlsForAIAssistant';
        }
    }

    public function configureView(ViewDataTable $view): void
    {
        parent::configureView($view);

        $view->config->show_table_all_columns = false;
        $view->config->show_insights          = false;

        // Show segment not supported message when a segment is selected
        if (!empty(Request::getRawSegmentFromRequest())) {
            $message = '<p style="margin-top:2em;margin-bottom:2em" class=" alert-info alert">' . Piwik::translate('BotTracking_SegmentNotSupported') . '</p>';
            $view->config->show_header_message = $message;
        }

        $view->config->setDefaultColumnsToDisplay(
            ['label', Metrics::COLUMN_REQUESTS, Metrics::COLUMN_PAGE_REQUESTS, Metrics::COLUMN_DOCUMENT_REQUESTS, Metrics::COLUMN_ACQUIRED_VISITS],
            false,
            false
        );

        // only show request count for flat table, as subtable doesn't have other metrics
        if ((int)$view->requestConfig->getRequestParam('flat') === 1) {
            $view->config->setDefaultColumnsToDisplay(
                ['label', Metrics::COLUMN_REQUESTS],
                false,
                false
            );
        }

        $secondaryDimensions = [
            'pages'     => Piwik::translate('BotTracking_ColumnPageRequests'),
            'documents' => Piwik::translate('BotTracking_ColumnDocumentRequests'),
        ];
        $view->config->setSecondaryDimensions($secondaryDimensions, 'pages');
    }

    /**
     * @return void
     */
    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig($factory->createWidget()->setIsWide());
    }
}
