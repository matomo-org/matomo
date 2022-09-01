<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

use Piwik\Access\Role\Admin;
use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\Piwik;
use Exception;

class RolesProvider
{
    /**
     * @return Role[]
     */
    public function getAllRoles(): array
    {
        return array(
            new View(),
            new Write(),
            new Admin()
        );
    }

    /**
     * Returns the list of the existing Access level.
     * Useful when a given API method requests a given access Level.
     * We first check that the required access level exists.
     *
     * @return string[]
     */
    public function getAllRoleIds(): array
    {
        $ids = array();
        foreach ($this->getAllRoles() as $role) {
            $ids[] = $role->getId();
        }
        return $ids;
    }

    public function isValidRole(string $roleId): bool
    {
        $roles = $this->getAllRoleIds();

        return \in_array($roleId, $roles, true);
    }

    /**
     * @param $roleId
     * @throws Exception
     */
    public function checkValidRole(string $roleId): void
    {
        if (!$this->isValidRole($roleId)) {
            $roles = $this->getAllRoleIds();
            throw new Exception(Piwik::translate("UsersManager_ExceptionAccessValues", [implode(", ", $roles), $roleId]));
        }
    }

}
