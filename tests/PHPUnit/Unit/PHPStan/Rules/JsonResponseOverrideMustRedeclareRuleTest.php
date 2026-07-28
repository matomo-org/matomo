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

    public function testOnlyNearestAncestorDeclarationDecides(): void
    {
        // Grandparent has the attribute; Intermediate overrides without it (converts to HTML), so
        // Intermediate must re-declare, but Grandchild's nearest ancestor (Intermediate) is not JSON,
        // so Grandchild must NOT be flagged.
        $this->analyse([
            __DIR__ . '/data/JsonResponseGrandparentController.php',
            __DIR__ . '/data/JsonResponseIntermediateController.php',
            __DIR__ . '/data/JsonResponseGrandchildController.php',
        ], [
            [
                'Controller action foo() overrides an action marked #[\Piwik\Http\JsonResponse] but'
                . ' does not re-declare the attribute. PHP does not inherit method attributes, so'
                . ' the override must repeat #[\Piwik\Http\JsonResponse] to keep serving JSON.',
                15,
            ],
        ]);
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
