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

class UseCustomTemplates extends Capability
{
    const ID = 'tagmanager_use_custom_templates';

    public function getId()
    {
        return self::ID;
    }

    public function getCategory()
    {
        return 'Tag Manager';
    }

    public function getName()
    {
        return 'Use Custom Templates';
    }

    public function getDescription()
    {
        return 'Lets you write content that will be executed on the website. Useful for example in tag manager.';
    }

    public function getIncludedInRoles()
    {
        return array(
            Admin::ID
        );
    }
}
