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
 * A controller action that sends the JSON header unconditionally must use the #[JsonResponse]
 * attribute instead of calling Json::sendHeaderJSON() manually, so that the JSON Content-Type is
 * (re-)applied after the action body ran and cannot be overwritten by later output.
 *
 * @implements Rule<ClassMethod>
 */
class MissingJsonResponseAttributeRule implements Rule
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

        // Methods that exit at top level flush their own response, so nothing can overwrite the
        // header they set; such an echo+exit action manages its own Content-Type.
        if (JsonResponseRuleHelper::hasTopLevelExit($node)) {
            return [];
        }

        $calls = JsonResponseRuleHelper::findTopLevelJsonHeaderCalls($node, $scope);

        if ($calls === []) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Controller action %s() calls Json::sendHeaderJSON() unconditionally; add the'
                . ' #[\\Piwik\\Http\\JsonResponse] attribute to the method and remove the manual call so'
                . ' the JSON Content-Type cannot be overwritten by later output.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.missingAttribute')
                ->line($calls[0]->getStartLine())
                ->build(),
        ];
    }
}
