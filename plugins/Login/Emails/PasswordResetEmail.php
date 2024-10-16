<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Emails;

use Piwik\Config;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\View;

class PasswordResetEmail extends Mail
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $resetUrl;

    /**
     * @var string
     */
    private $wasNotMeUrl;

    public function __construct($login, $ip, $resetUrl, $wasNotMeUrl)
    {
        parent::__construct();

        $this->login = $login;
        $this->ip = $ip;
        $this->resetUrl = $resetUrl;
        $this->wasNotMeUrl = $wasNotMeUrl;

        $this->setUpEmail();
    }

    private function setUpEmail()
    {
        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];

        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($replytoEmailAddress, $replytoEmailName);
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('Login_PasswordResetEmailSubject');
    }

    private function getDefaultBodyView()
    {
        $view = new View('@Login/_passwordResetEmail.twig');
        $view->login = $this->login;
        $view->ip = $this->ip;
        $view->resetUrl = $this->resetUrl;
        $view->wasNotMeUrl = $this->wasNotMeUrl;

        return $view->render();
    }
}
