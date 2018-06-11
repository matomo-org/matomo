<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Session\SessionNamespace;

/**
 * Nonce class.
 *
 * A cryptographic nonce -- "number used only once" -- is often recommended as
 * part of a robust defense against cross-site request forgery (CSRF/XSRF). This
 * class provides static methods that create and manage nonce values.
 *
 * Nonces in Piwik are stored as a session variable and have a configurable expiration.
 *
 * Learn more about nonces [here](http://en.wikipedia.org/wiki/Cryptographic_nonce).
 *
 * @api
 */
class Nonce
{
    /**
     * Returns an existing nonce by ID. If none exists, a new nonce will be generated.
     *
     * @param string $id Unique id to avoid namespace conflicts, e.g., `'ModuleName.ActionName'`.
     * @param int $ttl Optional time-to-live in seconds; default is 5 minutes. (ie, in 5 minutes,
     *                 the nonce will no longer be valid).
     * @return string
     */
    public static function getNonce($id, $ttl = 600)
    {
        // save session-dependent nonce
        $ns = new SessionNamespace($id);
        $nonce = $ns->nonce;

        // re-use an unexpired nonce (a small deviation from the "used only once" principle, so long as we do not reset the expiration)
        // to handle browser pre-fetch or double fetch caused by some browser add-ons/extensions
        if (empty($nonce)) {
            // generate a new nonce
            $nonce = md5(SettingsPiwik::getSalt() . time() . Common::generateUniqId());
            $ns->nonce = $nonce;
        }

        // extend lifetime if nonce is requested again to prevent from early timeout if nonce is requested again
        // a few seconds before timeout
        $ns->setExpirationSeconds($ttl, 'nonce');

        return $nonce;
    }

    /**
     * Returns if a nonce is valid and comes from a valid request.
     *
     * A nonce is valid if it matches the current nonce and if the current nonce
     * has not expired.
     *
     * The request is valid if the referrer is a local URL (see {@link Url::isLocalUrl()})
     * and if the HTTP origin is valid (see {@link getAcceptableOrigins()}).
     *
     * @param string $id The nonce's unique ID. See {@link getNonce()}.
     * @param string $cnonce Nonce sent from client.
     * @return bool `true` if valid; `false` otherwise.
     */
    public static function verifyNonce($id, $cnonce)
    {
        $ns = new SessionNamespace($id);
        $nonce = $ns->nonce;
        \quickDebug($cnonce . ' - ' . $nonce . "\n");
        // validate token
        if (empty($cnonce) || $cnonce !== $nonce) {
            return false;
        }

        // validate referrer
        $referrer = Url::getReferrer();
        if (!empty($referrer) && !Url::isLocalUrl($referrer)) {
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
     * Force expiration of the current nonce.
     *
     * @param string $id The unique nonce ID.
     */
    public static function discardNonce($id)
    {
        $ns = new SessionNamespace($id);
        $ns->unsetAll();
    }

    /**
     * Returns the **Origin** HTTP header or `false` if not found.
     *
     * @return string|bool
     */
    public static function getOrigin()
    {
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            return $_SERVER['HTTP_ORIGIN'];
        }
        return false;
    }

    /**
     * Returns a list acceptable values for the HTTP **Origin** header.
     *
     * @return array
     */
    public static function getAcceptableOrigins()
    {
        $host = Url::getCurrentHost(null);
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
        $origins = array(
            'http://' . $host,
            'https://' . $host,
        );

        // non-standard ports
        if (!empty($port) && $port != 80 && $port != 443) {
            $origins[] = 'http://' . $host . ':' . $port;
            $origins[] = 'https://' . $host . ':' . $port;
        }

        return $origins;
    }

    /**
     * Verifies and discards a nonce.
     *
     * @param string $nonceName The nonce's unique ID. See {@link getNonce()}.
     * @param string|null $nonce The nonce from the client. If `null`, the value from the
     *                           **nonce** query parameter is used.
     * @throws \Exception if the nonce is invalid. See {@link verifyNonce()}.
     */
    public static function checkNonce($nonceName, $nonce = null)
    {
        if ($nonce === null) {
            $nonce = Common::getRequestVar('nonce', null, 'string');
        }

        if (!self::verifyNonce($nonceName, $nonce)) {
            throw new \Exception(Piwik::translate('General_ExceptionNonceMismatch'));
        }

        self::discardNonce($nonceName);
    }
}
