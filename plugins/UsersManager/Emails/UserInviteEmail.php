<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UsersManager\Emails;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\View;

class UserInviteEmail extends Mail
{
    /**
     * @var string
     */
    private $currentUser;

    /**
     * @var object
     */
    private $invitedUser;

    /**
     * @var string
     */
    private $token;
    /**
     * @var int
     */
    private $expiryInDays;

    private $siteName;

    /**
     * @param string $currentUser
     * @param array  $invitedUser
     * @param string $siteName
     * @param string $token
     * @param int    $expiryInDays
     */
    public function __construct($currentUser, $invitedUser, $siteName, $token, $expiryInDays)
    {
        parent::__construct();
        $this->currentUser  = $currentUser;
        $this->invitedUser  = $invitedUser;
        $this->token        = $token;
        $this->expiryInDays = $expiryInDays;
        $this->siteName = $siteName;
        $this->setUpEmail();
    }


    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->invitedUser['email']);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject()
    {
        return Piwik::translate(
            'CoreAdminHome_UserInviteSubject',
            [$this->currentUser, $this->siteName]
        );
    }

    private function getDefaultSubjectWithStyle()
    {
        return Piwik::translate(
            'CoreAdminHome_UserInviteSubject',
            ['<strong>' . $this->currentUser . '</strong>', '<strong>' . $this->siteName . '</strong>']
        );
    }

    protected function getDefaultBodyView()
    {
        $view = new View('@UsersManager/_userInviteEmail.twig');
        $view->login = $this->invitedUser['login'];
        $view->loginPlugin = Piwik::getLoginPluginName();
        $view->emailAddress = $this->invitedUser['email'];
        $view->token = $this->token;

        // content line for email body
        $view->content = $this->getDefaultSubjectWithStyle();

        //notes for email footer
        $view->notes = Piwik::translate('CoreAdminHome_UserInviteNotes', [$this->currentUser,  $this->expiryInDays]);
        return $view;
    }
}