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
use Piwik\Tests\PHPStan\Rules\JsonResponseOverrideMustRedeclareRule;

/**
 * @group Core
 * @extends RuleTestCase<JsonResponseOverrideMustRedeclareRule>
 */
class JsonResponseOverrideMustRedeclareRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new JsonResponseOverrideMustRedeclareRule();
    }

    public function testRule(): void
    {
        $this->analyse([
            __DIR__ . '/data/JsonResponseInheritanceBaseController.php',
            __DIR__ . '/data/JsonResponseChildMissingRedeclare.php',
            __DIR__ . '/data/JsonResponseChildRedeclares.php',
            __DIR__ . '/data/JsonResponseChildOverridesPlain.php',
        ], [
            [
                'Controller action jsonAction() overrides an action marked #[\Piwik\Http\JsonResponse]'
                . ' but does not re-declare the attribute. PHP does not inherit method attributes, so'
                . ' the override must repeat #[\Piwik\Http\JsonResponse] to keep serving JSON.',
                15,
            ],
        ]);
    }
}
