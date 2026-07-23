<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

// overrides the attributed grandparent action without re-declaring -> must be flagged
class JsonResponseIntermediateController extends JsonResponseGrandparentController
{
    public function foo(): string
    {
        return '<p>html</p>';
    }
}
