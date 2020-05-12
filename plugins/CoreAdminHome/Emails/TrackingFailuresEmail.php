<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Emails;

use Piwik\Access;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

class TrackingFailuresEmail extends Mail
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
    private $numFailures;

    public function __construct($login, $emailAddress, $numFailures)
    {
        parent::__construct();

        $this->login = $login;
        $this->emailAddress = $emailAddress;
        $this->numFailures = (int)$numFailures;

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
    public function getNumFailures()
    {
        return $this->numFailures;
    }

    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->emailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_TrackingFailuresEmailSubject');
    }

    private function getDefaultBodyView()
    {
        $view = new View('@CoreAdminHome/_trackingFailuresEmail.twig');
        $view->login = $this->login;
        $view->emailAddress = $this->emailAddress;
        $view->numFailures = $this->numFailures;

        $sitesId = Access::getInstance()->getSitesIdWithAtLeastViewAccess();
        $idSite = false;
        if (!empty($sitesId)) {
            $idSite = array_shift($sitesId);
        }
        $view->trackingFailuresUrl = SettingsPiwik::getPiwikUrl() . 'index.php?' . Url::getQueryStringFromParameters([
            'module' => 'CoreAdminHome',
            'action' => 'trackingFailures',
            'period' => 'day',
            'date' => 'yesterday',
            'idSite' => $idSite
        ]);
        return $view;
    }
}