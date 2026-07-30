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
 * A controller action that returns a JSON response on any path must declare the #[JsonResponse]
 * attribute. The attribute is the single supported way to serve JSON from a controller: it lets
 * Matomo apply the JSON Content-Type after the action runs, so it cannot be overwritten by later
 * output, and setting the header directly is discouraged.
 *
 * Because the attribute forces the JSON Content-Type on every path, an action may not mix a JSON
 * return with an HTML or redirect one; a method that needs both must be split into separate actions.
 * Actions that already set the header via a rule-reported call are skipped here to avoid
 * double-reporting.
 *
 * @implements Rule<ClassMethod>
 */
class JsonReturnRequiresAttributeRule implements Rule
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

        // Actions whose JSON header is already reported by a header-focused rule are skipped to
        // avoid double-reporting; a merely conditional header is not covered there, so this rule
        // still catches "returns JSON but only sets the header conditionally, if at all".
        if (JsonResponseRuleHelper::jsonHeaderIsReportedByHeaderRules($node, $scope)) {
            return [];
        }

        $return = JsonResponseRuleHelper::firstJsonReturn($node);

        if ($return === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Controller action %s() returns a JSON response but is not marked'
                . ' #[\\Piwik\\Http\\JsonResponse]. Add the attribute so Matomo applies the JSON'
                . ' Content-Type after the action; do not send the header manually.',
                $node->name->toString()
            ))
                ->identifier('matomo.jsonResponse.undeclaredJsonReturn')
                ->line($return->getStartLine())
                ->build(),
        ];
    }
}
