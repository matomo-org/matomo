<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Nonce class.
 *
 * A cryptographic nonce -- "number used only once" -- is often recommended as part of a robust defense against cross-site request forgery (CSRF/XSRF).
 * Desrable characteristics: limited lifetime, uniqueness, unpredictability (pseudo-randomness).
 *
 * We use a session-dependent nonce with a configurable expiration that combines and hashes:
 * - a private salt because it's non-public
 * - time() because it's unique
 * - a mix of PRNGs (pseudo-random number generators) to increase entropy and make it less predictable
 *
 * @package Piwik
 */
class Piwik_Nonce
{
    /**
     * Generate nonce
     *
     * @param string $id   Unique id to avoid namespace conflicts, e.g., ModuleName.ActionName
     * @param int $ttl  Optional time-to-live in seconds; default is 5 minutes
     * @return string  Nonce
     */
    static public function getNonce($id, $ttl = 300)
    {
        // save session-dependent nonce
        $ns = new Piwik_Session_Namespace($id);
        $nonce = $ns->nonce;

        // re-use an unexpired nonce (a small deviation from the "used only once" principle, so long as we do not reset the expiration)
        // to handle browser pre-fetch or double fetch caused by some browser add-ons/extensions
        if (empty($nonce)) {
            // generate a new nonce
            $nonce = md5(Piwik_Common::getSalt() . time() . Piwik_Common::generateUniqId());
            $ns->nonce = $nonce;
            $ns->setExpirationSeconds($ttl, 'nonce');
        }

        return $nonce;
    }

    /**
     * Verify nonce and check referrer (if present, i.e., it may be suppressed by the browser or a proxy/network).
     *
     * @param string $id      Unique id
     * @param string $cnonce  Nonce sent to client
     * @return bool  true if valid; false otherwise
     */
    static public function verifyNonce($id, $cnonce)
    {
        $ns = new Piwik_Session_Namespace($id);
        $nonce = $ns->nonce;

        // validate token
        if (empty($cnonce) || $cnonce !== $nonce) {
            return false;
        }

        // validate referer
        $referer = Piwik_Url::getReferer();
        if (!empty($referer) && !Piwik_Url::isLocalUrl($referer)) {
            return false;
        }

        // validate origin
        $origin = self::getOrigin();
        if (!empty($origin) &&
            ($origin == 'null'
                || !in_array($origin, self::getAcceptableOrigins()))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Discard nonce ("now" as opposed to waiting for garbage collection)
     *
     * @param string $id  Unique id
     */
    static public function discardNonce($id)
    {
        $ns = new Piwik_Session_Namespace($id);
        $ns->unsetAll();
    }

    /**
     * Get ORIGIN header, false if not found
     *
     * @return string|false
     */
    static public function getOrigin()
    {
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            return $_SERVER['HTTP_ORIGIN'];
        }
        return false;
    }

    /**
     * Returns acceptable origins (not simply scheme://host) that
     * should handle a variety of proxy and web server (mis)configurations,.
     *
     * @return array
     */
    static public function getAcceptableOrigins()
    {
        $host = Piwik_Url::getCurrentHost(null);
        $port = '';

        // parse host:port
        if (preg_match('/^([^:]+):([0-9]+)$/D', $host, $matches)) {
            $host = $matches[1];
            $port = $matches[2];
        }

        if (empty($host)) {
            return array();
        }

        // standard ports
        $origins[] = 'http://' . $host;
        $origins[] = 'https://' . $host;

        // non-standard ports
        if (!empty($port) && $port != 80 && $port != 443) {
            $origins[] = 'http://' . $host . ':' . $port;
            $origins[] = 'https://' . $host . ':' . $port;
        }

        return $origins;
    }
}
