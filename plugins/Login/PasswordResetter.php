<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Login;

use Exception;
use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Mail;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\SettingsPiwik;
use Piwik\Url;

/**
 * Contains the logic for different parts of the password reset process.
 *
 * The process to reset a password is as follows:
 *
 * 1. The user chooses to reset a password. He/she enters a new password
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
     * Defaults to the `[General] login_password_recovery_email_name` INI config option.
     *
     * @var string
     */
    private $emailFromName;

    /**
     * The from email to use in the confirm password reset email.
     *
     * Deafults to the `[General] login_password_recovery_email_address` INI config option.
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
     */
    public function __construct($usersManagerApi = null, $confirmPasswordModule = null, $confirmPasswordAction = null,
                                $emailFromName = null, $emailFromAddress = null)
    {
        if (empty($usersManagerApi)) {
            $usersManagerApi = UsersManagerAPI::getInstance();
        }
        $this->usersManagerApi = $usersManagerApi;

        if (!empty($confirmPasswordModule)) {
            $this->confirmPasswordModule = $confirmPasswordModule;
        }

        if (!empty($confirmPasswordAction)) {
            $this->confirmPasswordAction = $confirmPasswordAction;
        }

        if (empty($emailFromName)) {
            $emailFromName = Config::getInstance()->General['login_password_recovery_email_name'];
        }
        $this->emailFromName = $emailFromName;

        if (empty($emailFromAddress)) {
            $emailFromAddress = Config::getInstance()->General['login_password_recovery_email_address'];
        }
        $this->emailFromAddress = $emailFromAddress;
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

        $this->savePasswordResetInfo($login, $newPassword);

        // ... send email with confirmation link
        try {
            $this->sendEmailConfirmationLink($user);
        } catch (Exception $ex) {
            // remove password reset info
            $this->removePasswordResetInfo($login);

            throw new Exception($ex->getMessage() . Piwik::translate('Login_ContactAdmin'));
        }
    }

    /**
     * Confirms a password reset. This should be called after {@link initiatePasswordResetProcess()}
     * is called.
     *
     * This method will get the new password associated with a reset token and set it
     * as the specified user's password.
     *
     * @param string $login The login of the user whose password is being reset.
     * @param string $resetToken The generated string token contained in the reset password
     *                           email.
     * @throws Exception If there is no user with login '$login', if $resetToken is not a
     *                   valid token or if the token has expired.
     */
    public function confirmNewPassword($login, $resetToken)
    {
        // get password reset info & user info
        $user = self::getUserInformation($login);
        if ($user === null) {
            throw new Exception(Piwik::translate('Login_InvalidUsernameEmail'));
        }

        // check that the reset token is valid
        $resetPassword = $this->getPasswordToResetTo($login);
        if ($resetPassword === false
            || !$this->isTokenValid($resetToken, $user)
        ) {
            throw new Exception(Piwik::translate('Login_InvalidOrExpiredToken'));
        }

        // check that the stored password hash is valid (sanity check)
        $this->checkPasswordHash($resetPassword);

        // reset password of user
        $usersManager = $this->usersManagerApi;
        Access::doAsSuperUser(function () use ($usersManager, $user, $resetPassword) {
            $usersManager->updateUser(
                $user['login'], $resetPassword, $email = false, $alias = false, $isPasswordHashed = true);
        });
    }

    /**
     * Returns true if a reset token is valid, false if otherwise. A reset token is valid if
     * it exists and has not expired.
     *
     * @param string $token The reset token to check.
     * @param array $user The user information returned by the UsersManager API.
     * @return bool true if valid, false otherwise.
     */
    public function isTokenValid($token, $user)
    {
        $now = time();

        // token valid for 24 hrs (give or take, due to the coarse granularity in our strftime format string)
        for ($i = 0; $i <= 24; $i++) {
            $generatedToken = $this->generatePasswordResetToken($user, $now + $i * 60 * 60);
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
     * @param int|null $expiryTimestamp The expiration timestamp to use or null to generate one from
     *                                  the current timestamp.
     * @return string The generated token.
     */
    public function generatePasswordResetToken($user, $expiryTimestamp = null)
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
            $expiry . $user['login'] . $user['email'],
            $user['password']
        );
        return $token;
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
     * Derived classes can override this method to provide custom user querying logic.
     *
     * @param string $loginMail user login or email address
     * @return array `array("login" => '...', "email" => '...', "password" => '...')` or null, if user not found.
     */
    protected function getUserInformation($loginOrMail)
    {
        $usersManager = $this->usersManagerApi;
        return Access::doAsSuperUser(function () use ($loginOrMail, $usersManager) {
            $user = null;
            if ($usersManager->userExists($loginOrMail)) {
                $user = $usersManager->getUser($loginOrMail);
            } else if ($usersManager->userEmailExists($loginOrMail)) {
                $user = $usersManager->getUserByEmail($loginOrMail);
            }
            return $user;
        });
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
        UsersManager::checkPasswordHash($passwordHash, Piwik::translate('Login_ExceptionPasswordMD5HashExpected'));
    }

    /**
     * Sends email confirmation link for a password reset request.
     *
     * @param array $user User info for the requested password reset.
     */
    private function sendEmailConfirmationLink($user)
    {
        $login = $user['login'];
        $email = $user['email'];

        // construct a password reset token from user information
        $resetToken = $this->generatePasswordResetToken($user);

        $confirmPasswordModule = $this->confirmPasswordModule;
        $confirmPasswordAction = $this->confirmPasswordAction;

        $ip = IP::getIpFromHeader();
        $url = Url::getCurrentUrlWithoutQueryString()
            . "?module=$confirmPasswordModule&action=$confirmPasswordAction&login=" . urlencode($login)
            . "&resetToken=" . urlencode($resetToken);

        // send email with new password
        $mail = new Mail();
        $mail->addTo($email, $login);
        $mail->setSubject(Piwik::translate('Login_MailTopicPasswordChange'));
        $bodyText = str_replace(
                '\n',
                "\n",
                sprintf(Piwik::translate('Login_MailPasswordChangeBody'), $login, $ip, $url)
            ) . "\n";
        $mail->setBodyText($bodyText);

        $mail->setFrom($this->emailFromAddress, $this->emailFromName);

        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];
        $mail->setReplyTo($replytoEmailAddress, $replytoEmailName);

        @$mail->send();
    }

    /**
     * Stores password reset info for a specific login.
     *
     * @param string $login The user login for whom a password change was requested.
     * @param string $newPassword The new password to set.
     */
    private function savePasswordResetInfo($login, $newPassword)
    {
        $optionName = $this->getPasswordResetInfoOptionName($login);
        $optionData = UsersManager::getPasswordHash($newPassword);

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
        return Option::get($optionName);
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
