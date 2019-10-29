<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

abstract class Role
{
    abstract public function getName();
    abstract public function getId();
    abstract public function getDescription();

    public function getHelpUrl()
    {
        return '';
    }

}
