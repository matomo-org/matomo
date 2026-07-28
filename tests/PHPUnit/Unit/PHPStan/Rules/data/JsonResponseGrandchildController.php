<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

// nearest ancestor (Intermediate) already dropped the attribute -> must NOT be flagged
class JsonResponseGrandchildController extends JsonResponseIntermediateController
{
    public function foo(): string
    {
        return '<p>child</p>';
    }
}
