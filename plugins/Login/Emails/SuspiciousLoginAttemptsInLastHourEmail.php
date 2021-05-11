<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Login\Emails;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model;
use Piwik\View;

class SuspiciousLoginAttemptsInLastHourEmail extends Mail
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string|int
     */
    private $countOverall;

    /**
     * @var string|int
     */
    private $countDistinctIps;

    public function __construct($login, $countOverall, $countDistinctIps)
    {
        parent::__construct();

        $this->login = $login;
        $this->countOverall = $countOverall;
        $this->countDistinctIps = $countDistinctIps;

        $this->setUpEmail();
    }

    private function setUpEmail()
    {
        $model = new Model();
        $user = $model->getUser($this->login);
        if (empty($user)
            || empty($user['login'])
        ) {
            throw new \Exception('Unexpected error: unable to find user to send ' . __CLASS__);
        }

        $userEmailAddress = $user['email'];

        $this->setDefaultFromPiwik();
        $this->addTo($userEmailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('Login_SuspiciousLoginAttemptsInLastHourEmailSubject');
    }

    private function getDefaultBodyView()
    {
        $view = new View('@Login/_suspiciousLoginAttemptsEmail.twig');
        $view->login = $this->login;
        $view->countOverall = $this->countOverall;
        $view->countDistinctIps = $this->countDistinctIps;
        return $view->render();
    }
}