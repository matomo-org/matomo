<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

use Piwik\Http\JsonResponse;

/**
 * Fixture demonstrating #[JsonResponse] used outside a controller. Never executed; only analysed.
 */
class NotAController
{
    #[JsonResponse]
    public function build(): string
    {
        return json_encode([]);
    }
}
