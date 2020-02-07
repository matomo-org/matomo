<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access\Role;

use Piwik\Access\Role;
use Piwik\Piwik;

class Admin extends Role
{
    const ID = 'admin';

    public function getName()
    {
        return Piwik::translate('UsersManager_PrivAdmin');
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return Piwik::translate('UsersManager_PrivAdminDescription', array(
            Piwik::translate('UsersManager_PrivWrite')
        ));
    }

    public function getHelpUrl()
    {
        return 'https://matomo.org/faq/general/faq_69/';
    }

}
