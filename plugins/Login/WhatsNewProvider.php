<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Changes\Model as ChangesModel;
use Piwik\Common;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;

/**
 * The "What's New" entries shown on the login layout (`@Login/loginLayout.twig`, shared by Login
 * and TwoFactorAuth).
 *
 * Takes the three most recent changes and keeps a link only when a logged out visitor could follow
 * it. An entry always keeps its title and description; only the link goes. Any failure is logged
 * and returns an empty list, so the login page always renders.
 */
class WhatsNewProvider
{
    private const MAX_ENTRIES = 3;

    /**
     * Hosts whose links may show a call to action, subdomains included. Anything else keeps its
     * entry and loses the link - including an absolute link back to this instance, which a logged
     * out visitor could not follow either.
     */
    private const ALLOWED_LINK_HOSTS = ['matomo.org'];

    private const CACHE_KEY = 'Login.whatsNewPanel';

    /**
     * Lifetime of the cached entries, in seconds.
     */
    private const CACHE_TTL = 3600;

    /**
     * @var ChangesModel
     */
    private $changesModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Per-request cache of the processed entries.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private $changes = null;

    public function __construct(ChangesModel $changesModel, LoggerInterface $logger)
    {
        $this->changesModel = $changesModel;
        $this->logger = $logger;
    }

    /**
     * Returns the processed What's New entries for the login layout.
     *
     * @return array<int, array<string, mixed>> Each entry has: title, description, plugin_name,
     *                                           showPluginPrefix, link, link_name. Empty on any failure.
     */
    public function getChanges(): array
    {
        if ($this->changes !== null) {
            return $this->changes;
        }

        try {
            $this->changes = $this->loadChanges();
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Unable to build the login What\'s New panel entries: {message}',
                ['message' => $e->getMessage(), 'exception' => $e]
            );
            $this->changes = [];
        }

        return $this->changes;
    }

    /**
     * The finished entries, cached across requests for logged out visitors so this public page
     * doesn't query on every hit.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadChanges(): array
    {
        $cache = Cache::getLazyCache();

        // The plugin list and language are part of the id, so activating a plugin invalidates it.
        $cacheKey = CacheId::pluginAware(self::CACHE_KEY);

        // Only a logged out visitor may read or fill this. `Changes.filterChanges` can rewrite the
        // list per user, so an authenticated request builds its own instead of inheriting somebody
        // else's.
        $isAnonymous = Piwik::isUserIsAnonymous();

        if ($isAnonymous) {
            $cached = $cache->fetch($cacheKey);

            if (is_array($cached) && !empty($cached)) {
                return $cached;
            }
        }

        $changes = $this->buildChanges();

        // Never pin an empty result: a fresh install records its first change eventually, and the
        // panel should show it without waiting out the TTL.
        if ($isAnonymous && !empty($changes)) {
            $cache->save($cacheKey, $changes, self::CACHE_TTL);
        }

        return $changes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildChanges(): array
    {
        // WhiteLabel installs must not reveal any Matomo name, announcement or link.
        if ($this->isWhiteLabelActive()) {
            return [];
        }

        // The model already orders the entries by recency.
        $selected = array_slice($this->changesModel->getChangeItems(), 0, self::MAX_ENTRIES);

        if (empty($selected)) {
            return [];
        }

        $pluginManager = PluginManager::getInstance();

        $result = [];

        foreach ($selected as $change) {
            $pluginName = is_string($change['plugin_name'] ?? null) ? $change['plugin_name'] : '';

            $entry = [
                'title'            => is_string($change['title'] ?? null) ? $change['title'] : '',
                'description'      => is_string($change['description'] ?? null) ? $change['description'] : '',
                'plugin_name'      => $pluginName,
                'showPluginPrefix' => $pluginName !== '' && !$pluginManager->isPluginBundledWithCore($pluginName),
                'link'             => '',
                'link_name'        => '',
            ];

            $link = $change['link'] ?? '';
            $linkName = $change['link_name'] ?? '';

            // Store the trimmed URL: that is what was checked, and `|safelink` rejects a padded one.
            if ($this->isDisplayableExternalLink($link, $linkName)) {
                $entry['link'] = trim($link);
                $entry['link_name'] = trim($linkName);
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function isWhiteLabelActive(): bool
    {
        return PluginManager::getInstance()->isPluginActivated('WhiteLabel');
    }

    /**
     * A link keeps its call to action only when it is an absolute http/https URL on an allowed host
     * and has a label. This is a product filter rather than a security boundary: links come from
     * each plugin's own `changes.json`, so nothing a visitor sends can reach it.
     *
     * @param mixed $link
     * @param mixed $linkName
     */
    private function isDisplayableExternalLink($link, $linkName): bool
    {
        if (!is_string($link) || !is_string($linkName) || trim($linkName) === '') {
            return false;
        }

        // No host means empty or relative; no scheme means protocol-relative.
        $parsed = parse_url(trim($link));

        if (!is_array($parsed) || empty($parsed['host'])) {
            return false;
        }

        if (!in_array(strtolower($parsed['scheme'] ?? ''), ['http', 'https'], true)) {
            return false;
        }

        // Reject userinfo: PHP splits the authority on the last `@`, browsers end the host at the
        // first `\`, so `https://evil.example\@matomo.org/` is matomo.org here and evil.example
        // there. Announcement links never carry credentials.
        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return false;
        }

        // parse_url() already split the port off; just fold case and drop a trailing root dot.
        return $this->isAllowedLinkHost(rtrim(strtolower($parsed['host']), '.'));
    }

    /**
     * An exact match on an allowed host or one of its subdomains. Anchoring the suffix on a dot is
     * what keeps `evil-matomo.org` and `matomo.org.example.com` out.
     */
    private function isAllowedLinkHost(string $host): bool
    {
        foreach (self::ALLOWED_LINK_HOSTS as $allowedHost) {
            if ($host === $allowedHost || Common::stringEndsWith($host, '.' . $allowedHost)) {
                return true;
            }
        }

        return false;
    }
}
