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
 * The #[JsonResponse] attribute is only honoured for public controller actions, because the header
 * is applied when FrontController dispatches to them. Using it anywhere else has no effect and is
 * therefore misleading.
 *
 * @implements Rule<ClassMethod>
 */
class JsonResponseAttributePlacementRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassMethod) {
            return [];
        }

        if (!JsonResponseRuleHelper::hasJsonResponseAttribute($node, $scope)) {
            return [];
        }

        $methodName = $node->name->toString();

        if (!JsonResponseRuleHelper::isControllerScope($scope)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Attribute #[\\Piwik\\Http\\JsonResponse] has no effect on %s(); it is only applied'
                    . ' to controller actions (methods of a Piwik\\Plugin\\Controller subclass).',
                    $methodName
                ))
                    ->identifier('matomo.jsonResponse.notInController')
                    ->line($node->getStartLine())
                    ->build(),
            ];
        }

        if (!$node->isPublic()) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Attribute #[\\Piwik\\Http\\JsonResponse] must be applied to a public controller'
                    . ' action, but %s() is not public.',
                    $methodName
                ))
                    ->identifier('matomo.jsonResponse.notPublic')
                    ->line($node->getStartLine())
                    ->build(),
            ];
        }

        return [];
    }
}
