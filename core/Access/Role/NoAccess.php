<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access\Role;

use Exception;
use Piwik\Access\Role;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;

class NoAccess extends Role
{
    const ID = 'noaccess';

    public function getName()
    {
        return 'No Access';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'Lets you view ...';
    }

    public function getHelpUrl()
    {
        return 'https://matomo.org/faq/general/faq_70/';
    }

    public function can()
    {
        return array();
    }
}
