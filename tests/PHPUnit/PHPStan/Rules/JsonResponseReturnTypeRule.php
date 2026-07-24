<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A controller action marked #[JsonResponse] always returns the JSON body as a string, so it must
 * declare an explicit `string` return type.
 *
 * @implements Rule<ClassMethod>
 */
class JsonResponseReturnTypeRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!JsonResponseRuleHelper::isControllerScope($scope)) {
            return [];
        }

        if (!JsonResponseRuleHelper::hasJsonResponseAttribute($node, $scope)) {
            return [];
        }

        $returnType = $node->returnType;

        if ($returnType instanceof Identifier && strtolower($returnType->toString()) === 'string') {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Controller action %s() is marked #[\\Piwik\\Http\\JsonResponse] and must declare a'
                . ' "string" return type.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.returnType')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
