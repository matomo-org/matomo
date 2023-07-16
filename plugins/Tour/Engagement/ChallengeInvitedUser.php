<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;
use Piwik\Url;

class ChallengeInvitedUser extends Challenge
{
    public function getName()
    {
        return Piwik::translate('Tour_InviteUser');
    }

    public function getDescription()
    {
        return Piwik::translate('UsersManager_PluginDescription');
    }

    public function getId()
    {
        // still named `add_user`, so users, that directly added users before we introduced the invite system don't see the challenge again
        return 'add_user';
    }

    public function getUrl()
    {
        return 'index.php' . Url::getCurrentQueryStringWithParametersModified(array('module' => 'UsersManager', 'action' => 'index', 'widget' => false));
    }


}
