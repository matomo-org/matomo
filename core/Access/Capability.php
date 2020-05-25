<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

abstract class Capability
{
    abstract public function getId(): string;
    abstract public function getName(): string;
    abstract public function getCategory(): string;
    abstract public function getDescription(): string;
    abstract public function getIncludedInRoles(): array;

    public function getHelpUrl(): string
    {
        return '';
    }

    public function hasRoleCapability(string $idRole): bool
    {
        return \in_array($idRole, $this->getIncludedInRoles(), true);
    }

}
