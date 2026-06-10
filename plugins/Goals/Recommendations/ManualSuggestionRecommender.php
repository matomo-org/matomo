<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Piwik;

/**
 * Produces non-URL goal *ideas* (form/event, file download, outbound link, visit
 * duration) from the crawl signals collected by {@see HomepageAnalyzer}.
 *
 * Unlike the URL recommendations, these are presented as manual suggestions only:
 * they are never auto-created, because they either need tracking code on the site
 * (events) or fall outside this MVP's URL-goal scope. The UI shows them with a
 * "how to set it up" hint and no create button.
 *
 * Inspired by the ID-6 "simple-new" prototype, which derived form/download/outlink/
 * time-on-site goals deterministically from the same kind of signals.
 */
class ManualSuggestionRecommender
{
    private const MAX_SUGGESTIONS = 5;

    /**
     * @param array{manualSignals?: array{downloadExtensions?: array<string, int>, outlinkHosts?: array<string, int>, hasContactLinks?: bool, formCount?: int}} $analysis
     * @return array<int, array{name: string, howTo: string, category: string}>
     */
    public function recommend(array $analysis): array
    {
        $signals = $analysis['manualSignals'] ?? [];
        $suggestions = [];

        if (!empty($signals['formCount'])) {
            $suggestions[] = [
                'category' => 'event',
                'name' => Piwik::translate('Goals_RecommendManualFormName'),
                'howTo' => Piwik::translate('Goals_RecommendManualFormSetup'),
            ];
        }

        if (!empty($signals['downloadExtensions'])) {
            $extensions = array_keys($signals['downloadExtensions']);
            $extension = (string) ($extensions[0] ?? '');
            $suggestions[] = [
                'category' => 'file',
                'name' => Piwik::translate('Goals_RecommendManualDownloadName'),
                'howTo' => Piwik::translate('Goals_RecommendManualDownloadSetup', ['.' . $extension]),
            ];
        }

        if (!empty($signals['outlinkHosts']) || !empty($signals['hasContactLinks'])) {
            $suggestions[] = [
                'category' => 'outlink',
                'name' => Piwik::translate('Goals_RecommendManualOutlinkName'),
                'howTo' => Piwik::translate('Goals_RecommendManualOutlinkSetup'),
            ];
        }

        // Universal engagement fallback, always offered last.
        $suggestions[] = [
            'category' => 'visit_duration',
            'name' => Piwik::translate('Goals_RecommendManualDurationName'),
            'howTo' => Piwik::translate('Goals_RecommendManualDurationSetup'),
        ];

        return array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
    }
}
