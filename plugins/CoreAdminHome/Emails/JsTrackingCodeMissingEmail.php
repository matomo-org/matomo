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
use Piwik\View;

class JsTrackingCodeMissingEmail extends Mail
{
    /**
     * @var string
     */
    private $siteUrl;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var int[]
     */
    private $idSites;

    public function __construct($siteUrl, $login, $emailAddress, $idSites)
    {
        parent::__construct();

        $this->siteUrl = $siteUrl;
        $this->login = $login;
        $this->emailAddress = $emailAddress;
        $this->idSites = $idSites;

        $this->setUpEmail();
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
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
     * @return int[]
     */
    public function getIdSites()
    {
        return $this->idSites;
    }

    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_MissingTrackingCodeEmailSubject', [$this->siteUrl]);
    }

    private function getDefaultBodyView()
    {
        $view = new View('@CoreAdminHome/_jsTrackingCodeMissingEmail.twig');
        $view->siteUrl = $this->siteUrl;
        $view->login = $this->login;
        $view->emailAddress = $this->emailAddress;
        $view->idSites = $this->idSites;
        return $view;
    }
}