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
 * A controller action carrying #[JsonResponse] must not exit/die: the JSON header is (re-)applied by
 * FrontController only after the action returns, so an exit bypasses that handling entirely and the
 * attribute silently has no effect. Such actions should return the JSON string instead (or, if they
 * really must exit, drop the attribute and send the header manually).
 *
 * @implements Rule<ClassMethod>
 */
class JsonResponseMustNotExitRule implements Rule
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

        foreach (JsonResponseRuleHelper::findExits($node) as $exit) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Controller action %s() is marked #[\\Piwik\\Http\\JsonResponse] but calls exit/die;'
                . ' this bypasses the JSON header handling in FrontController, so the attribute has no'
                . ' effect. Return the JSON string instead, or drop the attribute and send the header'
                . ' manually.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.exit')
                ->line($exit->getStartLine())
                ->build();
        }

        return $errors;
    }
}
