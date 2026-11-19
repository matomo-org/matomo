<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

/**
 * @internal Used by {@link ForcedQueryCapabilities}.
 */
trait ForcedQueryCapabilitiesTrait
{
    /** @var bool */
    public $forcedWindowFunctions = false;

    /** @var bool */
    public $forcedGroupByWithRollup = true;

    public function supportsWindowFunctions(): bool
    {
        return $this->forcedWindowFunctions;
    }

    public function supportsGroupByWithRollup(): bool
    {
        return $this->forcedGroupByWithRollup;
    }
}
