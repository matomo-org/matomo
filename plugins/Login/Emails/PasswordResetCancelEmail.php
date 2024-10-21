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
    protected $login;

    public function __construct(string $login)
    {
        parent::__construct();

        $this->login = $login;

        $this->setUpEmail();
    }

    private function setUpEmail(): void
    {
        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];

        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($replytoEmailAddress, $replytoEmailName);
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject(): string
    {
        return Piwik::translate('Login_PasswordResetCancelEmailSubject');
    }

    protected function getDefaultBodyView(): string
    {
        $view = new View('@Login/_passwordResetCancelEmail.twig');
        $view->login = $this->login;

        return $view->render();
    }
}
