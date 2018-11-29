<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\Activity;

use Piwik\Piwik;
use Piwik\Plugins\ActivityLog\Activity\Activity;

class TwoFactorEnabled extends Activity
{
    protected $eventName = 'TwoFactorAuth.enabled';

    public function extractParams($eventData)
    {
        list($userLogin) = $eventData;

        return [
            'login' => $userLogin
        ];
    }

    public function getTranslatedDescription($activityData, $performingUser)
    {
        return Piwik::translate('TwoFactorAuth_ActivityEnabledTwoFactorAuth');
    }
}
