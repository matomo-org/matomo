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

class View extends Role
{
    const ID = 'view';

    public function getName()
    {
        return Piwik::translate('UsersManager_PrivView');
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return Piwik::translate('UsersManager_PrivViewDescription');
    }

    public function getHelpUrl()
    {
        return 'https://matomo.org/faq/general/faq_70/';
    }


}
