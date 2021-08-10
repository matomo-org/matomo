<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Emails;

use Piwik\Mail;
use Piwik\View;
use Piwik\Piwik;

abstract class SecurityNotificationEmail extends Mail
{
    public static $notifyPluginList = [
        'Login' => 'CoreAdminHome_BruteForce',
        'TwoFactorAuth' => 'CoreAdminHome_TwoFactorAuth',
        'CoreAdminHome' => 'CoreAdminHome_Cors'
    ];

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $emailAddress;

    public function __construct($login, $emailAddress)
    {
        parent::__construct();

        $this->login = $login;
        $this->emailAddress = $emailAddress;

        $this->setUpEmail();
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }


    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_SecurityNotificationEmailSubject');
    }

    protected function getDefaultBodyView()
    {
        $view = new View('@CoreAdminHome/_securityNotificationEmail.twig');
        $view->login = $this->login;
        $view->body = $this->getBody();

        return $view;
    }

    abstract protected function getBody();
}
