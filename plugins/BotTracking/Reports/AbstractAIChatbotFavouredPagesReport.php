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
use Piwik\DataTable;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\PageUrl;
use Piwik\Plugins\BotTracking\Columns\Metrics\AIChatbotRequests;
use Piwik\Plugins\BotTracking\Columns\Metrics\DiscrepancyScore;
use Piwik\Plugins\BotTracking\Columns\Metrics\UniqueHumanPageviews;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * Shared base for the Human-Favoured and AI-Favoured Pages reports.
 *
 * Both reports expose the same flat URL dimension, the same Unique Human Pageviews +
 * AI Chatbot Requests metric pair, and a Discrepancy Score whose variant (human-favoured vs
 * ai-favoured) is provided by the concrete subclass. Each variant is backed by its own archived
 * blob record built and scored during archiving (see {@see \Piwik\Plugins\BotTracking\RecordBuilders\AIChatbotFavouredPages});
 * the API just reads it. Report-level surfaces (Custom Alerts, Scheduled Reports, glossary) treat
 * them as ordinary reports, and Row Evolution is supported because the per-period data is pre-computed.
 */
abstract class AbstractAIChatbotFavouredPagesReport extends Report
{
    protected function init(): void
    {
        parent::init();

        $this->categoryId        = 'General_AIAssistants';
        $this->subcategoryId     = 'BotTracking_AIChatbotsContentRequests';
        $this->dimension         = new PageUrl();
        // discrepancy_score is materialised during archiving (see AIChatbotFavouredPages /
        // FavouredPagesScorer) and read straight back, so it is an ordinary column here rather than a
        // recomputed processed metric. The two traffic metrics are ordered strong-side first (the
        // column the report favours leads), so AI-Favoured leads with AI Chatbot Requests.
        $human                   = new UniqueHumanPageviews();
        $ai                      = new AIChatbotRequests();
        $trafficMetrics          = $this->getDiscrepancyScoreVariant() === DiscrepancyScore::VARIANT_AI_FAVOURED
            ? [$ai, $human]
            : [$human, $ai];
        $this->metrics           = array_merge(
            $trafficMetrics,
            [new DiscrepancyScore($this->getDiscrepancyScoreVariant())]
        );
        // No processed metrics; don't inherit Report's core visitor defaults.
        $this->processedMetrics  = [];
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
     * The report's strong-side traffic column (human pageviews for Human-Favoured, AI requests for
     * AI-Favoured).
     */
    private function getStrongSideColumn(): string
    {
        return $this->getDiscrepancyScoreVariant() === DiscrepancyScore::VARIANT_HUMAN_FAVOURED
            ? Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS
            : Metrics::COLUMN_AI_CHATBOT_REQUESTS;
    }

    /**
     * The two traffic columns ordered strong-side first, so each report leads with the metric it
     * favours (AI Chatbot Requests for AI-Favoured, Unique Human Pageviews for Human-Favoured).
     *
     * @return string[]
     */
    private function getTrafficColumnsInDisplayOrder(): array
    {
        return $this->getDiscrepancyScoreVariant() === DiscrepancyScore::VARIANT_AI_FAVOURED
            ? [Metrics::COLUMN_AI_CHATBOT_REQUESTS, Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS]
            : [Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, Metrics::COLUMN_AI_CHATBOT_REQUESTS];
    }

    /**
     * Breaks ties on the Discrepancy Score by the report's strong-side traffic, so pages with the
     * same score (notably the many score=0 rows when the low-population filter is off) are ordered
     * by how much human / AI traffic they have instead of arbitrarily. If the user sorts by a
     * different column, the score becomes the tie-breaker.
     */
    public function getSecondarySortColumnCallback()
    {
        $strongColumn = $this->getStrongSideColumn();

        return function ($primaryColumn) use ($strongColumn) {
            return $primaryColumn === Metrics::COLUMN_DISCREPANCY_SCORE
                ? $strongColumn
                : Metrics::COLUMN_DISCREPANCY_SCORE;
        };
    }

    public function configureView(ViewDataTable $view): void
    {
        parent::configureView($view);

        $view->config->setDefaultColumnsToDisplay(
            array_merge(
                ['label'],
                $this->getTrafficColumnsInDisplayOrder(),
                [Metrics::COLUMN_DISCREPANCY_SCORE]
            ),
            false,
            false
        );

        // Show-all-columns switches to the Visitor Engagement preset, which doesn't fit this schema.
        $view->config->show_table_all_columns = false;

        // Insights and bar/pie/tag-cloud all assume visit metrics (nb_visits etc.) this report lacks,
        // so they would render empty — the table is the only useful view.
        $view->config->show_insights = false;
        $view->config->show_bar_chart = false;
        $view->config->show_pie_chart = false;
        $view->config->show_tag_cloud = false;

        // Render URL labels as clickable links (scheme-less normalized URLs; prepend https://).
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
     * Excludes pages that aren't meaningfully favoured by dropping rows whose Discrepancy Score is
     * below 1. Because the score is `lean × volume`, this removes balanced and opposite-leaning
     * pages (lean = 0 → score 0) as well as the near-zero-volume tail, so each report shows only
     * pages leaning its own way. The toggle defaults to ON; `enable_filter_excludelowpop=0` shows
     * every row.
     *
     * Wired through the standard ExcludeLowPopulation generic filter targeting the score column.
     * The score is a stored column (materialised during archiving), so it is present on every row by
     * the time the filter runs. The minimum value must stay > 0 — passing 0 makes ExcludeLowPopulation
     * fall back to its 2%-of-sum heuristic and empty the table.
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

        $view->requestConfig->filter_excludelowpop       = Metrics::COLUMN_DISCREPANCY_SCORE;
        $view->requestConfig->filter_excludelowpop_value = '1';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        // Side-by-side layout contract: the Human-Favoured and AI-Favoured reports must
        // render next to each other in a 2-column row on the AIChatbotsContentRequests page. Matomo
        // has no 2-equal-column widget-container primitive, so this relies on the reporting page
        // auto-pairing CONSECUTIVE NON-WIDE widgets into columns (see CoreHome
        // ReportingPage.store::widgets). On this page the non-wide widgets are Documents, Broken,
        // Human-Favoured and AI-Favoured (orders 20/30/40/50); the auto-pairing distributes them
        // across two columns so Documents sits beside Broken and Human-Favoured beside AI-Favoured.
        // For that to hold, all of the following must stay true (the BotTracking_spec.js pairing
        // assertions are the load-bearing guard):
        //   - none of those four reports call setIsWide() (hence the bare createWidget() below);
        //   - they keep orders 40 and 50 so they stay paired with each other (after Documents/Broken);
        //   - the Pages report (order 10) stays wide so it remains a full-width row on top;
        //   - no other plugin injects a non-wide widget between/around them in this subcategory.
        $widgetsList->addWidgetConfig($factory->createWidget());
    }
}
