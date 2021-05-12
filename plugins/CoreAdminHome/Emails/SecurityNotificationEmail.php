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
use Psr\Log\LoggerInterface;
use Piwik\Container\StaticContainer;

abstract class SecurityNotificationEmail extends Mail
{
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

    public function safeSend()
    {
        try {
            $this->send();
        } catch (\Exception $e) {
            // we do nothing but log if the email send was unsuccessful
            StaticContainer::get(LoggerInterface::class)->warning('Could not send {class} email: {exception}', ['class' => get_class($this), 'exception' => $e]);
        }
    }

    abstract protected function getBody();
}
