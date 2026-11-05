<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace\PluginTrial;

/**
 * A stored decision that ended one or more of a plugin's pending requests
 */
final class Decision
{
    public function __construct(
        public readonly int $id,
        public readonly int $numRequests
    ) {
    }
}
