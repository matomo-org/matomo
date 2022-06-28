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
    private $user;

    /**
     * @var string
     */
    private $token;
    /**
     * @var int
     */
    private $expireDays;

    private $siteName;

    /**
     * @param string $currentUser
     * @param array $user
     * @param string $siteName
     * @param string $token
     * @param int $expireDays
     */
    public function __construct($currentUser, $user, $siteName, $token, $expireDays)
    {
        parent::__construct();
        $this->currentUser = $currentUser;
        $this->user = $user;
        $this->token = $token;
        $this->expireDays = $expireDays;
        $this->siteName = $siteName;
        $this->setUpEmail();
    }


    private function setUpEmail()
    {
        $this->setDefaultFromPiwik();
        $this->addTo($this->user['email']);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultSubject()
    {
        return Piwik::translate('CoreAdminHome_UserInviteSubject',
          [$this->currentUser, $this->siteName]);
    }

    private function getDefaultSubjectWithStyle()
    {
        return Piwik::translate('CoreAdminHome_UserInviteSubject',
          ["<strong>" . $this->currentUser . "</strong>", "<strong>" . $this->user['login'] . "</strong>"]);
    }

    protected function getDefaultBodyView()
    {
        $view = new View('@UsersManager/_userInviteEmail.twig');
        $view->login = $this->user['login'];
        $view->emailAddress = $this->user['email'];
        $view->token = $this->token;

        // content line for email body
        $view->content = $this->getDefaultSubjectWithStyle();

        //notes for email footer
        $view->notes = Piwik::translate('CoreAdminHome_UserInviteNotes', [$this->currentUser,  $this->expireDays]);
        return $view;
    }
}