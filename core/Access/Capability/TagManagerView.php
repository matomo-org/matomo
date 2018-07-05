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

// TO BE IGNORED
class TagManagerView extends Capability
{
    const ID = 'tagmanager_view';

    public function getName()
    {
        return 'Tag Manager View';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'Lets you view ...';
    }

}
