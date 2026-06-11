<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\BotTracking\Columns\Metrics\AvgResponseSize;
use Piwik\Plugins\BotTracking\Columns\Metrics\AvgServerTime;
use Piwik\Plugins\BotTracking\Columns\Metrics\PageNotFound404Requests;
use Piwik\Plugins\BotTracking\Columns\Metrics\Requests;
use Piwik\Plugins\BotTracking\Columns\Metrics\ServerError5xxRequests;
use Piwik\Plugins\BotTracking\FeatureFlags\AIChatbotsContentReports;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * Base class for the Pages and Documents content-URL reports.
 *
 * Owns the shared metric set, configureView() defaults, configureWidgets(), and the per-report
 * scoped Requests documentation override. The error-status columns (page_not_found_404_requests,
 * server_error_5xx_requests) are registered in $this->metrics so they remain available via the
 * API, Custom Alerts, Scheduled Reports and Row Evolution — but the "show all columns" table
 * toggle is disabled because that view uses the Visitor Engagement preset which doesn't match
 * the BotTracking column schema (and would render an empty table).
 *
 * GetAIChatbotBrokenContent stays as a direct Report subclass — it has a different metric shape.
 */
abstract class AbstractAIChatbotContentUrlReport extends Report
{
    protected function init(): void
    {
        parent::init();

        $this->categoryId       = 'General_AIAssistants';
        $this->subcategoryId    = 'BotTracking_AIChatbotsContentRequests';
        $this->metrics          = [new Requests(), new ServerError5xxRequests(), new PageNotFound404Requests()];
        $this->processedMetrics = [new AvgServerTime(), new AvgResponseSize()];
        $this->defaultSortColumn = Metrics::COLUMN_REQUESTS;
    }

    /**
     * Gates this report behind the AIChatbotsContentReports feature flag.
     * When the flag is off the report is hidden from every UI surface and
     * direct API calls throw "Report not enabled".
     */
    public function isEnabled()
    {
        return StaticContainer::get(FeatureFlagManager::class)
            ->isFeatureActive(AIChatbotsContentReports::class);
    }

    public function configureView(ViewDataTable $view): void
    {
        parent::configureView($view);

        $view->config->setDefaultColumnsToDisplay(
            ['label', Metrics::COLUMN_REQUESTS, Metrics::COLUMN_AVG_SERVER_TIME, Metrics::COLUMN_AVG_RESPONSE_SIZE],
            false,
            false
        );

        // Disable the "show all columns" toggle: it switches the table to the Visitor Engagement
        // preset, which doesn't match the BotTracking column schema and would render empty data.
        $view->config->show_table_all_columns = false;

        // Disable the Insights visualization: it expects visit-based metrics (nb_visits etc.)
        // that this report does not provide, so the rendered output would be empty.
        $view->config->show_insights = false;

        // Disable the bar/pie/tag-cloud visualizations: they don't make meaningful sense for a
        // long list of URLs with byte and time metrics — the table view is the only useful one.
        $view->config->show_bar_chart = false;
        $view->config->show_pie_chart = false;
        $view->config->show_tag_cloud = false;

        // Render URL labels as clickable links. Labels are Matomo-normalized URLs without scheme
        // (e.g. example.com/article/2); prepend https:// to form a valid link target.
        $view->config->filters[] = function (DataTable $table) {
            foreach ($table->getRows() as $row) {
                if ($row->isSummaryRow()) {
                    continue;
                }
                $label = $row->getColumn('label');
                if (is_string($label) && $label !== '') {
                    $row->setMetadata('url', 'https://' . $label);
                }
            }
        };

        SegmentNotSupportedMessageHelper::addSegmentNotSupportedMessage($view);
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        $widgetsList->addWidgetConfig($factory->createWidget()->setIsWide());
    }

    /**
     * Returns the translation key for the scoped "Requests" tooltip for this report.
     * Concrete subclasses return the key that scopes the tooltip to their action type
     * (page URLs or document URLs).
     */
    abstract protected function getRequestsDocumentationKey(): string;

    /**
     * @return array<string, string>
     */
    public function getMetricsDocumentation(): array
    {
        $docs = parent::getMetricsDocumentation();

        // Scope the "Requests" tooltip to this report's action type (page URLs or document URLs).
        $docs[Metrics::COLUMN_REQUESTS] = Piwik::translate($this->getRequestsDocumentationKey());

        return $docs;
    }
}
