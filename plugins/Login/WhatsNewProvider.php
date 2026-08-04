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
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin\Manager as PluginManager;

/**
 * Provides the "What's New" entries shown on the shared, pre-authentication login layout
 * (`@Login/loginLayout.twig`, used by Login and TwoFactorAuth).
 *
 * It reuses the existing 6-month change data ({@see ChangesModel::getChangeItems()}), selects the
 * three most recent entries and renders a call-to-action only for links on {@see self::ALLOWED_LINK_HOSTS}.
 * The entry itself (title and description) always stays visible; only its link is removed.
 *
 * Since this runs on a public endpoint, the processed entries are cached across requests
 * ({@see self::loadChanges()}).
 *
 * This is purely decorative content: any failure while gathering the entries is logged and results in
 * an empty list so the login page always renders.
 */
class WhatsNewProvider
{
    /**
     * Number of most recent entries displayed in the panel.
     */
    private const MAX_ENTRIES = 3;

    /**
     * Hosts whose links may render a call to action, each also covering its subdomains.
     *
     * An allowlist rather than a check against this instance's own hostnames: it behaves the same on
     * every installation, and it also keeps a link recorded by a third party plugin from pointing an
     * unauthenticated visitor at an arbitrary domain. Extend this if announcements need to link
     * somewhere else.
     */
    private const ALLOWED_LINK_HOSTS = ['matomo.org'];

    /**
     * Cache id of the processed entries.
     */
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
     * Per-request cache of the processed entries.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private $changes = null;

    public function __construct(ChangesModel $changesModel)
    {
        $this->changesModel = $changesModel;
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
            StaticContainer::get(LoggerInterface::class)->warning(
                'Unable to build the login What\'s New panel entries: {message}',
                ['message' => $e->getMessage(), 'exception' => $e]
            );
            $this->changes = [];
        }

        return $this->changes;
    }

    /**
     * The processed entries, cached across requests.
     *
     * Nothing about them depends on the request, so the finished list is what gets cached - this is
     * a public endpoint, and it would otherwise read the changes table and dispatch
     * `Changes.filterChanges` on every anonymous hit.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadChanges(): array
    {
        $cache = Cache::getLazyCache();

        // Folds the loaded plugin list and the language into the id, so activating or deactivating
        // a plugin invalidates immediately. Nothing request specific is part of the cached value,
        // so the id stays fixed and cannot be influenced by a visitor.
        $cacheKey = CacheId::pluginAware(self::CACHE_KEY);

        $cached = $cache->fetch($cacheKey);

        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $changes = $this->buildChanges();

        // An empty result is deliberately NOT cached. It happens on a fresh install (and in the UI
        // test fixture) where the login page can be reached before any change has been recorded, and
        // pinning it would leave the panel empty for a whole cache lifetime after the entries exist.
        // The query behind an empty result is a single index lookup on a tiny table, so re-running it
        // costs nothing; the case worth caching - an install that does have entries - is fully
        // cached. Core and plugin updates flush this cache anyway
        // (Filesystem::deleteAllCacheOnUpdate()), so the lifetime is only a backstop for entries
        // added outside an update.
        if (!empty($changes)) {
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

            if ($this->isDisplayableExternalLink($link, $linkName)) {
                $entry['link'] = $link;
                $entry['link_name'] = $linkName;
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
     * A link only renders its CTA when it is an absolute http/https URL on an allowed host
     * ({@see self::ALLOWED_LINK_HOSTS}), with both a URL and a label. Everything else - relative and
     * internal links, protocol-relative links, invalid URLs, non-http schemes and any other host -
     * has its CTA stripped while the entry itself stays visible.
     *
     * @param mixed $link
     * @param mixed $linkName
     */
    private function isDisplayableExternalLink($link, $linkName): bool
    {
        if (!is_string($link) || !is_string($linkName)) {
            return false;
        }

        $link = trim($link);
        $linkName = trim($linkName);

        if ($link === '' || $linkName === '') {
            return false;
        }

        // Protocol-relative URLs (//example.org) inherit the current scheme and can point anywhere.
        if (strpos($link, '//') === 0) {
            return false;
        }

        $parsed = @parse_url($link);

        if (!is_array($parsed)) {
            return false;
        }

        // Must be an absolute URL with an explicit scheme and host.
        if (empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        $scheme = strtolower($parsed['scheme']);

        if ($scheme !== 'http' && $scheme !== 'https') {
            return false;
        }

        // parse_url() has already split any port off into its own component, so the host needs
        // nothing beyond case folding and dropping a trailing root dot.
        $host = rtrim(strtolower($parsed['host']), '.');

        return $this->isAllowedLinkHost($host);
    }

    /**
     * Whether a host may render a call to action: an exact match on an allowed host, or one of its
     * subdomains. Anchoring the suffix on a dot is what keeps look-alikes such as
     * `evil-matomo.org` and `matomo.org.example.com` out.
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
