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

class ChallengeAddedUser extends Challenge
{
    public function getName()
    {
        return Piwik::translate('Tour_AddUser');
    }

    public function getId()
    {
        return 'add_user';
    }

    public function getInAppLink()
    {
        return array('module' => 'UsersManager', 'action' => 'index', 'widget' => false);
    }


}