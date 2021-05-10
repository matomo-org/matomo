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
    private $pluginName;

    public function __construct($login, $emailAddress, $pluginName, $superuser = null)
    {
        $this->pluginName = $pluginName;
        $this->superuser = $superuser;

        parent::__construct($login, $emailAddress);
    }


    protected function getBody()
    {
        $pluginNameMap = [
            'Login' => 'Brute Force',
            'TwoFactorAuth' => 'Two-factor auth',
            'CoreAdminHome' => 'CORS'
        ];

        if ($this->superuser) {
            return Piwik::translate('CoreAdminHome_SecurityNotificationSettingsChangedByOtherSuperUserBody', [$this->superuser, $pluginNameMap[$this->pluginName]]);
        }

        return Piwik::translate('CoreAdminHome_SecurityNotificationSettingsChangedByUserBody', [$pluginNameMap[$this->pluginName]]);
    }
}
