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
use Piwik\Plugins\LanguagesManager\LanguagesHelper;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserNotifications\UserNotification;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

class InactiveUsersNotificationEmail extends Mail
{
    /**
     * @var UserNotification
     */
    private $notification;

    /** @var string */
    private $recipient;

    /** @var array */
    private $emailData;

    /** @var Model */
    private $userModel;

    public function __construct(UserNotification $notification, string $recipient, array $emailData)
    {
        parent::__construct();

        $this->notification = $notification;
        $this->recipient = $recipient;
        $this->emailData = $emailData;
        $this->userModel = new Model();

        $this->setUpEmail();
    }

    private function setUpEmail(): void
    {
        LanguagesHelper::doWithUserLanguage($this->recipient, function () {
            $this->setDefaultFromPiwik();
            $this->addTo($this->recipient);
            $this->setSubject($this->getDefaultSubject());
            $this->addReplyTo($this->getFrom(), $this->getFromName());
            $this->setBodyText($this->getDefaultBodyText());
            $this->setWrappedHtmlBody($this->getDefaultBodyView());
        });
    }

    protected function getManageUsersLink(): string
    {
        return SettingsPiwik::getPiwikUrl()
            . 'index.php?'
            . Url::getQueryStringFromParameters(['module' => 'UsersManager', 'action' => 'index']);
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate('UsersManager_InactiveUsersNotificationEmailSubject');
    }

    protected function getDefaultBodyText(): string
    {
        $view = new View('@UsersManager/_inactiveUsersNotificationTextEmail.twig');
        $view->setContentType('text/plain');

        $this->assignCommonParameters($view);

        return $view->render();
    }

    protected function getDefaultBodyView(): View
    {
        $view = new View('@UsersManager/_inactiveUsersNotificationHtmlEmail.twig');

        $this->assignCommonParameters($view);

        return $view;
    }

    protected function assignCommonParameters(View $view): void
    {
        $view->inactiveUsers = $this->notification->getUsers();
        $view->manageUsersLink = $this->getManageUsersLink();
        $view->superuserLogin = $this->userModel->getUserByEmail($this->recipient)['login'];

        foreach ($this->emailData as $item => $value) {
            $view->assign($item, $value);
        }
    }
}
