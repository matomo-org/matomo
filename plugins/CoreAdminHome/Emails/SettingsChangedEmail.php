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

class SettingsChangedEmail extends SecurityNotificationEmail
{
    /**
     * @var string
     */
    private $superuser;

    /**
     * @var string
     */
    private $pluginNames;

    public function __construct($login, $emailAddress, $pluginNames, $superuser = null)
    {
        $this->pluginNames = $pluginNames;
        $this->superuser = $superuser;

        parent::__construct($login, $emailAddress);
    }

    protected function getBody()
    {
        if ($this->superuser) {
            return Piwik::translate('CoreAdminHome_SecurityNotificationSettingsChangedByOtherSuperUserBody', [$this->superuser, $this->pluginNames]);
        }

        return Piwik::translate('CoreAdminHome_SecurityNotificationSettingsChangedByUserBody', [$this->pluginNames]) . ' ' . Piwik::translate('UsersManager_IfThisWasYouPasswordChange');
    }
}
