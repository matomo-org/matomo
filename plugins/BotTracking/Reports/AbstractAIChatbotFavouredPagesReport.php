<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\PageUrl;
use Piwik\Plugins\BotTracking\Columns\Metrics\AIChatbotRequests;
use Piwik\Plugins\BotTracking\Columns\Metrics\DiscrepancyScore;
use Piwik\Plugins\BotTracking\Columns\Metrics\UniqueHumanPageviews;
use Piwik\Plugins\BotTracking\FeatureFlags\AIChatbotsContentReports;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * Shared base for the Human-Favoured and AI-Favoured Pages reports.
 *
 * Both reports expose the same flat URL dimension, the same Unique Human Pageviews +
 * AI Chatbot Requests metric pair, and a Discrepancy Score processed metric whose variant
 * (human-favoured vs ai-favoured) is provided by the concrete subclass. The reports are
 * derived from the existing Actions/BotTracking blobs at API time (see API::buildFavouredPagesTable),
 * so no archive record is involved — but every report-level UI surface (Custom Alerts,
 * Scheduled Reports, Row Evolution, glossary) treats them as ordinary reports.
 */
abstract class AbstractAIChatbotFavouredPagesReport extends Report
{
    protected function init(): void
    {
        parent::init();

        $this->categoryId        = 'General_AIAssistants';
        $this->subcategoryId     = 'BotTracking_AIChatbotsContentRequests';
        $this->dimension         = new PageUrl();
        $this->metrics           = [new UniqueHumanPageviews(), new AIChatbotRequests()];
        $this->processedMetrics  = [new DiscrepancyScore($this->getDiscrepancyScoreVariant())];
        // Both reports sort by the Discrepancy Score — that's the headline insight, and it already
        // encodes traffic weighting, so sorting by it surfaces the genuinely (human/AI)-favoured
        // pages rather than just the busiest ones.
        $this->defaultSortColumn = Metrics::COLUMN_DISCREPANCY_SCORE;
    }

    /**
     * @return DiscrepancyScore::VARIANT_HUMAN_FAVOURED|DiscrepancyScore::VARIANT_AI_FAVOURED
     */
    abstract protected function getDiscrepancyScoreVariant(): string;

    /**
     * Column the "exclude low population" filter gates on. Per the DEV-19843 design each report
     * low-pops on its strong side (Human-Favoured → human pageviews, AI-Favoured → AI requests).
     */
    abstract protected function getExcludeLowPopulationColumn(): string;

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
            [
                'label',
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS,
                Metrics::COLUMN_AI_CHATBOT_REQUESTS,
                Metrics::COLUMN_DISCREPANCY_SCORE,
            ],
            false,
            false
        );

        // Disable the "show all columns" toggle: it switches the table to the Visitor Engagement
        // preset, which doesn't match the column schema of these reports and would render empty data.
        $view->config->show_table_all_columns = false;

        // Disable the Insights visualization: it expects visit-based metrics (nb_visits etc.)
        // that this report does not provide, so the rendered output would be empty.
        $view->config->show_insights = false;

        // Disable the bar/pie/tag-cloud visualizations: they don't make sense for a long list of
        // URLs with discrepancy-score metrics — the table view is the only useful one.
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

        // Force the default sort at the View layer too: RequestConfig::setDefaultSort ignores the
        // Report's $defaultSortColumn — it falls back to nb_visits (absent here) and then to the
        // first non-label column in columns_to_display. Set it explicitly so the Discrepancy Score
        // is the default sort in the UI as well as via the API.
        $view->requestConfig->filter_sort_column = $this->defaultSortColumn;
        $view->requestConfig->filter_sort_order  = 'desc';

        $this->configureExcludeLowPopulation($view);

        SegmentNotSupportedMessageHelper::addSegmentNotSupportedMessage($view);
    }

    /**
     * Excludes "low population" pages: those whose strong-side metric is below 3% of the top
     * page's value (per the DEV-19843 technical notes). The toggle defaults to ON; users can pass
     * `enable_filter_excludelowpop=0` to see every row.
     *
     * Implemented as a custom priority filter rather than the core ExcludeLowPopulation filter:
     * the threshold is relative to the table's own maximum (the "top page"), which the core
     * filter's percentage mode can't express (it uses a percentage of the column sum), and a
     * naive 0 minimum there would trigger its 2%-of-sum fallback and silently drop every row.
     * Running as a priority filter means the removal happens before sort/limit, so truncation and
     * the row count reflect the filtered set. The closure runs per table, so it is correct for
     * `DataTable\Map` (range / multi-period) results too.
     */
    private function configureExcludeLowPopulation(ViewDataTable $view): void
    {
        $view->config->show_exclude_low_population = true;

        $enabled = Common::getRequestVar('enable_filter_excludelowpop', '1', 'string') !== '0';

        // Surface the resolved toggle state to the client so the data-table footer label
        // ("Include/Exclude Rows With Low Population") matches whether the filter is actually
        // active on initial load, without the user having to interact with it first.
        $view->config->custom_parameters['enable_filter_excludelowpop'] = $enabled ? '1' : '0';

        if (!$enabled) {
            return;
        }

        $column = $this->getExcludeLowPopulationColumn();

        $view->config->filters[] = [
            function (DataTable $table) use ($column) {
                $max = 0;
                foreach ($table->getRows() as $row) {
                    if ($row->isSummaryRow()) {
                        continue;
                    }
                    $max = max($max, (int) $row->getColumn($column));
                }

                // Nothing meaningful to filter (empty table or all-zero strong side); leaving the
                // threshold at 0 here also avoids deleting every row.
                if ($max <= 0) {
                    return;
                }

                $threshold = 0.03 * $max;

                $keysToDelete = [];
                foreach ($table->getRows() as $key => $row) {
                    if ($row->isSummaryRow()) {
                        continue;
                    }
                    if ((float) $row->getColumn($column) < $threshold) {
                        $keysToDelete[] = $key;
                    }
                }
                foreach ($keysToDelete as $key) {
                    $table->deleteRow($key);
                }
            },
            [],
            $isPriority = true,
        ];
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        // Side-by-side layout contract (DEV-19843): the Human-Favoured and AI-Favoured reports must
        // render next to each other in a single 2-column row at the bottom of the
        // AIChatbotsContentRequests page. Matomo has no 2-equal-column widget-container primitive,
        // so this relies on the reporting page auto-pairing two CONSECUTIVE NON-WIDE widgets into a
        // row (see CoreHome ReportingPage.store::widgets). For that to hold, all of the following
        // must stay true (the BotTracking_spec.js pairing assertion is the load-bearing guard):
        //   - neither report calls setIsWide() (hence the bare createWidget() below);
        //   - they keep orders 40 and 50 so they remain the last two widgets on the page;
        //   - the three sibling content reports (orders 10/20/30) stay wide so they don't get
        //     pulled into the pairing;
        //   - no other plugin injects a non-wide widget between/around them in this subcategory.
        $widgetsList->addWidgetConfig($factory->createWidget());
    }
}
