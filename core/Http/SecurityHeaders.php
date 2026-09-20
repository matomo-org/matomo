<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

use Piwik\Common;
use Piwik\Config;
use Piwik\Url;
use Piwik\View\SecurityPolicy;

/**
 * Sends the response headers for a data response: one that carries data rather than application UI.
 */
class SecurityHeaders
{
    /**
     * Sends the header set for a response that must never be treated as application UI, such as
     * API output, report exports and generated report bodies. Such a response can never be sniffed
     * into another content type, cannot be framed unless `[General] enable_framed_pages` allows it,
     * and while the policy is enabled it also cannot run scripts, submit forms or override its base URI.
     *
     * Must be called before any output is written.
     *
     * @api
     */
    public static function sendForDataResponse(): void
    {
        Common::sendHeader('X-Content-Type-Options: nosniff');
        Common::sendHeader('Referrer-Policy: no-referrer');

        $config = Config::getInstance();

        // a dedicated instance: the shared one carries directives plugins add for UI pages
        $securityPolicy = new SecurityPolicy($config);
        $securityPolicy->restrictToDataResponse();

        // embedding is an operator decision that covers these responses too
        if (empty($config->General['enable_framed_pages'])) {
            Common::sendHeader('X-Frame-Options: deny');
        } else {
            $securityPolicy->removeDirective('frame-ancestors');
        }

        // a generated report references its logo and row icons absolutely, so that they resolve in
        // an email too, and those URLs are not covered by 'self' when Matomo is reachable under
        // more than one host
        $trustedHosts = self::getTrustedHostSources();
        if ([] !== $trustedHosts) {
            $securityPolicy->addPolicy('img-src', implode(' ', $trustedHosts));
        }

        $cspHeader = $securityPolicy->createHeaderString();
        if ('' !== $cspHeader) {
            Common::sendHeader($cspHeader);
        }
    }

    /**
     * The hosts Matomo may be served under, as source expressions. They carry no scheme on purpose:
     * a host source without one matches the scheme of the response itself.
     *
     * @return string[]
     */
    private static function getTrustedHostSources(): array
    {
        try {
            $hosts = Url::getTrustedHostsFromConfig();

            return array_values(array_filter($hosts, static function ($host): bool {
                // a host that could carry anything readable as a further directive is left out, as
                // is a malformed entry: a configured url without a host part is read as null, which
                // the cast turns into a value the pattern rejects rather than a type error
                return preg_match('/^([a-z\d.-]+|\[[a-f\d:]+\])(:\d+)?\z/i', (string) $host) === 1;
            }));
        } catch (\Throwable $e) {
            // the configuration is read on error paths too, where it may be what broke
            return [];
        }
    }
}
