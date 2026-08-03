<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Piwik\Changes\Model as ChangesModel;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\SettingsPiwik;
use Piwik\Url;

/**
 * Provides the "What's New" entries shown on the shared, pre-authentication login layout
 * (`@Login/loginLayout.twig`, used by Login and TwoFactorAuth).
 *
 * It reuses the existing 6-month change data ({@see ChangesModel::getChangeItems()}), selects the
 * three most recent entries and strips any call-to-action link that is not a safe, external link so
 * that internal/instance links are never exposed to unauthenticated visitors. The entry itself (title
 * and description) always stays visible; only its link is removed.
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
            $this->changes = $this->buildChanges();
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
     * @return array<int, array<string, mixed>>
     */
    private function buildChanges(): array
    {
        // WhiteLabel installs must not reveal any Matomo name, announcement or link.
        if ($this->isWhiteLabelActive()) {
            return [];
        }

        $changes = $this->changesModel->getChangeItems();

        if (empty($changes)) {
            return [];
        }

        // Select the three most recent entries first (the model already orders them by recency),
        // then strip unsafe links from the selection.
        $selected = array_slice($changes, 0, self::MAX_ENTRIES);

        $pluginManager = PluginManager::getInstance();
        $instanceHosts = $this->getInstanceHosts();

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

            if ($this->isDisplayableExternalLink($link, $linkName, $instanceHosts)) {
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
     * A link is only shown (its CTA rendered) when it is a genuine external link: an absolute
     * http/https URL that does not point back at this Matomo instance, with both a URL and a label.
     * Everything else (relative/internal links, same-instance links, protocol-relative links, invalid
     * URLs and non-http schemes) has its CTA stripped while the entry itself stays visible.
     *
     * @param mixed         $link
     * @param mixed         $linkName
     * @param array<string> $instanceHosts Lower-cased hosts that identify this instance.
     */
    private function isDisplayableExternalLink($link, $linkName, array $instanceHosts): bool
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

        $host = strtolower($this->stripPort($parsed['host']));

        if (in_array($host, $instanceHosts, true)) {
            return false;
        }

        return true;
    }

    /**
     * Lower-cased hosts that identify this Matomo instance, used to reject same-instance links.
     *
     * @return array<string>
     */
    private function getInstanceHosts(): array
    {
        $hosts = [];

        try {
            $currentHost = Url::getHost(false);
            if (is_string($currentHost) && $currentHost !== '') {
                $hosts[] = strtolower($this->stripPort($currentHost));
            }
        } catch (\Throwable $e) {
            // ignore - resolving the current host is best-effort
        }

        try {
            $piwikUrl = SettingsPiwik::getPiwikUrl();
            $parsed = @parse_url((string) $piwikUrl);
            if (is_array($parsed) && !empty($parsed['host'])) {
                $hosts[] = strtolower($this->stripPort($parsed['host']));
            }
        } catch (\Throwable $e) {
            // ignore - the configured Matomo URL is best-effort
        }

        return array_values(array_unique($hosts));
    }

    private function stripPort(string $host): string
    {
        return explode(':', $host, 2)[0];
    }
}
