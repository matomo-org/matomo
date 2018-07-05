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

class WebContent extends Capability
{
    const ID = 'web_content';

    public function getName()
    {
        return 'Web Content';
    }

    public function getId()
    {
        return self::ID;
    }

    public function getDescription()
    {
        return 'Lets you write content that will be executed on the website. Useful for example in tag manager.';
    }

    public function requiresRole()
    {
        return array(Admin::ID);
    }
}
