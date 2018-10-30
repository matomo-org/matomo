<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Login;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Session\SessionNamespace;
use Piwik\Url;

class PasswordVerify
{
    const VERIFY_VALID_FOR_MINUTES = 30;
    const VERIFY_REVALIDATE_X_MINUTES_LEFT = 15;

    public function getLoginSession()
    {
        return new SessionNamespace('Login');
    }

    public function hasPasswordVerifyBeenRequested()
    {
        $sessionNamespace = $this->getLoginSession();
        return !empty($sessionNamespace->redirectParams);
    }

    public function setPasswordVerifiedCorrectly()
    {
        $sessionNamespace = $this->getLoginSession();
        $sessionNamespace->lastPasswordAuth = Date::now()->getDatetime();
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60, 'lastPasswordAuth');
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60, 'redirectParams');

        Url::redirectToUrl('index.php' . Url::getCurrentQueryStringWithParametersModified(
            $sessionNamespace->redirectParams
        ));
    }

    public function hasBeenVerified()
    {
        $sessionNamespace = $this->getLoginSession();
        if (!empty($sessionNamespace->lastPasswordAuth) && !empty($sessionNamespace->redirectParams)) {
            // we require at least X minutes left so user has enough time to perform certain action
            $lastAuthValidTo = Date::factory($sessionNamespace->lastPasswordAuth)->addPeriod(self::VERIFY_VALID_FOR_MINUTES, 'minute');
            $now = Date::now()->addPeriod(self::VERIFY_REVALIDATE_X_MINUTES_LEFT, 'minute');
            if ($now->isEarlier($lastAuthValidTo)) {
                return true;
            }
        }
        return false;
    }

    public function requirePasswordVerifiedRecently($redirectParams)
    {
        if ($this->hasBeenVerified()) {
            return true;
        }

        $sessionNamespace = $this->getLoginSession();
        $sessionNamespace->redirectParams = $redirectParams;
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60 * 5, 'redirectParams');
        Piwik::redirectToModule('Login', 'confirmPassword');
    }

}
