<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;

/**
 * The configuration the corpus needs around it: the sites its rows point at, goals for the
 * conversions to belong to, and the segments the archiving benchmark measures.
 *
 * Idempotent throughout - a resumed run calls this again and must not end up with twenty sites.
 *
 * The segments matter as much as the data. Bottleneck B2 is that the "All Visits" archive takes
 * three to four hours and blocks every segment archive behind it, so the benchmark needs segments
 * whose selectivity spans the whole range, from "a quarter of all visits" down to a needle, and
 * at least one action-scope segment that forces a join to log_link_visit_action.
 */
class SetupHelper
{
    /**
     * Selectivities are by construction: each definition targets a value whose share is fixed in
     * Profile/VisitorProfile, so the measured share can be checked against the intent.
     */
    private const SEGMENTS = [
        ['perfcorpus device smartphone', 'deviceType==smartphone', 0.40],
        ['perfcorpus returning', 'visitorType==returning', 0.35],
        ['perfcorpus country de', 'countryCode==de', 0.25],
        ['perfcorpus dimension1 tier-0', 'dimension1==tier-0', 0.20],
        ['perfcorpus campaign traffic', 'referrerType==campaign', 0.15],
        ['perfcorpus event checkout', 'eventCategory==checkout', 0.05],
        ['perfcorpus city berlin', 'city==Berlin', 0.02],
        ['perfcorpus dimension3 cohort-42', 'dimension3==cohort-42', 0.01],
        ['perfcorpus de and smartphone', 'countryCode==de;deviceType==smartphone', 0.10],
        ['perfcorpus needle dimension4', 'dimension4==account-424242', 0.000001],
    ];

    public static function run(Profile $profile, callable $log): void
    {
        Access::doAsSuperUser(function () use ($profile, $log) {
            self::ensureBrowserArchivingDisabled($log);
            self::ensureSites($profile, $log);
            self::ensureGoals($profile, $log);
            self::ensureCustomDimensions($log);
            self::ensureSegments($log);
        });
    }

    /**
     * Turns off browser-triggered archiving.
     *
     * Matomo refuses to create pre-processed segments while it is on, and the benchmark needs
     * them: bottleneck B2 is precisely that segment archiving queues up behind the All Visits
     * archive. It is also the right setting for a measurement box regardless - a report request
     * that silently kicks off an archiving run in the background would contaminate every timing
     * taken on it.
     */
    private static function ensureBrowserArchivingDisabled(callable $log): void
    {
        if (!Rules::isBrowserTriggerEnabled()) {
            return;
        }

        // Rules::isBrowserTriggerEnabled() reads this option first when the general settings UI
        // is enabled, and falls back to the config file; setting the option covers both.
        Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, '0');
        Option::clearCachedOption(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);

        $log('  config browser-triggered archiving disabled (required for pre-processed segments,'
            . ' and it would contaminate timings anyway)');
    }

    private static function ensureSites(Profile $profile, callable $log): void
    {
        $existing = (int) Db::fetchOne('SELECT COUNT(*) FROM `' . Common::prefixTable('site') . '`');
        $wanted = $profile->getSiteCount();

        if ($existing >= $wanted) {
            $log(sprintf('  sites  %d already present', $existing));

            return;
        }

        for ($i = $existing + 1; $i <= $wanted; $i++) {
            // Named arguments on purpose: addSite takes nineteen positional parameters, and
            // miscounting the nulls puts the currency where the start date belongs.
            SitesManagerApi::getInstance()->addSite(
                siteName: 'Perf corpus site ' . $i,
                urls: ['https://example.org'],
                // Site 1 carries the ecommerce orders, so it has to have ecommerce enabled or
                // the Ecommerce reports archive nothing at all.
                ecommerce: 1 === $i ? 1 : 0,
                timezone: 'UTC',
                currency: 'EUR'
            );
        }

        $log(sprintf('  sites  created %d (now %d, all UTC)', $wanted - $existing, $wanted));
    }

    /**
     * Goals on every site, not just site 1.
     *
     * Conversions are spread across all the sites, and a conversion whose idgoal does not exist
     * for its site is rejected by the tracker and archives into nothing. This was found by the
     * churn test rejecting conversions on sites 2-5.
     */
    private static function ensureGoals(Profile $profile, callable $log): void
    {
        $created = 0;

        for ($idSite = 1; $idSite <= $profile->getSiteCount(); $idSite++) {
            $existing = GoalsApi::getInstance()->getGoals([$idSite]);

            for ($i = count($existing) + 1; $i <= Profile::GOAL_COUNT; $i++) {
                GoalsApi::getInstance()->addGoal(
                    idSite: $idSite,
                    name: 'Perf corpus goal ' . $i,
                    matchAttribute: 'url',
                    pattern: '/goal-' . $i,
                    patternType: 'contains'
                );
                $created++;
            }
        }

        $log(sprintf(
            '  goals  %d goals across %d sites (%d created)',
            Profile::GOAL_COUNT * $profile->getSiteCount(),
            $profile->getSiteCount(),
            $created
        ));
    }

    /**
     * Registers five visit-scope and five action-scope custom dimensions on site 1.
     *
     * The columns exist in the schema regardless, but a dimension has to be configured in the
     * plugin before dimensionN is a segment Matomo will accept - and the segment set needs the
     * high-cardinality ones to reach down to a needle-in-a-haystack selectivity.
     */
    private static function ensureCustomDimensions(callable $log): void
    {
        $api = CustomDimensionsApi::getInstance();
        $existing = $api->getConfiguredCustomDimensions(1);

        $byScope = ['visit' => 0, 'action' => 0];
        foreach ($existing as $dimension) {
            if (isset($byScope[$dimension['scope']])) {
                $byScope[$dimension['scope']]++;
            }
        }

        $created = 0;

        foreach (['visit' => 5, 'action' => 5] as $scope => $wanted) {
            for ($i = $byScope[$scope] + 1; $i <= $wanted; $i++) {
                $api->configureNewCustomDimension(
                    idSite: 1,
                    name: sprintf('Perf %s dimension %d', $scope, $i),
                    scope: $scope,
                    active: true
                );
                $created++;
            }
        }

        $log(sprintf(
            '  dims   5 visit-scope + 5 action-scope custom dimensions (%d created)',
            $created
        ));
    }

    private static function ensureSegments(callable $log): void
    {
        $api = SegmentEditorApi::getInstance();
        $existingNames = [];

        foreach ($api->getAll(1) as $segment) {
            $existingNames[$segment['name']] = true;
        }

        $created = 0;

        foreach (self::SEGMENTS as [$name, $definition, $targetShare]) {
            if (isset($existingNames[$name])) {
                continue;
            }

            $api->add(
                name: $name,
                definition: $definition,
                idSite: 1,
                autoArchive: true,
                enabledAllUsers: true
            );
            $created++;
        }

        $log(sprintf(
            '  segs   %d segments (%d created), selectivity %.4f%% to %.0f%%',
            count(self::SEGMENTS),
            $created,
            min(array_column(self::SEGMENTS, 2)) * 100,
            max(array_column(self::SEGMENTS, 2)) * 100
        ));
    }

    /**
     * @return array[] name, definition, intended share - used by the verifier
     */
    public static function getSegments(): array
    {
        return self::SEGMENTS;
    }
}
