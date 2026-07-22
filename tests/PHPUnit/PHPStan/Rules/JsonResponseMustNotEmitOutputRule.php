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
 * A controller action carrying #[JsonResponse] must not emit output (echo, print, flush) before it
 * returns. The JSON header is applied only after the action returns, but emitting output commits the
 * response headers first, so on setups without output buffering the JSON Content-Type would be lost.
 * Build the payload and return it as a string instead.
 *
 * @implements Rule<ClassMethod>
 */
class JsonResponseMustNotEmitOutputRule implements Rule
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

        foreach (JsonResponseRuleHelper::findOutputStatements($node) as $output) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Controller action %s() is marked #[\\Piwik\\Http\\JsonResponse] but emits output'
                . ' (echo/print/flush) before returning; this commits the response headers early and'
                . ' can prevent the JSON Content-Type from being applied. Return the JSON string instead.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.output')
                ->line($output->getStartLine())
                ->build();
        }

        return $errors;
    }
}
