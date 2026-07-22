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
 * A controller action must not set a JSON Content-Type directly via Common::sendHeader(). Doing so
 * bypasses the JSON response convention (and the checks that enforce it): use Json::sendHeaderJSON()
 * for a conditional JSON branch, or the #[JsonResponse] attribute for an always-JSON action.
 *
 * @implements Rule<ClassMethod>
 */
class NoRawJsonHeaderInControllerRule implements Rule
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

        $errors = [];

        foreach (JsonResponseRuleHelper::findRawJsonContentTypeCalls($node, $scope) as $call) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Controller action %s() sets a JSON Content-Type via Common::sendHeader(); use'
                . ' Json::sendHeaderJSON() (or the #[\\Piwik\\Http\\JsonResponse] attribute for an'
                . ' always-JSON action) instead, so the JSON response convention and its checks apply.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.rawHeader')
                ->line($call->getStartLine())
                ->build();
        }

        return $errors;
    }
}
