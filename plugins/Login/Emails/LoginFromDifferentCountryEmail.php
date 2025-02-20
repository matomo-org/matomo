<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Emails;

use Piwik\Date;
use Piwik\Intl\Data\Provider\DateTimeFormatProvider;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Url;
use Piwik\View;

class LoginFromDifferentCountryEmail extends Mail
{
    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $ip;

    public function __construct(string $login, string $country, string $ip)
    {
        parent::__construct();

        $this->login = $login;
        $this->country = $country;
        $this->ip = $ip;

        $this->setUpEmail();
    }

    private function setUpEmail(): void
    {
        $this->setDefaultFromPiwik();
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setBodyText($this->getDefaultBodyText());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate('Login_LoginFromDifferentCountryEmailSubject');
    }

    protected function getDateAndTimeFormatted(): string
    {
        return Date::now()->getLocalized(DateTimeFormatProvider::DATETIME_FORMAT_LONG);
    }

    protected function getPasswordResetLink(): string
    {
        return Url::getCurrentUrlWithoutQueryString()
            . '?module=' . Piwik::getLoginPluginName()
            . '&showResetForm=1';
    }

    protected function getEnable2FALink(): string
    {
        if (PluginManager::getInstance()->isPluginActivated('TwoFactorAuth')) {
            return Url::getCurrentUrlWithoutQueryString() . '?module=UsersManager&action=userSecurity';
        } else {
            return '';
        }
    }

    protected function getDefaultBodyText(): string
    {
        $view = new View('@Login/_loginFromDifferentCountryTextEmail.twig');
        $view->setContentType('text/plain');

        $this->assignCommonParameters($view);

        return $view->render();
    }

    protected function getDefaultBodyView(): View
    {
        $view = new View('@Login/_loginFromDifferentCountryHtmlEmail.twig');

        $this->assignCommonParameters($view);

        return $view;
    }

    protected function assignCommonParameters(View $view): void
    {
        $view->login = $this->login;
        $view->country = $this->country;
        $view->ip = $this->ip;
        $view->dateTime = $this->getDateAndTimeFormatted();
        $view->resetPasswordLink = $this->getPasswordResetLink();
        $view->enable2FALink = $this->getEnable2FALink();
    }
}
