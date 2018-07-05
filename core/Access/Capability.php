<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

abstract class Capability
{
    abstract public function getName();
    abstract public function getId();
    abstract public function getDescription();
    public function getHelpUrl()
    {
        return '';
    }

    public function requiresRole()
    {
        return array();
    }

    public function requiresCapability()
    {
        throw new \Exception('We could implement this any time later...');
        return array();
    }
}
