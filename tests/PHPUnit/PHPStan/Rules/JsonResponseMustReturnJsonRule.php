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
 * A controller action carrying #[JsonResponse] serves JSON on every path (FrontController applies the
 * JSON Content-Type unconditionally after it returns). It therefore must not return a non-JSON value
 * on any path — e.g. HTML or plain text — otherwise that branch is served as application/json. Such a
 * mixed action must be split into separate actions (or drop the attribute).
 *
 * Only string literals that fail to decode as JSON are flagged as "definitely non-JSON"; indirect
 * returns (a variable or a method call) are left alone, since they may produce JSON elsewhere.
 *
 * @implements Rule<ClassMethod>
 */
class JsonResponseMustReturnJsonRule implements Rule
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

        $errors = [];

        foreach (JsonResponseRuleHelper::findNonJsonStringLiteralReturns($node) as $return) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Controller action %s() is marked #[\\Piwik\\Http\\JsonResponse] but returns a non-JSON'
                . ' value on this path; such an action must return JSON on every path. Split it into'
                . ' separate actions (or remove the attribute).',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.nonJsonReturn')
                ->line($return->getStartLine())
                ->build();
        }

        return $errors;
    }
}
