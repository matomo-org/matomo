<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Login\Emails;

use Piwik\Common;
use Piwik\Config;
use Piwik\Mail;
use Piwik\Piwik;

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

    public function __construct($login, $ip, $resetUrl)
    {
        parent::__construct();

        $this->login = $login;
        $this->ip = $ip;
        $this->resetUrl = $resetUrl;

        $this->setUpEmail();
    }

    private function setUpEmail()
    {
        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];

        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($replytoEmailAddress, $replytoEmailName);
        $this->setWrappedHtmlBody($this->getHTMLBody());
        $this->setBodyText($this->getDefaultBodyText());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('Login_MailTopicPasswordChange');
    }

    /**
     * Get the translated plain text email body with the reset link
     *
     * @return string
     */
    private function getDefaultBodyText() : string
    {
        return Piwik::translate('Login_MailPasswordChangeBody2',
            [Common::sanitizeInputValue($this->login), Common::sanitizeInputValue($this->ip), Common::sanitizeInputValue($this->resetUrl)]);
    }

    /**
     * Create the HTML email body from the plain text body
     *
     * @return string
     */
    private function getHTMLBody() : string
    {
        return '<p>' . str_replace(
            "\n\n",
            "</p><p>",
            Piwik::translate('Login_MailPasswordChangeBody2',
                [Common::sanitizeInputValue($this->login),
                 Common::sanitizeInputValue($this->ip),
                '<p style="word-break: break-all"><a href="'.Common::sanitizeInputValue($this->resetUrl).'">'.
                Common::sanitizeInputValue($this->resetUrl).'</a></p>'])
        ) . "</p>";
    }
}