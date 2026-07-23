<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

use Piwik\Http\JsonResponse;

class JsonResponseGrandparentController extends \Piwik\Plugin\Controller
{
    #[JsonResponse]
    public function foo(): string
    {
        return json_encode([]);
    }
}
