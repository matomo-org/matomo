<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access\Capability;

use Piwik\Access\Capability;
use Piwik\Access\Role\Admin;
use Piwik\Access\Role\Write;

class TagManagerWrite extends Capability
{
    const ID = 'tagmanager_write';

    public function getName()
    {
        return 'Tag Manager Write';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'Lets you admin ...';
    }

    public function getIncludedInRoles()
    {
        return array(
            Write::ID,
            Admin::ID
        );
    }

}
