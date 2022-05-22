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

class UserAcceptInvitationEmail extends SecurityNotificationEmail
{
    /**
     * @var string
     */
    private $userLogin;

    public function __construct($login, $emailAddress, $userLogin)
    {
        $this->userLogin = $userLogin;

        parent::__construct($login, $emailAddress);
    }

    protected function getBody()
    {
        return Piwik::translate('CoreAdminHome_SecurityNotificationUserAcceptInviteBody', [$this->userLogin]);
    }
}
