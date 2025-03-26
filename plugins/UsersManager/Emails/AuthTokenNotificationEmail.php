<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Emails;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\AuthTokenNotifications\AuthTokenNotification;
use Piwik\Url;
use Piwik\View;

class AuthTokenNotificationEmail extends Mail
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var AuthTokenNotification
     */
    private $notification;

    /** @var int */
    private $rotationPeriodDays;

    public function __construct(string $login, string $emailAddress, AuthTokenNotification $notification, int $rotationPeriodDays)
    {
        parent::__construct();

        $this->login = $login;
        $this->emailAddress = $emailAddress;
        $this->notification = $notification;
        $this->rotationPeriodDays = $rotationPeriodDays;

        $this->setUpEmail();
    }

    private function setUpEmail(): void
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setBodyText($this->getDefaultBodyText());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getRotationPeriodPretty(): string
    {
        $startDate = new \DateTime();
        $endDate = (clone $startDate)->add(new \DateInterval("P{$this->rotationPeriodDays}D"));
        $diff = $startDate->diff($endDate);

        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y .
                ($diff->y === 1
                    ? Piwik::translate('Intl_PeriodYear')
                    : Piwik::translate('Intl_PeriodYears')
                );
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m .
                ($diff->m === 1
                    ? Piwik::translate('Intl_PeriodMonth')
                    : Piwik::translate('Intl_PeriodMonths')
                );
        }
        // Only include days if they're not zero OR if there are no years/months
        if ($diff->d > 0 || empty($parts)) {
            $parts[] = $diff->d .
                ($diff->d === 1
                    ? Piwik::translate('Intl_PeriodDay')
                    : Piwik::translate('Intl_PeriodDays')
                );
        }

        return implode(', ', $parts);
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate('UsersManager_AuthTokenNotificationEmailSubject');
    }

    protected function getManageAuthTokensLink(): string
    {
        return Url::getCurrentUrlWithoutQueryString()
            . '?module=UsersManager'
            . '&action=userSecurity'
            . '#authtokens';
    }

    protected function getDefaultBodyText(): string
    {
        $view = new View('@UsersManager/_authTokenNotificationTextEmail.twig');
        $view->setContentType('text/plain');

        $this->assignCommonParameters($view);

        return $view->render();
    }

    protected function getDefaultBodyView(): View
    {
        $view = new View('@UsersManager/_authTokenNotificationHtmlEmail.twig');

        $this->assignCommonParameters($view);

        return $view;
    }

    protected function assignCommonParameters(View $view): void
    {
        $view->login = $this->login;
        $view->tokenName = $this->notification->getTokenName();
        $view->tokenCreationDate = $this->notification->getTokenCreationDate();
        $view->rotationPeriod = $this->getRotationPeriodPretty();
        $view->manageAuthTokensLink = $this->getManageAuthTokensLink();
    }
}
