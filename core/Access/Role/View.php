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

// TO BE IGNORED
class View extends Role
{
    const ID = 'view';

    public function getName()
    {
        return 'View';
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
        return array(
            new \Piwik\Access\Capability\AnalyticsView(),
            new \Piwik\Access\Capability\TagManagerView()
        );
    }

}
