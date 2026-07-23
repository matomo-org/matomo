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
        if (!JsonResponseRuleHelper::isPublicControllerAction($node, $scope)) {
            return [];
        }

        $rawCalls = JsonResponseRuleHelper::findRawJsonContentTypeCalls($node, $scope);

        if ($rawCalls === []) {
            return [];
        }

        $hasAttribute = JsonResponseRuleHelper::hasJsonResponseAttribute($node, $scope);
        $methodName = $node->name->toString();

        $errors = [];

        foreach ($rawCalls as $call) {
            $message = $hasAttribute
                ? sprintf(
                    'Controller action %s() is marked #[\\Piwik\\Http\\JsonResponse], which already'
                    . ' sends the JSON header; remove this redundant Common::sendHeader() call.',
                    $methodName
                )
                : sprintf(
                    'Controller action %s() sets a JSON Content-Type via Common::sendHeader(). Mark the'
                    . ' action with the #[\\Piwik\\Http\\JsonResponse] attribute instead of setting the'
                    . ' header directly (use Json::sendHeaderJSON() only for a conditional JSON branch'
                    . ' that cannot use the attribute).',
                    $methodName
                );

            $errors[] = RuleErrorBuilder::message($message)
                ->identifier('matomo.jsonResponse.rawHeader')
                ->line($call->getStartLine())
                ->build();
        }

        return $errors;
    }
}
