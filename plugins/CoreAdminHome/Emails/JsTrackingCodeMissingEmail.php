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
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Url;
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
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_MissingTrackingCodeEmailSubject', ["'" . Site::getNameFor($this->idSite) . "'"]);
    }

    protected function getDefaultBodyView()
    {
        $view = new View('@CoreAdminHome/_jsTrackingCodeMissingEmail.twig');
        $view->login = $this->login;
        $view->emailAddress = $this->emailAddress;
        $view->idSite = $this->idSite;
        $view->siteName = Site::getNameFor($this->idSite);
        $view->trackingCodeUrl = SettingsPiwik::getPiwikUrl() . 'index.php?' . Url::getQueryStringFromParameters([
            'idSite' => $this->idSite,
            'module' => 'CoreAdminHome',
            'action' => 'trackingCodeGenerator',
            'period' => 'day',
            'date' => 'yesterday',
        ]);
        return $view;
    }
}