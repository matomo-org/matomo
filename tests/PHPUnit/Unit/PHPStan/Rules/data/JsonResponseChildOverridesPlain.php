<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

// overrides a non-attributed action -> must NOT be flagged
class JsonResponseChildOverridesPlain extends JsonResponseInheritanceBaseController
{
    public function plainAction(): string
    {
        return 'still plain';
    }
}
