<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Emails;

use Piwik\Config;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\TokenNotifications\AuthTokenExpirationWarningEmailNotification;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

class AuthTokenExpirationWarningNotificationEmail extends Mail
{
    /**
     * @var AuthTokenExpirationWarningEmailNotification
     */
    private $notification;

    /** @var string */
    private $recipient;

    /** @var array */
    private $emailData;

    public function __construct(
        AuthTokenExpirationWarningEmailNotification $notification,
        string $recipient,
        array $emailData
    ) {
        parent::__construct();

        $this->notification = $notification;
        $this->recipient = $recipient;
        $this->emailData = $emailData;

        $this->setUpEmail();
    }

    private function setUpEmail(): void
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->recipient);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setBodyText($this->getDefaultBodyText());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getExpirationWarningPeriodPretty(): string
    {
        $expirationPeriodDays = Config::getInstance()->General['auth_token_expiration_notification_days'];
        return $expirationPeriodDays . ' ' . Piwik::translate('Intl_PeriodDay' . ($expirationPeriodDays === 1 ? '' : 's'));
    }

    protected function getInstanceUrl(): string
    {
        return SettingsPiwik::getPiwikUrl();
    }

    protected function getManageAuthTokensLink(): string
    {
        return $this->getInstanceUrl()
            . 'index.php?'
            . Url::getQueryStringFromParameters(['module' => 'UsersManager', 'action' => 'userSecurity'])
            . '#authtokens';
    }
    protected function getDefaultSubject(): string
    {
        return Piwik::translate(
            'UsersManager_AuthTokenExpirationWarningEmailSubjectAll',
            [
                $this->getInstanceUrl(),
                $this->getExpirationWarningPeriodPretty(),
            ]
        );
    }

    protected function getDefaultBodyText(): string
    {
        $view = new View('@UsersManager/_authTokenExpirationWarningNotificationTextEmail.twig');
        $view->setContentType('text/plain');

        $this->assignCommonParameters($view);

        return $view->render();
    }

    protected function getDefaultBodyView(): View
    {
        $view = new View('@UsersManager/_authTokenExpirationWarningNotificationHtmlEmail.twig');

        $this->assignCommonParameters($view);

        return $view;
    }

    protected function assignCommonParameters(View $view): void
    {
        $view->tokens = $this->notification->getTokens();
        $view->expirationWarningPeriod = $this->getExpirationWarningPeriodPretty();
        $view->manageAuthTokensLink = $this->getManageAuthTokensLink();
        $view->instanceUrl = $this->getInstanceUrl();

        foreach ($this->emailData as $item => $value) {
            $view->assign($item, $value);
        }
    }
}
