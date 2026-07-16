<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Session\SessionNamespace;
use Piwik\Url;

class PasswordVerifier
{
    public const VERIFY_VALID_FOR_MINUTES = 30;
    public const VERIFY_REVALIDATE_X_MINUTES_LEFT = 15;

    /**
     * @var Date|null
     */
    private $now;

    /**
     * @var bool
     */
    private $enableRedirect = true;

    /**
     * @return void
     * @ignore tests only
     */
    public function setDisableRedirect()
    {
        $this->enableRedirect = false;
    }

    private function getLoginSession(): SessionNamespace
    {
        return new SessionNamespace('Login');
    }

    /**
     * @param string $userLogin
     * @param string $password
     * @return bool
     */
    public function isPasswordCorrect(
        $userLogin,
        #[\SensitiveParameter]
        $password
    ) {
        /**
         * @ignore
         * @internal
         */
        Piwik::postEvent('Login.beforeLoginCheckAllowed');

        /** @var \Piwik\Auth $authAdapter */
        $authAdapter = StaticContainer::get('Piwik\Auth');
        $authAdapter->setLogin($userLogin);
        $authAdapter->setPasswordHash(null);// ensure authentication happens on password
        $authAdapter->setPassword($password);
        $authAdapter->setTokenAuth(null);// ensure authentication happens on password
        $authResult = $authAdapter->authenticate();

        if ($authResult->wasAuthenticationSuccessful()) {
            return true;
        }

        /**
         * @ignore
         * @internal
         */
        Piwik::postEvent('Login.recordFailedLoginAttempt');
        return false;
    }

    /**
     * @return bool
     */
    public function hasPasswordVerifyBeenRequested()
    {
        $sessionNamespace = $this->getLoginSession();
        return !empty($sessionNamespace->redirectParams);
    }

    /**
     * @return void
     */
    public function forgetVerifiedPassword()
    {
        // call this method if you want the user to enter the password again after some action was finished which needed
        // the password
        $sessionNamespace = $this->getLoginSession();
        unset($sessionNamespace->lastPasswordAuth);
        unset($sessionNamespace->passwordVerifiedLogin);
        unset($sessionNamespace->redirectParams);
    }

    /**
     * @return void
     * @ignore tests only
     */
    public function setNow(Date $now)
    {
        $this->now = $now;
    }

    private function getNow(): Date
    {
        if ($this->now) {
            return $this->now;
        }
        return Date::now();
    }

    /**
     * @return void
     */
    public function setPasswordVerifiedCorrectly(?string $login = null)
    {
        if ($login === null) {
            $login = Piwik::getCurrentUserLogin();
        }

        $sessionNamespace = $this->getLoginSession();
        $sessionNamespace->lastPasswordAuth = $this->getNow()->getDatetime();
        $sessionNamespace->passwordVerifiedLogin = $login;
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60, 'lastPasswordAuth');
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60, 'passwordVerifiedLogin');
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60, 'redirectParams');

        if ($this->enableRedirect) {
            Url::redirectToUrl('index.php' . Url::getCurrentQueryStringWithParametersModified(
                $sessionNamespace->redirectParams
            ));
        }
    }

    /**
     * @return bool
     */
    public function hasBeenVerified()
    {
        $lastAuthValidTo = $this->getPasswordVerifyValidUpToDateIfVerified();
        $now = $this->getNow();

        if ($lastAuthValidTo && $now->isEarlier($lastAuthValidTo)) {
            return true;
        }
        return false;
    }

    private function getPasswordVerifyValidUpToDateIfVerified(): ?Date
    {
        $sessionNamespace = $this->getLoginSession();
        if (
            !empty($sessionNamespace->lastPasswordAuth)
            && !empty($sessionNamespace->redirectParams)
            // ensure the password was verified for the user currently performing the action.
            && !empty($sessionNamespace->passwordVerifiedLogin)
            && $sessionNamespace->passwordVerifiedLogin === Piwik::getCurrentUserLogin()
        ) {
            return Date::factory($sessionNamespace->lastPasswordAuth)->addPeriod(self::VERIFY_VALID_FOR_MINUTES, 'minute');
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function hasBeenVerifiedAndHalfTimeValid()
    {
        $lastAuthValidTo = $this->getPasswordVerifyValidUpToDateIfVerified();
        $now = $this->getNow()->addPeriod(self::VERIFY_REVALIDATE_X_MINUTES_LEFT, 'minute');

        if ($lastAuthValidTo && $now->isEarlier($lastAuthValidTo)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the user has verified the password within the last 15 minutes. If not, the user will be redirected.
     * The password verify will be valid for at least another 15 minutes giving the user some time to perform an action.
     * See  {@link requirePasswordVerified}
     *
     * @param array $redirectParams
     * @return null|true if password has been verified recently, will redirect if not
     */
    public function requirePasswordVerifiedRecently($redirectParams)
    {
        if ($this->hasBeenVerifiedAndHalfTimeValid()) {
            return true;
        }

        $this->initiatePasswordVerifyRedirect($redirectParams);

        return null;
    }

    /**
     * Checks if the user has verified the password within the last 30 minutes. If not, the user will be redirected.
     * Please note that if the user performs an action afterwards, the password verify could be valid for only few more
     * seconds or minutes and by the time the user confirms a certain action, the password verify may no longer be valid.
     * If you want to ensure the password will be still valid for eg 15 minutes before the user performs some action,
     * consider using {@link requirePasswordVerifiedRecently}.
     *
     * @param array $redirectParams
     * @return null|true if password has been verified, will redirect if not
     */
    public function requirePasswordVerified($redirectParams)
    {
        if ($this->hasBeenVerified()) {
            return true;
        }

        $this->initiatePasswordVerifyRedirect($redirectParams);

        return null;
    }

    /**
     * @param array $redirectParams
     */
    private function initiatePasswordVerifyRedirect($redirectParams): void
    {
        $sessionNamespace = $this->getLoginSession();
        $sessionNamespace->redirectParams = $redirectParams;
        $sessionNamespace->setExpirationSeconds(self::VERIFY_VALID_FOR_MINUTES * 60 * 5, 'redirectParams');

        if ($this->enableRedirect) {
            Piwik::redirectToModule(Piwik::getLoginPluginName(), 'confirmPassword');
        }
    }
}
