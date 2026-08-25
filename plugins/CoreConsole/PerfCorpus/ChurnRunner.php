<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Tracker;
use Piwik\Tracker\Request;

/**
 * Drives Matomo's real tracker to reproduce the write pattern tracking actually produces.
 *
 * This is the test the corpus cannot be: the corpus is insert-only and represents the end state
 * of a visit, whereas Matomo's tracker is not append-only. Every action after the first UPDATEs
 * the same log_visit row, and there are several more UPDATEs around it, so the write pattern a
 * replica or a change-data-capture pipeline actually sees is nothing like a bulk load.
 *
 * Crucially this does not model the write pattern, it *causes* it: requests go through
 * Tracker::trackRequest(), the same entry point a real tracking request takes, so visit
 * recognition, DeviceDetector, the dimension classes, GoalManager, PagePerformance and
 * CustomDimensions all run exactly as they do in production. Nothing here has an opinion about
 * what SQL that produces - it just watches, via the tracker's own SQL profiler.
 *
 * The write pattern it therefore exercises, all verified in core rather than assumed:
 *
 *   Visit.php:377                      UPDATE log_visit on every action after the first
 *   CustomDimensionsRequestProcessor:48 UPDATE log_visit (last_idlink_va) on the first action
 *   CustomDimensionsRequestProcessor:64 UPDATE log_link_visit_action (time_spent) on the
 *                                      *previous* action, once the next one arrives
 *   PerformanceDataProcessor:85        SELECT llva JOIN log_action, then UPDATE llva time_*
 *   Visit.php:380                      UPDATE idvisitor across every log table, when a visitor
 *                                      is re-identified mid-visit by user id
 *   GoalManager.php:402                UPDATE log_conversion as an ecommerce cart changes
 *
 * Visits are kept open concurrently, so updates land on rows inserted seconds earlier - which is
 * the property that makes a ReplacingMergeTree accumulate versions faster than it merges them.
 */
class ChurnRunner
{
    /** Actions per visit, reusing the corpus distribution so the shape matches the corpus. */
    private Profile $profile;

    private int $seed;
    private int $workerNumber;
    private string $tokenAuth;
    private int $actionGapSeconds;
    private float $pagePerformanceShare;
    private float $userIdRewriteShare;
    private bool $sendVisitorIdCookie;

    private Tracker $tracker;

    /** @var array[] in-flight visits */
    private array $slots = [];

    private array $counters = [
        'visits' => 0,
        'requests' => 0,
        'pageviews' => 0,
        'events' => 0,
        'searches' => 0,
        'downloads' => 0,
        'outlinks' => 0,
        'performancePings' => 0,
        'goalConversions' => 0,
        'ecommerceOrders' => 0,
        'userIdRewrites' => 0,
        'errors' => 0,
    ];

    private int $nextVisitorOrdinal;

    /**
     * Makes order ids unique across runs. Visitor ordinals restart from the same base every run,
     * which is fine - it just means returning visitors - but Matomo rejects a repeated
     * idsite/idorder pair, so the order id needs something that does not repeat.
     */
    private string $runNonce;

    public function __construct(
        Profile $profile,
        int $seed,
        int $workerNumber,
        #[\SensitiveParameter]
        string $tokenAuth,
        int $actionGapSeconds,
        float $pagePerformanceShare,
        float $userIdRewriteShare,
        bool $sendVisitorIdCookie = true
    ) {
        $this->profile = $profile;
        $this->seed = $seed;
        $this->workerNumber = $workerNumber;
        $this->tokenAuth = $tokenAuth;
        $this->actionGapSeconds = max(1, $actionGapSeconds);
        $this->pagePerformanceShare = $pagePerformanceShare;
        $this->userIdRewriteShare = $userIdRewriteShare;
        $this->sendVisitorIdCookie = $sendVisitorIdCookie;

        $this->runNonce = base_convert((string) (time() % 1000000000), 10, 36) . base_convert((string) getmypid(), 10, 36);

        // Each run and worker gets its own block of a million visitor ordinals, well above
        // anything the corpus uses.
        //
        // This is not tidiness. Visitor identity is derived from the ordinal, so a fixed base
        // meant consecutive runs reused the same visitors - and Matomo's visit window is thirty
        // minutes, so the second run's requests matched the first run's still-open visits and
        // were appended to them instead of starting new ones. That turns new-visit INSERTs into
        // existing-visit UPDATEs and inflates the one number this command exists to report.
        $this->nextVisitorOrdinal = 2000000000
            + (crc32($this->runNonce . ':' . $workerNumber) % 1000000) * 1000000;

        Tracker::loadTrackerEnvironment();
        $this->tracker = new Tracker();

        if (!$this->tracker->shouldRecordStatistics()) {
            throw new \RuntimeException(
                'The tracker will not record anything. Check record_statistics in config.ini.php '
                . 'and that Matomo is installed.'
            );
        }
    }

    /**
     * Mean seconds a visit stays open, which is what decides how many have to be held at once.
     */
    public function getMeanVisitSeconds(): float
    {
        return (Profile::MEAN_ACTIONS_PER_VISIT - 1) * $this->actionGapSeconds;
    }

    /**
     * @param callable|null $onTick called about once a second with the counters so far
     * @return array the counters
     */
    public function run(
        int $durationSeconds,
        int $concurrentVisits,
        ?callable $onTick = null,
        ?callable $rateMultiplier = null
    ): array {
        $startedAt = microtime(true);
        $endAt = $startedAt + $durationSeconds;
        $lastTick = $startedAt;

        $this->slots = array_fill(0, max(1, $concurrentVisits), null);

        while (true) {
            $now = microtime(true);

            if ($now >= $endAt) {
                break;
            }

            $didWork = false;

            foreach ($this->slots as $index => $slot) {
                if (null === $slot) {
                    // Under --diurnal the rate follows the traffic curve, and holding a slot
                    // closed is how the rate comes down: fewer visits open, so fewer actions and
                    // fewer updates per second.
                    if (null !== $rateMultiplier) {
                        $multiplier = $rateMultiplier(($now - $startedAt) / max(1, $durationSeconds));

                        if ($multiplier < 1.0 && (mt_rand() / mt_getrandmax()) > $multiplier) {
                            continue;
                        }
                    }

                    $this->slots[$index] = $this->openVisit($now);
                    $didWork = true;
                    continue;
                }

                if ($now < $slot['dueAt']) {
                    continue;
                }

                $this->slots[$index] = $this->advanceVisit($slot, $now);
                $didWork = true;
            }

            if (null !== $onTick && $now - $lastTick >= 1.0) {
                $onTick($this->counters, $now - $startedAt);
                $lastTick = $now;
            }

            if (!$didWork) {
                usleep(2000);
            }
        }

        return $this->counters;
    }

    /**
     * Sets up a visit and issues its first action. Nothing is written by this method directly -
     * the tracker decides that.
     *
     * Returns null when the visit was a single-action bounce, which is 45% of them: it opened and
     * closed within this call, so the slot is free again immediately.
     */
    private function openVisit(float $now): ?array
    {
        $ordinal = $this->nextVisitorOrdinal++;
        $rng = Rng::forStream($this->seed, Rng::S_VISIT, $ordinal);
        $visitor = VisitorProfile::build($this->seed, $ordinal, $this->profile);

        $actionCount = $rng->nextBool(Profile::BOUNCE_SHARE)
            ? 1
            : min(
                Profile::MAX_ACTIONS_PER_VISIT,
                1 + max(1, (int) round($rng->nextLogNormal(
                    Profile::ACTIONS_LOGNORMAL_MEDIAN,
                    Profile::ACTIONS_LOGNORMAL_SIGMA
                )))
            );

        $siteCount = $this->profile->getSiteCount();
        $idSite = $siteCount > 1 && !$rng->nextBool(Profile::SITE_1_SHARE)
            ? $rng->nextInt(2, $siteCount)
            : 1;

        $slot = [
            'ordinal' => $ordinal,
            'rng' => $rng,
            'visitor' => $visitor,
            'idSite' => $idSite,
            'actions' => $actionCount,
            'cursor' => 0,
            'dueAt' => $now,
            'lastUrl' => null,
            // A user id arriving after the visit already exists is what makes the tracker
            // re-identify the visitor and rewrite idvisitor across every log table.
            'rewriteUserId' => $rng->nextBool($this->userIdRewriteShare),
            'converts' => $rng->nextBool(Profile::GOAL_CONVERSION_SHARE),
            // Only site 1 has ecommerce enabled, same as the corpus.
            'orders' => $rng->nextBool(Profile::ECOMMERCE_ORDER_SHARE) && 1 === $idSite,
            'hotIndex' => $rng->nextZipfRank($this->hotPoolSize(), Profile::ZIPF_EXPONENT) - 1,
        ];

        return $this->advanceVisit($slot, $now);
    }

    /**
     * Issues the next action of a visit, and closes it when there are none left.
     *
     * @return array|null null when the visit is finished and the slot is free
     */
    private function advanceVisit(array $slot, float $now): ?array
    {
        $rng = $slot['rng'];
        $isFirst = 0 === $slot['cursor'];
        $isLast = $slot['cursor'] === $slot['actions'] - 1;

        $params = $this->baseParams($slot);
        $typeIndex = $rng->pickFromCdf(Profile::ACTION_TYPE_CDF);
        $pageViewId = null;

        if (0 === $typeIndex) {
            $slot['hotIndex'] = $this->walk($rng, $slot['hotIndex']);
            $url = 'https://' . Vocabulary::hotUrl($slot['hotIndex']);
            $pageViewId = substr(md5($slot['ordinal'] . ':' . $slot['cursor']), 0, 6);

            $params['url'] = $url;
            $params['action_name'] = Vocabulary::hotTitle($slot['hotIndex']);
            $params['pv_id'] = $pageViewId;
            $this->counters['pageviews']++;
        } elseif (1 === $typeIndex) {
            $params['url'] = 'https://' . Vocabulary::hotUrl($slot['hotIndex']);
            $params['e_c'] = Vocabulary::eventCategory($rng->nextInt(0, 199));
            $params['e_a'] = Vocabulary::eventAction($rng->nextInt(0, 1999));
            $params['e_n'] = Vocabulary::eventName($rng->nextInt(0, 9999));
            $this->counters['events']++;
        } elseif (2 === $typeIndex) {
            $params['url'] = 'https://example.org/search';
            $params['search'] = Vocabulary::searchKeyword($rng->nextInt(0, 9999));
            $params['search_cat'] = 'cat-' . $rng->nextInt(0, 19);
            $params['search_count'] = $rng->nextInt(0, 200);
            $this->counters['searches']++;
        } elseif (3 === $typeIndex) {
            $params['url'] = 'https://' . Vocabulary::hotUrl($slot['hotIndex']);
            $params['download'] = 'https://' . Vocabulary::download($rng->nextInt(0, 49999));
            $this->counters['downloads']++;
        } else {
            $params['url'] = 'https://' . Vocabulary::hotUrl($slot['hotIndex']);
            $params['link'] = 'https://' . Vocabulary::outlink($rng->nextInt(0, 49999));
            $this->counters['outlinks']++;
        }

        if ($isFirst) {
            $params['urlref'] = $this->entryReferrer($rng);
            $this->counters['visits']++;
        } elseif (null !== $slot['lastUrl']) {
            $params['urlref'] = $slot['lastUrl'];
        }

        // The user id deliberately arrives on the second action, not the first: that is what makes
        // the tracker re-identify an existing visit and rewrite idvisitor across the log tables.
        if ($slot['rewriteUserId'] && 1 === $slot['cursor']) {
            $params['uid'] = 'user_' . $slot['ordinal'];
            $this->counters['userIdRewrites']++;
        } elseif ($slot['rewriteUserId'] && $slot['cursor'] > 1) {
            $params['uid'] = 'user_' . $slot['ordinal'];
        }

        if ($isLast && $slot['converts']) {
            $params['idgoal'] = $rng->nextInt(1, Profile::GOAL_COUNT);
            $this->counters['goalConversions']++;
        }

        $this->send($params);

        // Only a page view becomes the referrer of the next action; a download or an outlink
        // leaves the visitor where they were.
        if (0 === $typeIndex) {
            $slot['lastUrl'] = $params['url'];
        }

        // PagePerformance arrives as its own request carrying the same pv_id, which is why it has
        // to look the action up before it can update it.
        if (null !== $pageViewId && $rng->nextBool($this->pagePerformanceShare)) {
            $this->send($this->performanceParams($slot, $params['url'], $pageViewId, $rng));
            $this->counters['performancePings']++;
        }

        if ($isLast && $slot['orders']) {
            $this->send($this->orderParams($slot, $rng));
            $this->counters['ecommerceOrders']++;
        }

        $slot['cursor']++;

        if ($slot['cursor'] >= $slot['actions']) {
            return null;
        }

        $slot['dueAt'] = $now + $this->actionGapSeconds;

        return $slot;
    }

    private function baseParams(array $slot): array
    {
        $visitor = $slot['visitor'];

        $params = [
            'idsite' => $slot['idSite'],
            'rec' => 1,
            'apiv' => 1,
            // The JS tracker sends the visitor id it holds in a first-party cookie. Without it
            // the tracker falls back to fingerprint matching on config_id, which is the
            // cookieless case - and the two produce noticeably different amounts of idvisitor
            // rewriting, which is worth being able to measure separately.
            '_id' => $this->sendVisitorIdCookie ? $visitor->idVisitorHex : null,
            'ua' => self::userAgentFor($slot['ordinal']),
            'lang' => $visitor->language,
            'res' => $visitor->resolution,
            'cookie' => 1,
            'send_image' => 0,
            'dimension1' => $visitor->customDimensions[1],
            'dimension2' => $visitor->customDimensions[2],
            'dimension3' => $visitor->customDimensions[3],
            'dimension4' => $visitor->customDimensions[4],
        ];

        // cip needs an authenticated request, and without it every visit shares one IP address.
        // That is not a cosmetic problem: config_id - the fingerprint the tracker falls back on to
        // match a visit - is built from the IP, so a constant IP collapses the fingerprint space,
        // unrelated visitors get matched to each other's open visits, and the tracker rewrites
        // idvisitor across four tables to "correct" them. An empty location_ip also leaves
        // geolocation guessing from browser language.
        $params['cip'] = $this->ipFor($visitor);

        return $params;
    }

    /**
     * A per-visitor user agent, with the browser's major version varied.
     *
     * config_id - the fingerprint the tracker falls back on to match a visit - is built from the
     * browser name and version, the OS, the language and the IP. With a handful of fixed user
     * agents, thousands of concurrent visitors collide on the same fingerprint, and the tracker
     * spends its time matching one visitor's request to another's open visit and rewriting
     * idvisitor across all four log tables. That is an artefact of the load generator, not of
     * Matomo, and it was inflating the most expensive statement in the report several-fold.
     *
     * Production has effectively unbounded user agent variety; this gets close enough that
     * fingerprint collisions stop dominating.
     */
    private static function userAgentFor(int $ordinal): string
    {
        $base = Vocabulary::USER_AGENTS[$ordinal % count(Vocabulary::USER_AGENTS)];
        $version = 100 + intdiv($ordinal, count(Vocabulary::USER_AGENTS)) % 40;

        // Whatever the browser, the major version is the first number after its name; replacing
        // it is what changes the version DeviceDetector reports and therefore the fingerprint.
        return preg_replace_callback(
            '#(Chrome/|Firefox/|Version/|CriOS/|Edg/|rv:)(\d+)#',
            static function (array $match) use ($version): string {
                return $match[1] . $version;
            },
            $base,
            1
        );
    }

    private function performanceParams(array $slot, string $url, string $pageViewId, Rng $rng): array
    {
        return $this->baseParams($slot) + [
            'url' => $url,
            'pv_id' => $pageViewId,
            'pf_net' => $rng->nextInt(5, 400),
            'pf_srv' => $rng->nextInt(20, 900),
            'pf_tfr' => $rng->nextInt(5, 500),
            'pf_dm1' => $rng->nextInt(30, 1200),
            'pf_dm2' => $rng->nextInt(50, 2500),
            'pf_onl' => $rng->nextInt(10, 800),
            'ping' => 1,
        ];
    }

    private function orderParams(array $slot, Rng $rng): array
    {
        $subtotal = round($rng->nextLogNormal(48.0, 0.9), 2);
        $tax = round($subtotal * 0.19, 2);
        $shipping = $rng->nextBool(0.6) ? 4.95 : 0.0;
        $itemCount = $rng->pickFromCdf(Profile::ECOMMERCE_ITEMS_CDF) + 1;

        $items = [];
        for ($i = 0; $i < $itemCount; $i++) {
            $sku = $rng->nextInt(0, 29999);
            $items[] = [
                Vocabulary::sku($sku),
                Vocabulary::productName($sku),
                Vocabulary::productCategory($sku % 500),
                round($rng->nextLogNormal(18.0, 0.7), 2),
                $rng->nextInt(1, 3),
            ];
        }

        return $this->baseParams($slot) + [
            'url' => 'https://example.org/checkout/confirmation',
            'idgoal' => 0,
            'ec_id' => sprintf('churn-%s-%d-%d', $this->runNonce, $slot['ordinal'], $slot['cursor']),
            'revenue' => round($subtotal + $tax + $shipping, 2),
            'ec_st' => $subtotal,
            'ec_tx' => $tax,
            'ec_sh' => $shipping,
            'ec_items' => json_encode($items),
        ];
    }

    private function entryReferrer(Rng $rng): string
    {
        $typeIndex = $rng->pickFromCdf(Vocabulary::REFERRER_TYPE_CDF);

        switch (Vocabulary::REFERRER_TYPES[$typeIndex]) {
            case 2:
                return 'https://www.google.com/search?q=' . urlencode(Vocabulary::searchKeyword($rng->nextInt(0, 9999)));
            case 3:
                return 'https://' . Vocabulary::referrerWebsite($rng->nextZipfRank(5000, 1.0) - 1) . '/article/1';
            case 6:
                return 'https://example.org/?pk_campaign=' . Vocabulary::campaignName($rng->nextInt(0, 199))
                    . '&pk_kwd=' . Vocabulary::CAMPAIGN_MEDIUMS[$rng->nextInt(0, 5)];
            default:
                return '';
        }
    }

    private function ipFor(VisitorProfile $visitor): string
    {
        $hex = substr($visitor->ipHex, 0, 8);

        return sprintf(
            '%d.%d.%d.%d',
            max(1, hexdec(substr($hex, 0, 2))),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            hexdec(substr($hex, 6, 2))
        );
    }

    private function walk(Rng $rng, int $currentIndex): int
    {
        $poolSize = $this->hotPoolSize();

        if ($rng->nextBool(0.6)) {
            return ($currentIndex + $rng->nextInt(1, 40) * count(Vocabulary::SECTIONS)) % $poolSize;
        }

        return $rng->nextZipfRank($poolSize, Profile::ZIPF_EXPONENT) - 1;
    }

    private function hotPoolSize(): int
    {
        return $this->profile->getHotPoolSize();
    }

    /**
     * The one place a tracking request is issued. Everything that happens after this line is
     * Matomo's, not ours.
     */
    private function send(array $params): void
    {
        $params = array_filter($params, static function ($value): bool {
            return null !== $value && '' !== $value;
        });

        try {
            $this->tracker->trackRequest(new Request($params, $this->tokenAuth));
            $this->counters['requests']++;
        } catch (\Throwable $e) {
            $this->counters['errors']++;

            if ($this->counters['errors'] <= 3) {
                $this->counters['lastError'] = substr($e->getMessage(), 0, 300);
            }
        }
    }
}
