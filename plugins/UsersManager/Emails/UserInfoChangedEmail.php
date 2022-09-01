<?php

namespace Piwik\Plugins\UsersManager\Emails;

use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Mail;
use Piwik\View;

class UserInfoChangedEmail extends Mail
{

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $changedNewValue;

    /**
     * @var string
     */
    private $deviceDescription;

    /**
     * @var string
     */
    private $login;

    public function __construct($type, $changedNewValue, $deviceDescription, $login)
    {
        parent::__construct();
        $this->type = $type;
        $this->changedNewValue = $changedNewValue;
        $this->deviceDescription = $deviceDescription;
        $this->login = $login;
        $this->setUpEmail();
    }


    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->setWrappedHtmlBody($this->getDefaultBodyView());

        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];
        $this->addReplyTo($replytoEmailAddress, $replytoEmailName);
    }


    /**
     * @return View
     */
    protected function getDefaultBodyView()
    {
        $deviceDescription = $this->deviceDescription;

        $view = new View('@UsersManager/_userInfoChangedEmail.twig');
        $view->type = $this->type;
        $view->accountName = Common::sanitizeInputValue($this->login);
        $view->newEmail = Common::sanitizeInputValue($this->changedNewValue);
        $view->ipAddress = IP::getIpFromHeader();
        $view->deviceDescription = $deviceDescription;
        return $view;
    }
}