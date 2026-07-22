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
 * A controller action that unconditionally returns a JSON response must declare the #[JsonResponse]
 * attribute rather than serving JSON without it. The attribute is the single supported way to serve
 * JSON from a controller: it lets Matomo apply the JSON Content-Type after the action runs, so it
 * cannot be overwritten by later output. Setting the header directly is discouraged.
 *
 * Actions that only return JSON conditionally (returning HTML or redirecting on other paths) cannot
 * use the always-on attribute; their JSON return is nested rather than top-level, so it is not
 * matched here and they keep sending the header manually in that branch.
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
        if (!$node instanceof ClassMethod) {
            return [];
        }

        if (!JsonResponseRuleHelper::isPublicControllerAction($node, $scope)) {
            return [];
        }

        if (JsonResponseRuleHelper::hasJsonResponseAttribute($node, $scope)) {
            return [];
        }

        // Actions whose JSON header is already reported by a header-focused rule are skipped to
        // avoid double-reporting; a merely conditional header is not covered there, so this rule
        // still catches "returns JSON unconditionally but only sets the header conditionally".
        if (JsonResponseRuleHelper::jsonHeaderIsReportedByHeaderRules($node, $scope)) {
            return [];
        }

        $return = JsonResponseRuleHelper::unconditionalJsonReturn($node);

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
