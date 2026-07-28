<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Piwik\Tests\PHPStan\Rules\JsonResponseAttributePlacementRule;

/**
 * @group Core
 * @extends RuleTestCase<JsonResponseAttributePlacementRule>
 */
class JsonResponseAttributePlacementRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new JsonResponseAttributePlacementRule();
    }

    public function testRule(): void
    {
        $this->analyse([
            __DIR__ . '/data/JsonResponseController.php',
            __DIR__ . '/data/NotAController.php',
        ], [
            [
                'Attribute #[\Piwik\Http\JsonResponse] must be applied to a public controller action, but'
                . ' notPublic() is not public.',
                52,
            ],
            [
                'Attribute #[\Piwik\Http\JsonResponse] has no effect on build(); it is only applied to'
                . ' controller actions (methods of a Piwik\Plugin\Controller subclass).',
                19,
            ],
        ]);
    }
}
