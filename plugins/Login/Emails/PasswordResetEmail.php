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
    protected $login;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $cancelUrl;

    /**
     * @var string
     */
    protected $resetUrl;

    public function __construct(string $login, string $ip, string $resetUrl, string $cancelUrl)
    {
        parent::__construct();

        $this->login = $login;
        $this->ip = $ip;
        $this->resetUrl = $resetUrl;
        $this->cancelUrl = $cancelUrl;

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
        return Piwik::translate('Login_PasswordResetEmailSubject');
    }

    protected function getDefaultBodyView(): string
    {
        $view = new View('@Login/_passwordResetEmail.twig');
        $view->login = $this->login;
        $view->ip = $this->ip;
        $view->resetUrl = $this->resetUrl;
        $view->cancelUrl = $this->cancelUrl;

        return $view->render();
    }
}
