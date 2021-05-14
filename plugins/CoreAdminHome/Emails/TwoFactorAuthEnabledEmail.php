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
use Piwik\Plugins\CoreAdminHome\Emails\SecurityNotificationEmail;

class TwoFactorAuthEnabledEmail extends SecurityNotificationEmail
{
    /**
     * @var string[]
     */
    private $superuserEmails;

    public function __construct($login, $emailAddress, $superuserEmails)
    {
        $this->superuserEmails = $superuserEmails;

        parent::__construct($login, $emailAddress);
    }

    protected function getBody()
    {
        $email = $this->getASuperUserEmail();

        if (!$email) {
            return Piwik::translate('CoreAdminHome_SecurityNotificationTwoFactorAuthEnabledBodyNoEmail');
        }

        return Piwik::translate('CoreAdminHome_SecurityNotificationTwoFactorAuthEnabledBody', [$email]);
    }

    private function getASuperUserEmail()
    {
        if (count($this->superuserEmails)) {
            foreach ($this->superuserEmails as $login => $email) {
                return $email;
            }
        }

        return false;
    }
}
