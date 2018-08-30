<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Emails;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\View;

class JsTrackingCodeMissingEmail extends Mail
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var int
     */
    private $idSite;

    public function __construct($login, $emailAddress, $idSite)
    {
        parent::__construct();

        $this->login = $login;
        $this->emailAddress = $emailAddress;
        $this->idSite = $idSite;

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

    /**
     * @return int
     */
    public function getIdSite()
    {
        return $this->idSite;
    }

    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->setReplyTo($this->getFrom());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_MissingTrackingCodeEmailSubject', [Site::getMainUrlFor($this->idSite)]);
    }

    private function getDefaultBodyView()
    {
        $view = new View('@CoreAdminHome/_jsTrackingCodeMissingEmail.twig');
        $view->login = $this->login;
        $view->emailAddress = $this->emailAddress;
        $view->idSite = $this->idSite;
        $view->siteUrl = Site::getMainUrlFor($this->idSite);
        return $view;
    }
}