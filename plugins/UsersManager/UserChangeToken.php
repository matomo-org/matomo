<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use Piwik\Common;
use Piwik\SettingsPiwik;

// TODO: check docs
class UserChangeToken
{
    /**
     * Generate a password reset token.  Expires in 24 hours from the beginning of the current hour.
     *
     * The reset token is generated using a user's email, login and the time when the token expires.
     *
     * @param array $user The user information.
     * @param string $keySuffix The suffix used in generating a token.
     * @param int|null $expiryTimestamp The expiration timestamp to use or null to generate one from
     *                                  the current timestamp.
     * @return string The generated token.
     */
    public function generateToken($user, $keySuffix, $data, $expiryTimestamp = null)
    {
        /*
         * Piwik does not store the generated password reset token.
         * This avoids a database schema change and SQL queries to store, retrieve, and purge (expired) tokens.
         */
        if (!$expiryTimestamp) {
            $expiryTimestamp = $this->getDefaultExpiryTime();
        }

        $expiry = strftime('%Y%m%d%H', $expiryTimestamp);
        $token = $this->generateSecureHash(
            $expiry . $user['login'] . $user['email'] . $user['ts_password_modified'] . $keySuffix,
            $data
        );
        return $token;
    }

    /**
     * Returns true if a reset token is valid, false if otherwise. A reset token is valid if
     * it exists and has not expired.
     *
     * @param string $token The reset token to check.
     * @param array $user The user information returned by the UsersManager API.
     * @param string $keySuffix The suffix used in generating a token.
     * @return bool true if valid, false otherwise.
     */
    public function isTokenValid($token, array $user, $keySuffix, $data)
    {
        $now = time();

        // token valid for 24 hrs (give or take, due to the coarse granularity in our strftime format string)
        for ($i = 0; $i <= 24; $i++) {
            $generatedToken = $this->generateToken($user, $keySuffix, $data, $now + $i * 60 * 60);
            if ($generatedToken === $token) {
                return true;
            }
        }

        // fails if token is invalid, expired, password already changed, other user information has changed, ...
        return false;
    }

    /**
     * Generates a hash using a hash "identifier" and some data to hash. The hash identifier is
     * a string that differentiates the hash in some way.
     *
     * We can't get the identifier back from a hash but we can tell if a hash is the hash for
     * a specific identifier by computing a hash for the identifier and comparing with the
     * first hash.
     *
     * @param string $hashIdentifier A unique string that identifies the hash in some way, can,
     *                               for example, be user information or can contain an expiration date,
     *                               or whatever.
     * @param string $data Any data that needs to be hashed securely, ie, a password.
     * @return string The hash string.
     */
    protected function generateSecureHash($hashIdentifier, $data)
    {
        // mitigate rainbow table attack
        $halfDataLen = strlen($data) / 2;

        $stringToHash = $hashIdentifier
            . substr($data, 0, $halfDataLen)
            . $this->getSalt()
            . substr($data, $halfDataLen)
        ;

        return $this->hashData($stringToHash);
    }

    /**
     * Returns the string salt to use when generating a secure hash. Defaults to the value of
     * the `[General] salt` INI config option.
     *
     * Derived classes can override this to provide a different salt.
     *
     * @return string
     */
    protected function getSalt()
    {
        return SettingsPiwik::getSalt();
    }

    /**
     * Hashes a string.
     *
     * Derived classes can override this to provide a different hashing implementation.
     *
     * @param string $data The data to hash.
     * @return string
     */
    protected function hashData($data)
    {
        return Common::hash($data);
    }

    /**
     * Returns an expiration time from the current time. By default it will be one day (24 hrs) from
     * now.
     *
     * Derived classes can override this to provide a different default expiration time
     * generation implementation.
     *
     * @return int
     */
    protected function getDefaultExpiryTime()
    {
        return time() + 24 * 60 * 60; /* +24 hrs */
    }
}