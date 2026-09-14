<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Session\SaveHandler;

use Piwik\Common;
use Piwik\Session;
use Piwik\Session\SessionFingerprint;
use Zend_Session;

/**
 * Three-way merge of stored session data.
 *
 * The session is stored as one blob, so two requests that changed different parts of it used to
 * overwrite each other. This merges the value a request wants to store with the value another
 * request stored in the meantime, using the value the request originally read as the base.
 *
 * Everything here works on the session array itself, never on the stored string: the stored
 * string is a single opaque key that Zend_Session wraps the whole session in, so merging it
 * directly would only ever see one value and fall back to keeping the newest.
 */
class SessionDataMerger
{
    /**
     * Keys identifying the session itself. Removing one of these is how a logout takes effect,
     * so a concurrent change must never bring one back.
     */
    private const IDENTITY_KEYS = [
        SessionFingerprint::USER_NAME_SESSION_VAR_NAME,
        SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME,
        SessionFingerprint::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED,
        SessionFingerprint::SESSION_INFO_TWO_FACTOR_AUTH_VERIFIED_USER,
        SessionFingerprint::SESSION_INFO_TEMP_TOKEN_AUTH,
    ];

    /**
     * Merges the session this request wants to store with the one currently stored.
     *
     * @param string $base   the session as this request read it
     * @param string $mine   the session this request wants to store
     * @param string $theirs the session that is stored now
     * @return string|null the merged session, or null when any of them cannot be read
     */
    public function merge($base, $mine, $theirs)
    {
        $baseData = $this->decode($base);
        $myData = $this->decode($mine);
        $theirData = $this->decode($theirs);

        if ($baseData === null || $myData === null || $theirData === null) {
            return null;
        }

        $merged = $this->mergeArrays(
            $this->removeNulls($baseData),
            $this->removeNulls($myData),
            $this->removeNulls($theirData)
        );

        return $this->encode($merged);
    }

    /**
     * @return array|null null when the value is not a session written by Matomo
     */
    public function decode($data)
    {
        if ($data === '' || $data === null) {
            return [];
        }

        // the envelope itself never holds an object, only the session inside it may
        $envelope = Common::safe_unserialize($data);

        if (!is_array($envelope) || array_keys($envelope) !== ['data'] || !is_string($envelope['data'])) {
            return null;
        }

        $session = Common::safe_unserialize(base64_decode($envelope['data']), Session::SESSION_DATA_ALLOWED_CLASSES);

        return is_array($session) ? $session : null;
    }

    public function encode(array $session)
    {
        return serialize(Zend_Session::buildSessionData($session));
    }

    /**
     * Merges one level of the session. Public so the rules below can be tested on their own.
     */
    public function mergeArrays(array $base, array $mine, array $theirs)
    {
        $merged = [];
        $keys = array_keys($base + $mine + $theirs);

        foreach ($keys as $key) {
            $inBase = array_key_exists($key, $base);
            $inMine = array_key_exists($key, $mine);
            $inTheirs = array_key_exists($key, $theirs);

            $baseValue = $inBase ? $base[$key] : null;
            $myValue = $inMine ? $mine[$key] : null;
            $theirValue = $inTheirs ? $theirs[$key] : null;

            // both sides agree, including both having removed it
            if ($inMine === $inTheirs && (!$inMine || $this->isSame($myValue, $theirValue))) {
                if ($inMine) {
                    $merged[$key] = $myValue;
                }
                continue;
            }

            $changedByMe = $inBase ? (!$inMine || !$this->isSame($myValue, $baseValue)) : $inMine;
            $changedByThem = $inBase ? (!$inTheirs || !$this->isSame($theirValue, $baseValue)) : $inTheirs;

            if (!$changedByMe) {
                if ($inTheirs) {
                    $merged[$key] = $theirValue;
                }
                continue;
            }

            if (!$changedByThem) {
                if ($inMine) {
                    $merged[$key] = $myValue;
                }
                continue;
            }

            // one side removed it while the other changed it. the side that removed it had seen
            // the value - a consumed nonce, or a logout - so removing wins.
            if (!$inMine || !$inTheirs) {
                if (!$inMine && $this->isReplacedNonce($key, $baseValue, $theirValue)) {
                    $merged[$key] = $theirValue;
                }
                continue;
            }

            // both changed it. merge a level deeper when each side is a map, so that entries
            // stored under their own key - notifications, for instance - do not replace each other.
            $nestedBase = $inBase ? $baseValue : [];

            if ($this->isMap($nestedBase) && $this->isMap($myValue) && $this->isMap($theirValue)) {
                $merged[$key] = $this->mergeArrays($nestedBase, $myValue, $theirValue);
                continue;
            }

            $merged[$key] = $myValue;
        }

        return $merged;
    }

    /**
     * Whether the other request replaced a nonce this request consumed. A namespace written by
     * Nonce holds nothing but the nonce itself, so a different value there is a new nonce that
     * nobody has used yet, and removing it would only force the form to be reloaded.
     */
    private function isReplacedNonce($key, $base, $theirs)
    {
        if (in_array($key, self::IDENTITY_KEYS, true)) {
            return false;
        }

        if (!is_array($base) || !is_array($theirs)) {
            return false;
        }

        if (array_keys($base) !== ['nonce'] || array_keys($theirs) !== ['nonce']) {
            return false;
        }

        return $base['nonce'] !== $theirs['nonce'];
    }

    /**
     * Reading a session value that is not set stores it as null, because Zend returns it by
     * reference. Those are not real values and must not win over what another request stored.
     */
    private function removeNulls(array $data)
    {
        foreach ($data as $key => $value) {
            if (null === $value) {
                unset($data[$key]);
                continue;
            }

            if (!is_array($value) || [] === $value) {
                continue;
            }

            $value = $this->removeNulls($value);

            if ([] === $value) {
                unset($data[$key]);
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * A map is keyed by something meaningful, so entries can be merged by key. A list is not:
     * two requests appending to one both write to the same position.
     */
    private function isMap($value)
    {
        if (!is_array($value)) {
            return false;
        }

        return [] === $value || array_keys($value) !== range(0, count($value) - 1);
    }

    private function isSame($left, $right)
    {
        // the session may hold objects, which must be compared by what they contain
        return serialize($left) === serialize($right);
    }
}
