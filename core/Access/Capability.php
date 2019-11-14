<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

abstract class Capability
{
    abstract public function getId();
    abstract public function getName();
    abstract public function getCategory();
    abstract public function getDescription();
    abstract public function getIncludedInRoles();

    public function getHelpUrl()
    {
        return '';
    }

    public function hasRoleCapability($idRole)
    {
        return in_array($idRole, $this->getIncludedInRoles(), true);
    }

}
