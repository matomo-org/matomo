<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

use Piwik\Http\JsonResponse;

// overrides and re-declares the attribute -> must NOT be flagged
class JsonResponseChildRedeclares extends JsonResponseInheritanceBaseController
{
    #[JsonResponse]
    public function jsonAction(): string
    {
        return parent::jsonAction();
    }
}
