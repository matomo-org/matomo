<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Fires a single tracking request with a chosen consent state, then shows what landed.
 *
 * Demo helper for the consent-aware tracking investigation. Not intended for merge.
 *
 *     ddev exec php consent-track.php [options]
 *
 * Options
 *     --site=N         site to track against. 2 = masking enforced, 3 = nothing enforced (default 2)
 *     --consent=1|0    the consent decision to send. Omit entirely to send no signal at all,
 *                      which is what a site not using consent looks like
 *     --visitor=HEX    16 hex chars. Reuse the same value to add actions to the same visit;
 *                      omit to start a new visitor
 *     --page=PATH      page path to track (default landing)
 *     --campaign       arrive on a campaign URL. Only meaningful on the first action of a visit,
 *                      since that is where entry attribution is decided
 *     --status         skip tracking, just print the current state of both demo sites
 *
 * Notes
 *     Requests are tracked at the current time. A backdated request would need a token, and the
 *     'cip' parameter is rejected without one, so every visit shares this machine's IP - visits
 *     are kept apart by the visitor id alone.
 */

namespace Piwik;

use Piwik\Application\Environment;
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
Plugin\Manager::getInstance()->loadActivatedPlugins();
Access::getInstance()->setSuperUserAccess(true);

const TRACKER_URL = 'http://localhost/matomo.php';
const CAMPAIGN = 'spring-sale';
const KEYWORD = 'running-shoes';

/** @return array<string, string|bool> */
function options(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $argument) {
        if (!preg_match('/^--([a-z]+)(?:=(.*))?$/', $argument, $matches)) {
            fwrite(STDERR, "unrecognised argument: $argument\n");
            exit(1);
        }

        $options[$matches[1]] = $matches[2] ?? true;
    }

    return $options;
}

function showState(): void
{
    $rows = Db::fetchAll(
        'SELECT v.idsite,
                v.idvisit,
                LOWER(HEX(v.idvisitor)) AS visitor,
                v.consent AS visit_consent,
                v.referer_name,
                v.config_resolution,
                GROUP_CONCAT(COALESCE(a.consent, "-") ORDER BY a.idlink_va) AS action_consents
           FROM ' . Common::prefixTable('log_visit') . ' v
           LEFT JOIN ' . Common::prefixTable('log_link_visit_action') . ' a ON a.idvisit = v.idvisit
          WHERE v.idsite IN (2, 3)
          GROUP BY v.idvisit
          ORDER BY v.idsite, v.idvisit'
    );

    if (empty($rows)) {
        echo "no visits on the demo sites yet\n";
        return;
    }

    $format = "%-5s %-7s %-18s %-16s %-13s %-26s %s\n";
    $header = sprintf(
        $format,
        'site',
        'visit',
        'visitor',
        'consent summary',
        'per action',
        'entry campaign',
        'resolution'
    );

    echo $header . str_repeat('-', strlen(rtrim($header, "\n"))) . "\n";

    foreach ($rows as $row) {
        printf(
            $format,
            $row['idsite'],
            $row['idvisit'],
            $row['visitor'],
            null === $row['visit_consent'] ? 'null' : $row['visit_consent'],
            (string) $row['action_consents'],
            (string) $row['referer_name'],
            (string) $row['config_resolution']
        );
    }

    echo "\nconsent summary: null no signal | 0 declined | 1 consented throughout"
        . " | 2 consented part way\n";
}

$options = options($_SERVER['argv']);

if (isset($options['status'])) {
    showState();
    exit(0);
}

$idSite = (int) ($options['site'] ?? 2);
$page = (string) ($options['page'] ?? 'landing');
$visitorId = isset($options['visitor']) && is_string($options['visitor'])
    ? $options['visitor']
    : bin2hex(random_bytes(8));

if (!preg_match('/^[0-9a-f]{16}$/', $visitorId)) {
    fwrite(STDERR, "--visitor must be exactly 16 hex characters\n");
    exit(1);
}

$consent = null;
if (isset($options['consent'])) {
    $consent = (string) $options['consent'];

    if (!in_array($consent, ['0', '1'], true)) {
        fwrite(STDERR, "--consent must be 0 or 1, or omitted to send no signal\n");
        exit(1);
    }
}

TrackerCache::deleteTrackerCache();

$tracker = new \MatomoTracker($idSite, TRACKER_URL);
$tracker->setVisitorId($visitorId);
$tracker->setUserAgent(
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko)'
    . ' Chrome/126.0.0.0 Safari/537.36'
);
$tracker->setBrowserLanguage('fr');
$tracker->setResolution(1920, 1080);
$tracker->setBrowserHasCookies(true);

if (null !== $consent) {
    $tracker->setCustomTrackingParameter('consent', $consent);
}

$url = 'https://demo.example.com/' . ltrim($page, '/');
if (isset($options['campaign'])) {
    $url .= '?' . http_build_query(['pk_campaign' => CAMPAIGN, 'pk_kwd' => KEYWORD]);
}

$tracker->setUrl($url);
$tracker->setUrlReferrer('');

// The request goes over HTTP to the real tracker endpoint, which is the point - it should be an
// ordinary tracking request. That does mean it competes for PHP-FPM workers, and a running test
// suite can saturate them for seconds at a time, so allow generously and retry rather than dying
// on a transient queue.
$tracker->setRequestTimeout(30);

$response = '';

for ($attempt = 1; $attempt <= 3; $attempt++) {
    try {
        $response = (string) $tracker->doTrackPageView(ucfirst(str_replace(['-', '/'], ' ', $page)));
    } catch (\Exception $e) {
        echo "attempt $attempt could not reach the tracker: " . $e->getMessage() . "\n";

        if ($attempt === 3) {
            echo "\nthe tracker endpoint is not responding. If a test suite is running it is holding\n";
            echo "the PHP-FPM workers - wait for it to finish, or check: ddev exec curl -I http://localhost/matomo.php\n";
            exit(1);
        }

        sleep(2);
        continue;
    }

    break;
}

if (false === strpos($response, 'GIF89a')) {
    echo "the tracker rejected the request: " . substr(trim(strip_tags($response)), 0, 200) . "\n";
    exit(1);
}

printf(
    "tracked  site=%d  consent=%s  visitor=%s  page=%s%s\n\n",
    $idSite,
    null === $consent ? 'none' : $consent,
    $visitorId,
    $page,
    isset($options['campaign']) ? '  (campaign entry)' : ''
);

echo "reuse this visitor to add to the same visit:\n";
echo "  --visitor=$visitorId\n\n";

showState();
