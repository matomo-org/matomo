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
 * A controller action carrying #[JsonResponse] must not also call Json::sendHeaderJSON() itself:
 * the attribute already sends the header, so the manual call is redundant.
 *
 * @implements Rule<ClassMethod>
 */
class RedundantJsonHeaderCallRule implements Rule
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

        if (!JsonResponseRuleHelper::isControllerScope($scope)) {
            return [];
        }

        if (!JsonResponseRuleHelper::hasJsonResponseAttribute($node, $scope)) {
            return [];
        }

        $errors = [];

        foreach (JsonResponseRuleHelper::findAllJsonHeaderCalls($node, $scope) as $call) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Method %s() carries #[\\Piwik\\Http\\JsonResponse], which already sends the JSON'
                . ' header; the manual Json::sendHeaderJSON() call is redundant and should be removed.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.redundantCall')
                ->line($call->getStartLine())
                ->build();
        }

        return $errors;
    }
}
