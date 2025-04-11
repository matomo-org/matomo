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
use Piwik\Plugins\UsersManager\TokenNotifications\TokenNotification;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

class AuthTokenNotificationEmail extends Mail
{
    /**
     * @var TokenNotification
     */
    private $notification;

    /** @var string */
    private $recipient;

    /** @var array */
    private $emailData;

    public function __construct(TokenNotification $notification, string $recipient, array $emailData)
    {
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

    private function getRotationPeriodPretty(): string
    {
        $rotationPeriodDays = Config::getInstance()->General['auth_token_rotation_notification_days'];

        return Piwik::translate('Intl_PeriodDay' . ($rotationPeriodDays === 1 ? '' : 's'));
    }

    protected function getManageAuthTokensLink(): string
    {
        return SettingsPiwik::getPiwikUrl()
            . 'index.php?'
            . Url::getQueryStringFromParameters(['module' => 'UsersManager', 'action' => 'userSecurity'])
            . '#authtokens';
    }
    protected function getDefaultSubject(): string
    {
        return Piwik::translate('UsersManager_AuthTokenNotificationEmailSubject');
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
        $view->tokenName = $this->notification->getTokenName();
        $view->tokenCreationDate = $this->notification->getTokenCreationDate();

        $view->rotationPeriod = $this->getRotationPeriodPretty();
        $view->manageAuthTokensLink = $this->getManageAuthTokensLink();

        foreach ($this->emailData as $item => $value) {
            $view->assign($item, $value);
        }
    }
}
