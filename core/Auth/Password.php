<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Auth;

use Exception;
use Piwik\Config;

/**
 * Main class to handle actions related to password hashing and verification.
 *
 * @api
 */
class Password
{
    /**
     * Choose the used algorithm for password_hash depending on the config option
     *
     * @return string|int depending on PHP version
     * @throws Exception
     */
    private function preferredAlgorithm()
    {
        $passwordHashAlgorithm = Config::getInstance()->General['password_hash_algorithm'];
        switch ($passwordHashAlgorithm) {
            case "default":
                return PASSWORD_DEFAULT;
            case "bcrypt":
                return PASSWORD_BCRYPT;
            case "argon2i":
                return PASSWORD_ARGON2I;
            case "argon2id":
                if (version_compare(PHP_VERSION, '7.3.0', '<')) {
                    throw new Exception("argon2id needs at leat PHP 7.3.0");
                }
                return PASSWORD_ARGON2ID;
            default:
                throw new Exception("invalid password_hash_algorithm");
        }
    }

    /**
     * Fetches argon2 options from config.ini.php
     *
     * @return array
     */
    private function algorithmOptions()
    {
        $options = [];
        $generalConfig = Config::getInstance()->General;
        if ($generalConfig["password_hash_argon2_threads"] != "default") {
            $options["threads"] = max($generalConfig["password_hash_argon2_threads"], 1);
        }
        if ($generalConfig["password_hash_argon2_memory_cost"] != "default") {
            $options["memory_cost"] = max($generalConfig["password_hash_argon2_memory_cost"], 8 * $options["threads"]);
        }
        if ($generalConfig["password_hash_argon2_time_cost"] != "default") {
            $options["time_cost"] = max($generalConfig["password_hash_argon2_time_cost"], 1);
        }
        return $options;
    }

    /**
     * Hashes a password with the configured algorithm.
     *
     * @param string $password
     * @return string
     */
    public function hash($password)
    {
        return password_hash($password, $this->preferredAlgorithm(), $this->algorithmOptions());
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
        return password_needs_rehash($hash, $this->preferredAlgorithm(), $this->algorithmOptions());
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
