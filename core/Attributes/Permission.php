<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Permission
{
    /** @var string */
    private $requirement;

    /** @var string|null */
    private $parameter;

    public function __construct(string $requirement, ?string $parameter = null)
    {
        $this->requirement = $requirement;
        $this->parameter = $parameter;
    }

    public function getRequirement(): string
    {
        return $this->requirement;
    }

    public function getParameter(): ?string
    {
        return $this->parameter;
    }
}
