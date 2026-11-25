<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Access;

/**
 * Base class for capabilities that plugins add using the `Access.Capability.addCapabilities` event.
 *
 * @api
 */
abstract class Capability
{
    /**
     * Returns the unique ID of the capability, e.g. `'tagmanager_publish_live_container'`. It may only contain
     * `a-zA-Z0-9_-` and must not be longer than 50 characters.
     *
     * @return string The capability ID.
     */
    abstract public function getId(): string;

    /**
     * Returns the display name of the capability.
     *
     * @return string The translated name.
     */
    abstract public function getName(): string;

    /**
     * Returns the category the capability is grouped under.
     *
     * @return string The translated category name.
     */
    abstract public function getCategory(): string;

    /**
     * Returns the description of the capability.
     *
     * @return string The translated description.
     */
    abstract public function getDescription(): string;

    /**
     * Returns the roles that include this capability by default, e.g. `Piwik\Access\Role\Admin::ID`.
     *
     * @return string[] The role IDs.
     */
    abstract public function getIncludedInRoles(): array;

    /**
     * Returns the URL of the help page for the capability.
     *
     * @return string The URL, or an empty string if there is none.
     */
    public function getHelpUrl(): string
    {
        return '';
    }

    /**
     * Returns whether the given role includes this capability by default.
     *
     * @param string $idRole The role ID.
     * @return bool True if the role is listed in {@link getIncludedInRoles()}.
     */
    public function hasRoleCapability(string $idRole): bool
    {
        return \in_array($idRole, $this->getIncludedInRoles(), true);
    }
}
