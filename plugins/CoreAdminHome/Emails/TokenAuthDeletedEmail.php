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

class TokenAuthDeletedEmail extends SecurityNotificationEmail
{
    /**
     * @var string
     */
    private $tokenDescription;

    /**
     * @var bool
     */
    private $all;

    public function __construct($login, $emailAddress, $tokenDescription, $all = false)
    {
        $this->tokenDescription = $tokenDescription;
        $this->all = $all;

        parent::__construct($login, $emailAddress);
    }

    protected function getBody()
    {
        if ($this->all) {
            return Piwik::translate('CoreAdminHome_SecurityNotificationAllTokenAuthDeletedBody') . ' ' . Piwik::translate('UsersManager_IfThisWasYouPasswordChange');
        }

        return Piwik::translate('CoreAdminHome_SecurityNotificationTokenAuthDeletedBody', [$this->tokenDescription]) . ' ' . Piwik::translate('UsersManager_IfThisWasYouPasswordChange');
    }
}
