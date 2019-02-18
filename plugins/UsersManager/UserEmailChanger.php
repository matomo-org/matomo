<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Mail;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Tracker\Cache;
use Piwik\Url;

class UserEmailChanger
{
    const OPTION_NAME_PREFIX = 'UsersManager.userEmailChange.';

    public function startEmailChange($user, $newEmail)
    {
        $keySuffix = time() . Common::getRandomString($length = 32);
        $optionData = json_encode([ 'suffix' => $keySuffix, 'email' => $newEmail ]);

        $optionName = $this->getOptionName($user['login']);
        Option::set($optionName, $optionData);

        $userChangeToken = new UserChangeToken();
        $token = $userChangeToken->generateToken($user, $keySuffix, $newEmail);

        $this->sendEmail($user, $newEmail, $token);
    }

    public function confirmEmailChange($user, $token)
    {
        $optionName = $this->getOptionName($user['login']);
        $optionData = Option::get($optionName);
        if (empty($optionData)) {
            throw new Exception(Piwik::translate('Login_InvalidOrExpiredToken'));
        }

        $optionData = json_decode($optionData, $isAssoc = true);
        if (empty($optionData)
            || empty($optionData['suffix'])
            || empty($optionData['email'])
        ) {
            throw new Exception(Piwik::translate('Login_InvalidOrExpiredToken'));
        }

        $userChangeToken = new UserChangeToken();
        if (!$userChangeToken->isTokenValid($token, $user, $optionData['suffix'], $optionData['email'])) {
            throw new Exception(Piwik::translate('Login_InvalidOrExpiredToken'));
        }

        $model = new Model();
        $model->updateUser($user['login'], $user['password'], $optionData['email'], $user['alias'], $user['token_auth']);

        Cache::deleteTrackerCache();

        Piwik::postEvent('UsersManager.updateUser.end', array($user['login'], false, $optionData['email'], null, $user['alias']));
    }

    private function sendEmail($user, $newEmail, $token)
    {
        $url = Url::getCurrentUrlWithoutQueryString()
            . "?module=UsersManager&action=confirmEmailChange&login=" . urlencode($user['login'])
            . "&token=" . urlencode($token);

        // send email with new password
        $mail = new Mail();
        $mail->addTo($newEmail, $user['login']);
        $mail->setSubject(Piwik::translate('UsersManager_VerifyEmailChangeEmailSubject'));
        $mail->setDefaultFromPiwik();
        $bodyText = '<p>' . str_replace(
                "\n\n",
                "</p><p>",
                Piwik::translate('UsersManager_VerifyEmailChangeEmailBody', [
                    Common::sanitizeInputValue($newEmail),
                    Common::sanitizeInputValue($user['login']),
                ]) . "\n\n" . $url
            ) . "</p>";
        $mail->setWrappedHtmlBody($bodyText);

        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];
        $mail->setReplyTo($replytoEmailAddress, $replytoEmailName);

        $mail->send();
    }

    private function getOptionName($login)
    {
        return self::OPTION_NAME_PREFIX . $login;
    }
}