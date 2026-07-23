<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

// overrides the attributed action without re-declaring #[JsonResponse] -> must be flagged
class JsonResponseChildMissingRedeclare extends JsonResponseInheritanceBaseController
{
    public function jsonAction(): string
    {
        return parent::jsonAction();
    }
}
