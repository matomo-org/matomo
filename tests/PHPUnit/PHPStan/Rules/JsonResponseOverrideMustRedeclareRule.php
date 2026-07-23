<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A controller action that overrides a parent action carrying #[JsonResponse] must re-declare the
 * attribute. PHP does not inherit method attributes, and FrontController only honours the attribute
 * declared directly on the dispatched method, so an override that omits it would silently serve JSON
 * without the JSON Content-Type (and would escape the other #[JsonResponse] checks).
 *
 * @implements Rule<ClassMethod>
 */
class JsonResponseOverrideMustRedeclareRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!JsonResponseRuleHelper::isPublicControllerAction($node, $scope)) {
            return [];
        }

        if (JsonResponseRuleHelper::hasJsonResponseAttribute($node, $scope)) {
            return [];
        }

        if (!JsonResponseRuleHelper::overridesAttributedAction($node, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Controller action %s() overrides an action marked #[\\Piwik\\Http\\JsonResponse] but'
                . ' does not re-declare the attribute. PHP does not inherit method attributes, so the'
                . ' override must repeat #[\\Piwik\\Http\\JsonResponse] to keep serving JSON.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.overrideMustRedeclare')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
