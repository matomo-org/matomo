<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

/**
 * A link a listener offers next to its usage figures or its denial, for example
 * "Buy more credits" pointing at a billing page. See
 * {@link AIRequestDecision::reportUsage()} and {@link AIRequestDecision::deny()}.
 *
 * The URL is guaranteed to be relative or http(s), not safe HTML: callers still escape
 * the URL and the label when rendering them.
 */
final class AIUsageAction
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $url;

    /**
     * @param string $label Translated link text.
     * @param string $url   A Matomo-relative URL such as `index.php?module=...`, or an absolute http(s) URL.
     */
    public function __construct(string $label, string $url)
    {
        if (trim($label) === '') {
            throw new \InvalidArgumentException('An AI usage action needs a label.');
        }

        if (!self::isSupportedUrl($url)) {
            throw new \InvalidArgumentException('An AI usage action URL must be relative or http(s).');
        }

        $this->label = $label;
        $this->url = $url;
    }

    /**
     * Whitespace and control characters are refused anywhere because browsers strip them before
     * parsing, which would turn `java\tscript:` into a scheme or `/\t/host` into another host.
     */
    private static function isSupportedUrl(string $url): bool
    {
        // Every check fails closed: preg_match() returns false on a PCRE error.
        if ($url === '' || preg_match('/[\x00-\x20\x7F]/', $url) !== 0) {
            return false;
        }

        if (preg_match('~^https?://[^/\\\\?#]~i', $url) === 1) {
            return true;
        }

        // Relative: a colon before the first `/`, `?` or `#` is a scheme, and browsers read a
        // leading `//`, `\\`, `/\` or `\/` as a link to another host. No regex for the scheme,
        // whose backtracking on a long URL can exhaust the PCRE limit.
        $beforePath = substr($url, 0, strcspn($url, '/?#'));

        return !str_contains($beforePath, ':') && preg_match('~^[/\\\\]{2}~', $url) === 0;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
