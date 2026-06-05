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
use Piwik\Piwik;
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

        $this->categoryId       = 'General_AIAssistants';
        $this->subcategoryId    = 'BotTracking_AIChatbotsContentRequests';
        $this->dimension        = new PageUrl();
        $this->metrics          = [new UniqueHumanPageviews(), new AIChatbotRequests()];
        $this->processedMetrics = [new DiscrepancyScore($this->getDiscrepancyScoreVariant())];
    }

    /**
     * @return DiscrepancyScore::VARIANT_HUMAN_FAVOURED|DiscrepancyScore::VARIANT_AI_FAVOURED
     */
    abstract protected function getDiscrepancyScoreVariant(): string;

    /**
     * Column to gate the standard `ExcludeLowPopulation` filter on. Per the DEV-19843 design each
     * report low-pops on its strong side (Human-Favoured → human pageviews, AI-Favoured → AI requests).
     */
    abstract protected function getExcludeLowPopulationColumn(): string;

    /**
     * Column to sort the report by when no explicit `filter_sort_column` is passed.
     * Mirrors $defaultSortColumn but is also applied at the ViewDataTable layer — the View runs
     * its own setDefaultSort before the API generic filters and would otherwise pick the first
     * non-label column from the merged DataTable (which happens to be ai_chatbot_requests for
     * bot rows after the merge), ignoring the Report-level default.
     */
    abstract protected function getDefaultViewSortColumn(): string;

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

        // Force the per-report default sort: the ViewDataTable layer otherwise falls back to
        // 'nb_visits' (absent) and then to the first non-label column it finds on the first row,
        // which is ai_chatbot_requests for both reports because of the bot-rows-first merge order.
        $view->requestConfig->filter_sort_column = $this->getDefaultViewSortColumn();
        $view->requestConfig->filter_sort_order  = 'desc';

        $this->configureExcludeLowPopulation($view);

        SegmentNotSupportedMessageHelper::addSegmentNotSupportedMessage($view);
    }

    /**
     * Wires the standard ExcludeLowPopulation filter on the report's strong-side column.
     * Defaults the toggle to ON (matching the DEV-19843 design) — users can pass
     * `enable_filter_excludelowpop=0` to see every row.
     *
     * The threshold is fixed at 1: rows with the strong-side column at 0 are dropped (a
     * Human-Favoured page with 0 human pageviews or an AI-Favoured page with 0 AI requests
     * isn't meaningful here). Passing 0 instead would let ExcludeLowPopulation fall back to
     * its 2%-of-column-sum heuristic, which silently filters every row on small datasets.
     */
    private function configureExcludeLowPopulation(ViewDataTable $view): void
    {
        $view->config->show_exclude_low_population = true;

        if (Common::getRequestVar('enable_filter_excludelowpop', '1', 'string') === '0') {
            // User explicitly disabled the filter via the UI toggle. Surface that state to the
            // client too so the toggle keeps showing "Exclude Rows With Low Population".
            $view->config->custom_parameters['enable_filter_excludelowpop'] = '0';
            return;
        }

        $view->requestConfig->filter_excludelowpop       = $this->getExcludeLowPopulationColumn();
        $view->requestConfig->filter_excludelowpop_value = '1';

        // Surface the default-on state to the client so the toggle in the data-table footer
        // renders as "Include Rows With Low Population" (i.e. the filter is currently active)
        // without the user having to interact with it first.
        $view->config->custom_parameters['enable_filter_excludelowpop'] = '1';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        // Intentionally not calling setIsWide(): the reporting page auto-pairs consecutive
        // non-wide widgets into a 2-column row (see CoreHome ReportingPage.store). Ordering
        // the two favoured-pages reports after the existing wide reports yields the
        // Human-Favoured + AI-Favoured side-by-side row required by DEV-19843.
        $widgetsList->addWidgetConfig($factory->createWidget());
    }

    /**
     * @return array<string, string>
     */
    public function getMetricsDocumentation(): array
    {
        $docs = parent::getMetricsDocumentation();

        // Scope the Discrepancy Score tooltip per variant.
        $key = $this->getDiscrepancyScoreVariant() === DiscrepancyScore::VARIANT_HUMAN_FAVOURED
            ? 'BotTracking_ColumnDiscrepancyScoreHumanFavouredDocumentation'
            : 'BotTracking_ColumnDiscrepancyScoreAIFavouredDocumentation';

        $docs[Metrics::COLUMN_DISCREPANCY_SCORE] = Piwik::translate($key);

        return $docs;
    }
}
