<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access\Role;

use Piwik\Access\Role;

class Write extends Role
{
    const ID = 'write';

    public function getName()
    {
        return 'Write';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'Lets you write ...';
    }

    public function getHelpUrl()
    {
        return 'TBD';
    }

}
