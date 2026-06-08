<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\DataTable;

use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Row;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tracker\PageUrl;

/**
 * Merges the AI chatbot content-pages table with the Actions page-URLs table into the flat
 * source table that backs the Human-Favoured and AI-Favoured Pages reports.
 *
 * The bot table is the base: each row is reduced to a consistent column set
 * `[label, unique_human_pageviews, ai_chatbot_requests]` (human pageviews defaulting to 0).
 * The Actions rows are then walked — a matching bot row gets its `unique_human_pageviews`
 * patched in, and a URL present only on the human side is appended with
 * `ai_chatbot_requests = 0`. This guarantees every row carries both inputs the
 * DiscrepancyScore processed metric depends on.
 *
 * The caller is responsible for naming the Actions columns (e.g. applying `ReplaceColumnNames`)
 * before passing the table in — this merger reads `nb_visits` by name.
 */
class FavouredPagesMerger
{
    /**
     * @param DataTable|DataTable\Map $botData    AI chatbot content-pages table (or a Map of them).
     * @param DataTable|DataTable\Map $actionsData Actions page-URLs table, flat, with named columns.
     * @return DataTable|DataTable\Map The merged table (the bot table is mutated and returned).
     */
    public function merge(DataTableInterface $botData, DataTableInterface $actionsData): DataTableInterface
    {
        if ($botData instanceof DataTable\Map) {
            $actionsChildren = $actionsData instanceof DataTable\Map ? $actionsData->getDataTables() : [];

            foreach ($botData->getDataTables() as $key => $botChild) {
                $actionsChild = $actionsChildren[$key] ?? new DataTable();
                $botData->addTable($this->merge($botChild, $actionsChild), $key);
            }

            return $botData;
        }

        // After the Map check above $botData is necessarily a flat DataTable. The Actions side is
        // guarded anyway in case the period/site shape diverges between the two source APIs.
        $actionsTable = $actionsData instanceof DataTable ? $actionsData : new DataTable();

        return $this->mergeTable($botData, $actionsTable);
    }

    private function mergeTable(DataTable $botTable, DataTable $actionsTable): DataTable
    {
        // Step 1: reduce every bot row to the canonical column set and order so all rows serialize
        // identically regardless of origin: [label, unique_human_pageviews(=0), ai_chatbot_requests].
        foreach ($botTable->getRows() as $row) {
            $requests = (int) $row->getColumn(Metrics::COLUMN_REQUESTS);
            $row->setColumns([
                'label'                                => $row->getColumn('label'),
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 0,
                Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $requests,
            ]);
            // Drop any metadata carried over from the bot blob (segment hints etc.).
            $row->deleteMetadata();
        }
        $botTable->setLabelsHaveChanged();

        // Step 2: walk the Actions rows. Patch matching bot rows in place; collect human-only URLs
        // keyed by label so duplicates that normalize to the same label collapse to one row.
        $humanOnly = [];
        foreach ($actionsTable->getRows() as $actionsRow) {
            $url = $actionsRow->getMetadata('url');
            if (!is_string($url) || $url === '') {
                // Summary/"Others" rows and page-title rows have no url metadata — skip them.
                continue;
            }

            // The bot label is log_action.name normalized via PageUrl::normalizeUrl, which strips
            // the scheme AND a leading "www.". The Actions `url` metadata is the reconstructed full
            // URL, so re-normalize it the same way or www. sites never match (rows would split).
            $label = PageUrl::normalizeUrl($url)['url'];
            if ($label === '') {
                continue;
            }

            $nbVisits = (int) $actionsRow->getColumn('nb_visits');

            $matchingBotRow = $botTable->getRowFromLabel($label);
            if ($matchingBotRow !== false) {
                $matchingBotRow->setColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, $nbVisits);
                continue;
            }

            $humanOnly[$label] = $nbVisits;
        }

        // Step 3: append the human-only URLs (deduped above) in the canonical column order.
        foreach ($humanOnly as $label => $nbVisits) {
            $botTable->addRow(new Row([
                Row::COLUMNS => [
                    'label'                                => $label,
                    Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $nbVisits,
                    Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 0,
                ],
            ]));
        }

        return $botTable;
    }
}
