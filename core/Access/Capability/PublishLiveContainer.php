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

class PublishLiveContainer extends Capability
{
    const ID = 'tagmanager_publish_live_container';

    public function getName()
    {
        return 'Tag Manager Publish Live Container';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'If allowed, lets you publish to the live container';
    }

    public function getIncludedInRoles()
    {
        return array(
            Admin::ID
        );
    }
}
