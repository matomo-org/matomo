<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Seeds this instance with the visitor archetypes that demonstrate consent-aware tracking.
 *
 * Demo helper for the consent-aware tracking investigation. Not intended for merge.
 *
 *     ddev exec php consent-demo-seed.php
 *
 * Creates two sites, so the rule "consent lifts a restriction, it never imposes one" is visible
 * from both sides:
 *
 *     site A   campaign masking enforced   the interesting one
 *     site B   nothing enforced            proves consent alone restricts nobody
 *
 * and tracks four archetypes on each. Every visit arrives on a campaign URL and performs four
 * actions, so the entry attribution and the mid-visit transition are both observable:
 *
 *     no signal    the consent parameter is never sent              visit -> null
 *     declined     every action says consent=0                      visit -> 0
 *     consented    every action says consent=1                      visit -> 1
 *     converted    starts at consent=0, consents on action three    visit -> 2
 */

namespace Piwik;

use Piwik\Application\Environment;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tracker\Cache as TrackerCache;

if (!defined('PIWIK_DOCUMENT_ROOT')) {
    define('PIWIK_DOCUMENT_ROOT', realpath(dirname(__FILE__)));
}

define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);

require_once PIWIK_DOCUMENT_ROOT . '/index.php';

if (!Common::isPhpCliMode()) {
    return;
}

$environment = new Environment('cli');
$environment->init();

// dispatch is off, so the activated plugins are not loaded for us. Without this the measurable
// type registry is empty and addSite() rejects the type it ships with.
Plugin\Manager::getInstance()->loadActivatedPlugins();

Access::getInstance()->setSuperUserAccess(true);

const CAMPAIGN = 'spring-sale';
const KEYWORD = 'running-shoes';
const TRACKER_URL = 'http://localhost/matomo.php';

/** Resolves a site by name, creating it if it is not there yet, so the script is re-runnable. */
function ensureSite(string $name): int
{
    foreach (SitesManagerAPI::getInstance()->getAllSites() as $site) {
        if ($site['name'] === $name) {
            return (int) $site['idsite'];
        }
    }

    return (int) SitesManagerAPI::getInstance()->addSite($name, ['https://demo.example.com']);
}

/**
 * One visitor's journey. Actions one and two carry the entry decision, three and four the later
 * one, which is what produces the mid-visit transition for the "converted" archetype.
 *
 * @return array<int, array{page: string, title: string, consent: string|null, campaign: bool}>
 */
function journey(?string $entryConsent, ?string $laterConsent): array
{
    return [
        ['page' => 'landing', 'title' => 'Landing page', 'consent' => $entryConsent, 'campaign' => true],
        ['page' => 'catalogue', 'title' => 'Catalogue', 'consent' => $entryConsent, 'campaign' => false],
        ['page' => 'product/trail-shoe', 'title' => 'Trail shoe', 'consent' => $laterConsent, 'campaign' => false],
        ['page' => 'basket', 'title' => 'Basket', 'consent' => $laterConsent, 'campaign' => false],
    ];
}

/**
 * @param array<int, array{page: string, title: string, consent: string|null, campaign: bool}> $actions
 */
function trackVisit(int $idSite, string $label, string $visitorId, string $ip, array $actions): void
{
    $tracker = new \MatomoTracker($idSite, TRACKER_URL);
    $tracker->setVisitorId($visitorId);
    $tracker->setIp($ip);
    $tracker->setUserAgent(
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko)'
        . ' Chrome/126.0.0.0 Safari/537.36'
    );
    $tracker->setBrowserLanguage('fr');
    $tracker->setResolution(1920, 1080);
    $tracker->setBrowserHasCookies(true);
    // a running test suite can hold every PHP-FPM worker for seconds, and the default 5s gives up
    // in the middle of that. See the same note in consent-track.php.
    $tracker->setRequestTimeout(30);

    foreach ($actions as $action) {
        $tracker->clearCustomTrackingParameters();

        if (null !== $action['consent']) {
            $tracker->setCustomTrackingParameter('consent', $action['consent']);
        }

        $url = 'https://demo.example.com/' . $action['page'];
        if ($action['campaign']) {
            $url .= '?' . http_build_query(['pk_campaign' => CAMPAIGN, 'pk_kwd' => KEYWORD]);
        }

        $tracker->setUrl($url);
        $tracker->setUrlReferrer('');

        $response = (string) $tracker->doTrackPageView($action['title']);

        if (false === strpos($response, 'GIF89a')) {
            echo "    ! tracking failed on {$action['page']}: "
                . substr(trim(strip_tags($response)), 0, 160) . "\n";
        }
    }

    echo "    $label\n";
}

$sites = [
    'enforced' => ensureSite('Consent demo - masking enforced'),
    'open' => ensureSite('Consent demo - nothing enforced'),
];

PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, $sites['enforced']);
PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $sites['open']);
TrackerCache::deleteTrackerCache();

// label, entry consent, later consent
$archetypes = [
    ['no signal sent', null, null],
    ['declined throughout', '0', '0'],
    ['consented throughout', '1', '1'],
    ['consented on action three', '0', '1'],
];

$seed = 0;

foreach ($sites as $tag => $idSite) {
    echo "site $idSite ($tag):\n";

    foreach ($archetypes as [$label, $entry, $later]) {
        $seed++;
        trackVisit(
            $idSite,
            $label,
            str_pad(dechex($seed * 0x1d872b41), 16, '0', STR_PAD_LEFT),
            '203.0.113.' . (10 + $seed),
            journey($entry, $later)
        );
    }

    echo "\n";
}

// Read back what actually landed, so the script demonstrates the behaviour rather than asserting it
$rows = Db::fetchAll(
    'SELECT v.idsite,
            v.idvisit,
            v.consent AS visit_consent,
            v.referer_name,
            v.config_resolution,
            GROUP_CONCAT(COALESCE(a.consent, "-") ORDER BY a.idlink_va) AS action_consents,
            COUNT(a.idlink_va) AS actions
       FROM ' . Common::prefixTable('log_visit') . ' v
       LEFT JOIN ' . Common::prefixTable('log_link_visit_action') . ' a ON a.idvisit = v.idvisit
      WHERE v.idsite IN (?, ?)
      GROUP BY v.idvisit
      ORDER BY v.idsite, v.idvisit',
    [$sites['enforced'], $sites['open']]
);

$rowFormat = "%-5s %-16s %-8s %-11s %-30s %s";

$header = sprintf(
    $rowFormat,
    'site',
    'consent summary',
    'actions',
    'per action',
    'entry campaign',
    'resolution'
);

echo "what landed\n" . str_repeat('-', strlen($header)) . "\n" . $header . "\n";

foreach ($rows as $row) {
    printf(
        $rowFormat . "\n",
        $row['idsite'],
        null === $row['visit_consent'] ? 'null' : $row['visit_consent'],
        $row['actions'],
        (string) $row['action_consents'],
        (string) $row['referer_name'],
        (string) $row['config_resolution']
    );
}

echo "\nconsent summary: null no signal | 0 declined | 1 consented throughout"
    . " | 2 consented part way\n";
echo "one row per visitor means no visit was split by the consent transition\n";
