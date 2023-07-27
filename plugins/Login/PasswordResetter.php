<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Login;

use Exception;
use Piwik\Access;
use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\IP;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Login\Emails\PasswordResetEmail;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\SettingsPiwik;
use Piwik\Url;

/**
 * Contains the logic for different parts of the password reset process.
 *
 * The process to reset a password is as follows:
 *
 * 1. The user chooses to reset a password. They enter a new password
 *    and submits it to Piwik.
 * 2. PasswordResetter will store the hash of the password in the Option table.
 *    This is done by {@link initiatePasswordResetProcess()}.
 * 3. PasswordResetter will generate a reset token and email the user a link
 *    to confirm that they requested a password reset. (This way an attacker
 *    cannot reset a user's password if they do not have control of the user's
 *    email address.)
 * 4. The user opens the email and clicks on the link. The link leads to
 *    a controller action that finishes the password reset process.
 * 5. When the link is clicked, PasswordResetter will update the user's password
 *    and remove the Option stored earlier. This is accomplished by
 *    {@link confirmNewPassword()}.
 *
 * Note: this class does not contain any controller logic so it won't directly
 * handle certain requests. Controllers should call the appropriate methods.
 *
 * ## Reset Tokens
 *
 * Reset tokens are hashes that are unique for each user and are associated with
 * an expiry timestamp in the future. see the {@link generatePasswordResetToken()}
 * and {@link isTokenValid()} methods for more info.
 *
 * By default, reset tokens will expire after 24 hours.
 *
 * ## Overriding
 *
 * Plugins that want to tweak the password reset process can derive from this
 * class. They can override certain methods (read documentation for individual
 * methods to see why and how you might want to), but for the overriding to
 * have effect, it must be used by the Login controller.
 */
class PasswordResetter
{
    /**
     * @var Password
     */
    protected $passwordHelper;

    /**
     * @var UsersManagerAPI
     */
    protected $usersManagerApi;

    /**
     * The module to link to in the confirm password reset email.
     *
     * @var string
     */
    private $confirmPasswordModule = "Login";

    /**
     * The action to link to in the confirm password reset email.
     *
     * @var string
     */
    private $confirmPasswordAction = "confirmResetPassword";

    /**
     * The name to use in the From: part of the confirm password reset email.
     *
     * Defaults to the `[General] noreply_email_name` INI config option.
     *
     * @var string
     */
    private $emailFromName;

    /**
     * The from email to use in the confirm password reset email.
     *
     * Defaults to the `[General] noreply_email_address` INI config option.
     *
     * @var
     */
    private $emailFromAddress;

    /**
     * Constructor.
     *
     * @param UsersManagerAPI|null $usersManagerApi
     * @param string|null $confirmPasswordModule
     * @param string|null $confirmPasswordAction
     * @param string|null $emailFromName
     * @param string|null $emailFromAddress
     * @param Password $passwordHelper
     */
    public function __construct($usersManagerApi = null, $confirmPasswordModule = null, $confirmPasswordAction = null,
                                $emailFromName = null, $emailFromAddress = null, $passwordHelper = null)
    {
        if (empty($usersManagerApi)) {
            $usersManagerApi = UsersManagerAPI::getInstance();
        }
        $this->usersManagerApi = $usersManagerApi;

        $this->confirmPasswordModule = Piwik::getLoginPluginName();
        if (!empty($confirmPasswordModule)) {
            $this->confirmPasswordModule = $confirmPasswordModule;
        }

        if (!empty($confirmPasswordAction)) {
            $this->confirmPasswordAction = $confirmPasswordAction;
        }

        $this->emailFromName = $emailFromName;
        $this->emailFromAddress = $emailFromAddress;

        if (empty($passwordHelper)) {
            $passwordHelper = new Password();
        }
        $this->passwordHelper = $passwordHelper;
    }

    /**
     * Initiates the password reset process. This method will save the password reset
     * information as an {@link Option} and send an email with the reset confirmation
     * link to the user whose password is being reset.
     *
     * The email confirmation link will contain the generated reset token.
     *
     * @param string $loginOrEmail The user's login or email address.
     * @param string $newPassword The un-hashed/unencrypted password.
     * @throws Exception if $loginOrEmail does not correspond with a non-anonymous user,
     *                   if the new password does not pass UserManager's password
     *                   complexity requirements
     *                   or if sending an email fails in some way
     */
    public function initiatePasswordResetProcess($loginOrEmail, $newPassword)
    {
        $this->checkNewPassword($newPassword);

        // 'anonymous' has no password and cannot be reset
        if ($loginOrEmail === 'anonymous') {
            throw new Exception(Piwik::translate('Login_InvalidUsernameEmail'));
        }

        // get the user's login
        $user = $this->getUserInformation($loginOrEmail);
        if ($user === null) {
            throw new Exception(Piwik::translate('Login_InvalidUsernameEmail'));
        }

        $login = $user['login'];

        $keySuffix = time() . Common::getRandomString($length = 32);
        $this->savePasswordResetInfo($login, $newPassword, $keySuffix);

        // ... send email with confirmation link
        try {
            $this->sendEmailConfirmationLink($user, $keySuffix);
        } catch (Exception $ex) {
            // remove password reset info
            $this->removePasswordResetInfo($login);

            throw new Exception($ex->getMessage() . Piwik::translate('Login_ContactAdmin'));
        }
    }

    public function checkValidConfirmPasswordToken($login, $resetToken)
    {
        // get password reset info & user info
        $user = self::getUserInformation($login);
        if ($user === null) {
            throw new Exception(Piwik::translate('Login_InvalidUsernameEmail'));
        }

        // check that the reset token is valid
        $resetInfo = $this->getPasswordToResetTo($login);
        if ($resetInfo === false
            || empty($resetInfo['hash'])
            || empty($resetInfo['keySuffix'])
            || !$this->isTokenValid($resetToken, $user, $resetInfo['keySuffix'])
        ) {
            throw new Exception(Piwik::translate('Login_InvalidOrExpiredToken'));
        }

        // check that the stored password hash is valid (sanity check)
        $resetPassword = $resetInfo['hash'];

        $this->checkPasswordHash($resetPassword);

        return $resetPassword;
    }

    /**
     * Confirms a password reset. This should be called after {@link initiatePasswordResetProcess()}
     * is called.
     *
     * This method will get the new password associated with a reset token and set it
     * as the specified user's password.
     *
     * @param string $login The login of the user whose password is being reset.
     * @param string $passwordHash The generated string token contained in the reset password
     *                           email.
     * @throws Exception If there is no user with login '$login', if $resetToken is not a
     *                   valid token or if the token has expired.
     */
    public function setHashedPasswordForLogin($login, $passwordHash)
    {
        Access::doAsSuperUser(function () use ($login, $passwordHash) {
            $userUpdater = new UserUpdater();
            $userUpdater->updateUserWithoutCurrentPassword(
                $login,
                $passwordHash,
                $email = false,
                $isPasswordHashed = true
            );
        });
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
    public function isTokenValid($token, $user, $keySuffix)
    {
        $now = time();

        // token valid for 24 hrs (give or take, due to the coarse granularity in our strftime format string)
        for ($i = 0; $i <= 24; $i++) {
            $generatedToken = $this->generatePasswordResetToken($user, $keySuffix, $now + $i * 60 * 60);
            if ($generatedToken === $token) {
                return true;
            }
        }

        // fails if token is invalid, expired, password already changed, other user information has changed, ...
        return false;
    }

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
    public function generatePasswordResetToken($user, $keySuffix, $expiryTimestamp = null)
    {
        /*
         * Piwik does not store the generated password reset token.
         * This avoids a database schema change and SQL queries to store, retrieve, and purge (expired) tokens.
         */
        if (!$expiryTimestamp) {
            $expiryTimestamp = $this->getDefaultExpiryTime();
        }

        $expiry = date('YmdH', $expiryTimestamp);
        $token = $this->generateSecureHash(
            $expiry . $user['login'] . $user['email'] . $user['ts_password_modified'] . $keySuffix,
            $user['password']
        );
        return $token;
    }

    public function doesResetPasswordHashMatchesPassword($passwordPlain, $passwordHash)
    {
        $passwordPlain = UsersManager::getPasswordHash($passwordPlain);
        return $this->passwordHelper->verify($passwordPlain, $passwordHash);
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

    /**
     * Checks the reset password's complexity. Will use UsersManager's requirements for user passwords.
     *
     * Derived classes can override this method to provide fewer or additional checks.
     *
     * @param string $newPassword The password to check.
     * @throws Exception if $newPassword is inferior in some way.
     */
    protected function checkNewPassword($newPassword)
    {
        UsersManager::checkPassword($newPassword);
    }

    /**
     * Returns user information based on a login or email.
     *
     * If user is pending, return null
     *
     * Derived classes can override this method to provide custom user querying logic.
     *
     * @param string $loginMail user login or email address
     * @return array `array("login" => '...', "email" => '...', "password" => '...')` or null, if user not found.
     */
    protected function getUserInformation($loginOrMail)
    {
        $userModel = new Model();

        if ($userModel->isPendingUser($loginOrMail)) {
            return null;
        }

        $user = null;

        if ($userModel->userExists($loginOrMail)) {
            $user = $userModel->getUser($loginOrMail);
        } else if ($userModel->userEmailExists($loginOrMail)) {
            $user = $userModel->getUserByEmail($loginOrMail);
        }
        return $user;
    }

    /**
     * Checks the password hash that was retrieved from the Option table. Used as a sanity check
     * when finishing the reset password process. If a password is obviously malformed, changing
     * a user's password to it will keep the user from being able to login again.
     *
     * Derived classes can override this method to provide fewer or more checks.
     *
     * @param string $passwordHash The password hash to check.
     * @throws Exception if the password hash length is incorrect.
     */
    protected function checkPasswordHash($passwordHash)
    {
        $hashInfo = $this->passwordHelper->info($passwordHash);

        if (!isset($hashInfo['algo']) || 0 >= $hashInfo['algo']) {
            throw new Exception(Piwik::translate('Login_ExceptionPasswordMD5HashExpected'));
        }
    }

    /**
     * Sends email confirmation link for a password reset request.
     *
     * @param array $user User info for the requested password reset.
     * @param string $keySuffix The suffix used in generating a token.
     */
    private function sendEmailConfirmationLink($user, $keySuffix)
    {
        $login = $user['login'];
        $email = $user['email'];

        // construct a password reset token from user information
        $resetToken = $this->generatePasswordResetToken($user, $keySuffix);

        $confirmPasswordModule = $this->confirmPasswordModule;
        $confirmPasswordAction = $this->confirmPasswordAction;

        $ip = IP::getIpFromHeader();
        $url = Url::getCurrentUrlWithoutQueryString()
            . "?module=$confirmPasswordModule&action=$confirmPasswordAction&login=" . urlencode($login)
            . "&resetToken=" . urlencode($resetToken);

        // send email with new password
        $mail = new PasswordResetEmail($login, $ip, $url);
        $mail->addTo($email, $login);

        if ($this->emailFromAddress || $this->emailFromName) {
            $mail->setFrom($this->emailFromAddress, $this->emailFromName);
        } else {
            $mail->setDefaultFromPiwik();
        }

        @$mail->send();
    }

    /**
     * Stores password reset info for a specific login.
     *
     * @param string $login The user login for whom a password change was requested.
     * @param string $newPassword The new password to set.
     * @param string $keySuffix The suffix used in generating a token.
     *
     * @throws Exception if a password reset was already requested within one hour
     */
    private function savePasswordResetInfo($login, $newPassword, $keySuffix)
    {
        $optionName = self::getPasswordResetInfoOptionName($login);

        $existingResetInfo = Option::get($optionName);

        $time = time();
        $count = 0;

        if ($existingResetInfo) {
            $existingResetInfo = json_decode($existingResetInfo, true);

            if (isset($existingResetInfo['timestamp']) && $existingResetInfo['timestamp'] > time()-3600) {
                $time = $existingResetInfo['timestamp'];
                $count = !empty($existingResetInfo['requests']) ? $existingResetInfo['requests'] : $count;

                if(isset($existingResetInfo['requests']) && $existingResetInfo['requests'] > 2) {
                    throw new Exception(Piwik::translate('Login_PasswordResetAlreadySent'));
                }
            }
        }


        $optionData = [
            'hash' => $this->passwordHelper->hash(UsersManager::getPasswordHash($newPassword)),
            'keySuffix' => $keySuffix,
            'timestamp' => $time,
            'requests' => $count+1
        ];
        $optionData = json_encode($optionData);

        Option::set($optionName, $optionData);
    }

    /**
     * Gets password hash stored in password reset info.
     *
     * @param string $login The user login to check for.
     * @return string|false The hashed password or false if no reset info exists.
     */
    private function getPasswordToResetTo($login)
    {
        $optionName = self::getPasswordResetInfoOptionName($login);
        $optionValue = Option::get($optionName);
        $optionValue = json_decode($optionValue, $isAssoc = true);
        return $optionValue;
    }

    /**
     * Removes stored password reset info if it exists.
     *
     * @param string $login The user login to check for.
     */
    public function removePasswordResetInfo($login)
    {
        $optionName = self::getPasswordResetInfoOptionName($login);
        Option::delete($optionName);
    }

    /**
     * Gets the option name for the option that will store a user's password change
     * request.
     *
     * @param string $login The user login for whom a password change was requested.
     * @return string
     */
    public static function getPasswordResetInfoOptionName($login)
    {
        return $login . '_reset_password_info';
    }
}
