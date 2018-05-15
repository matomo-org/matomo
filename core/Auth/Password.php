<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Auth;

/**
 * Main class to handle actions related to password hashing and verification.
 *
 * @api
 */
class Password
{
    /**
     * Hashes a password with the configured algorithm.
     *
     * @param string $password
     * @return string
     */
    public function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Returns information about a hashed password (algo, options, ...).
     *
     * Can be used to verify whether a string is compatible with password_hash().
     *
     * @param string
     * @return array
     */
    public function info($hash)
    {
        return password_get_info($hash);
    }

    /**
     * Rehashes a user's password if necessary.
     *
     * This method expects the password to be pre-hashed by
     * \Piwik\Plugins\UsersManager\UsersManager::getPasswordHash().
     *
     * @param string $hash
     * @return boolean
     */
    public function needsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT);
    }

    /**
     * Verifies a user's password against the provided hash.
     *
     * This method expects the password to be pre-hashed by
     * \Piwik\Plugins\UsersManager\UsersManager::getPasswordHash().
     *
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
