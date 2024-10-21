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

class PasswordResetCancelEmail extends Mail
{
    /**
     * @var string
     */
    private $login;

    public function __construct($login)
    {
        parent::__construct();

        $this->login = $login;

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
        return Piwik::translate('Login_PasswordResetCancelEmailSubject');
    }

    private function getDefaultBodyView()
    {
        $view = new View('@Login/_passwordResetCancelEmail.twig');
        $view->login = $this->login;

        return $view->render();
    }
}
