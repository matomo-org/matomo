<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Emails;

use Piwik\Piwik;
use Piwik\Site;
use Piwik\Mail;
use Piwik\View;

class UserInviteEmail extends mail
{
    /**
     * @var string
     */
    private $currentUser;

    /**
     * @var object
     */
    private $inviteUser;

    /**
     * @var string
     */
    private $idSite;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $currentUser
     * @param array $inviteUser
     * @param string $idSite
     * @param string $token
     */
    public function __construct($currentUser, $inviteUser, $idSite, $token)
    {
        parent::__construct();
        $this->currentUser = $currentUser;
        $this->inviteUser = $inviteUser;
        $this->idSite = $idSite;
        $this->token = $token;
        $this->setUpEmail();
    }


    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->inviteUser['email']);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_UserInviteSubject',
          [$this->currentUser, Site::getNameFor($this->idSite)]);
    }
    private function getDefaultSubjectWithStyle()
    {
        return Piwik::translate('CoreAdminHome_UserInviteSubject',
          ["<strong>".$this->currentUser."</strong>", "<strong>".Site::getNameFor($this->idSite)."</strong>"]);
    }

    protected function getDefaultBodyView()
    {
        $view = new View('@CoreAdminHome/_userInviteEmail.twig');
        $view->login = $this->inviteUser['login'];
        $view->emailAddress = $this->inviteUser['email'];
        $view->idSite = $this->idSite;
        $view->siteName = Site::getNameFor($this->idSite);
        $view->token = $this->token;

        // content line for email body
        $view->content = $this->getDefaultSubjectWithStyle();

        //notes for email footer
        $view->notes = Piwik::translate('CoreAdminHome_UserInviteNotes', [$this->currentUser,$this->currentUser]);
        return $view;
    }
}
