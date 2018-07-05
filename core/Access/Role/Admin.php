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

// TO BE IGNORED!
class Admin extends Role
{
    const ID = 'admin';

    public function getName()
    {
        return 'Admin';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'Lets you admin ...';
    }

    public function getHelpUrl()
    {
        return 'https://matomo.org/faq/general/faq_69/';
    }

    public function can()
    {
        return array(
            new \Piwik\Access\Capability\Admin(),
            new \Piwik\Access\Capability\PublishLiveContainer(),
            new \Piwik\Access\Capability\WebContent(),
            new \Piwik\Access\Capability\SegmentsWrite(),
            new \Piwik\Access\Capability\AnalyticsWrite(),
            new \Piwik\Access\Capability\TagManagerWrite(),
        );
    }

}
