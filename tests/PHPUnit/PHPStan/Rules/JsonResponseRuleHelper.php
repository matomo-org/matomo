<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;

/**
 * Shared helpers for the #[JsonResponse] PHPStan rules.
 */
final class JsonResponseRuleHelper
{
    public const ATTRIBUTE_CLASS = 'Piwik\\Http\\JsonResponse';
    public const CONTROLLER_CLASS = 'Piwik\\Plugin\\Controller';

    private const JSON_RENDERER_CLASS = 'Piwik\\DataTable\\Renderer\\Json';
    private const JSON_HEADER_METHOD = 'sendheaderjson';

    /**
     * Whether the analysed method lives in a Piwik\Plugin\Controller subclass.
     */
    public static function isControllerScope(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }

        $classReflection = $scope->getClassReflection();

        return $classReflection !== null
            && in_array(self::CONTROLLER_CLASS, $classReflection->getParentClassesNames(), true);
    }

    public static function hasJsonResponseAttribute(ClassMethod $method, Scope $scope): bool
    {
        foreach ($method->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($scope->resolveName($attr->name) === self::ATTRIBUTE_CLASS) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Json::sendHeaderJSON() calls that are unconditional top-level statements of the method body.
     *
     * @return StaticCall[]
     */
    public static function findTopLevelJsonHeaderCalls(ClassMethod $method, Scope $scope): array
    {
        $calls = [];

        foreach ($method->stmts ?? [] as $stmt) {
            if (
                $stmt instanceof Expression
                && $stmt->expr instanceof StaticCall
                && self::isJsonHeaderCall($stmt->expr, $scope)
            ) {
                $calls[] = $stmt->expr;
            }
        }

        return $calls;
    }

    /**
     * All Json::sendHeaderJSON() calls anywhere within the method body.
     *
     * @return StaticCall[]
     */
    public static function findAllJsonHeaderCalls(ClassMethod $method, Scope $scope): array
    {
        return self::collectJsonHeaderCalls($method->stmts ?? [], $scope);
    }

    /**
     * Whether the method has a top-level exit/die statement. Such methods emit and flush their own
     * response, so the Content-Type they set can no longer be overwritten by later output.
     */
    public static function hasTopLevelExit(ClassMethod $method): bool
    {
        foreach ($method->stmts ?? [] as $stmt) {
            if ($stmt instanceof Expression && $stmt->expr instanceof Exit_) {
                return true;
            }
        }

        return false;
    }

    /**
     * All exit/die statements anywhere within the method body.
     *
     * @return Exit_[]
     */
    public static function findExits(ClassMethod $method): array
    {
        return self::collectExits($method->stmts ?? []);
    }

    /**
     * @param Node[] $nodes
     * @return Exit_[]
     */
    private static function collectExits(array $nodes): array
    {
        $exits = [];

        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }

            if ($node instanceof Exit_) {
                $exits[] = $node;
            }

            foreach ($node->getSubNodeNames() as $subNodeName) {
                $subNode = $node->{$subNodeName};
                $children = is_array($subNode) ? $subNode : [$subNode];
                $exits = array_merge($exits, self::collectExits($children));
            }
        }

        return $exits;
    }

    /**
     * @param Node[] $nodes
     * @return StaticCall[]
     */
    private static function collectJsonHeaderCalls(array $nodes, Scope $scope): array
    {
        $calls = [];

        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }

            if ($node instanceof StaticCall && self::isJsonHeaderCall($node, $scope)) {
                $calls[] = $node;
            }

            foreach ($node->getSubNodeNames() as $subNodeName) {
                $subNode = $node->{$subNodeName};
                $children = is_array($subNode) ? $subNode : [$subNode];
                $calls = array_merge($calls, self::collectJsonHeaderCalls($children, $scope));
            }
        }

        return $calls;
    }

    private static function isJsonHeaderCall(StaticCall $call, Scope $scope): bool
    {
        if (!$call->class instanceof Name || !$call->name instanceof Identifier) {
            return false;
        }

        if (strtolower($call->name->toString()) !== self::JSON_HEADER_METHOD) {
            return false;
        }

        return $scope->resolveName($call->class) === self::JSON_RENDERER_CLASS;
    }
}
