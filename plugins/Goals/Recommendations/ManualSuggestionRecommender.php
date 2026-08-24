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
 * Produces non-URL goal ideas (form/event, file download, outbound link, visit
 * duration) from the crawl signals. These are manual suggestions only, never
 * auto-created, because they need tracking code on the site or fall outside the
 * URL-goal scope; the UI shows them with a "how to set it up" hint.
 */
class ManualSuggestionRecommender
{
    private const MAX_SUGGESTIONS = 5;

    /**
     * @param array{
     *   manualSignals?: array{
     *     downloadExtensions?: array<string, int>, outlinkHosts?: array<string, int>,
     *     hasContactLinks?: bool, formCount?: int
     *   }
     * } $analysis
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

        // Universal engagement goal that fits any site; always offered last.
        $suggestions[] = [
            'category' => 'visit_duration',
            'name' => Piwik::translate('Goals_RecommendManualDurationName'),
            'howTo' => Piwik::translate('Goals_RecommendManualDurationSetup'),
        ];

        return array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
    }
}
