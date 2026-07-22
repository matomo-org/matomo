<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
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
    private const COMMON_CLASS = 'Piwik\\Common';
    private const SEND_HEADER_METHOD = 'sendheader';

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
        return self::collect($method->stmts ?? [], static function (Node $node) use ($scope): bool {
            return $node instanceof StaticCall && self::isJsonHeaderCall($node, $scope);
        });
    }

    /**
     * All Common::sendHeader('Content-Type: ...json...') calls anywhere within the method body.
     *
     * @return StaticCall[]
     */
    public static function findRawJsonContentTypeCalls(ClassMethod $method, Scope $scope): array
    {
        return self::collect($method->stmts ?? [], static function (Node $node) use ($scope): bool {
            return $node instanceof StaticCall && self::isRawJsonContentTypeCall($node, $scope);
        });
    }

    /**
     * Whether the method contains any JSON header call (Json::sendHeaderJSON() or a raw
     * Common::sendHeader('Content-Type: ...json...')), anywhere in its body.
     */
    public static function sendsJsonHeaderAnywhere(ClassMethod $method, Scope $scope): bool
    {
        return self::findAllJsonHeaderCalls($method, $scope) !== []
            || self::findRawJsonContentTypeCalls($method, $scope) !== [];
    }

    /**
     * Return statements that are unconditional top-level statements of the method body and whose
     * value looks like a JSON response (a json_encode() call, a (string) json_encode() cast, or a
     * JSON-ish string literal). The empty-string "no result" fallback is intentionally not matched.
     *
     * @return Return_[]
     */
    public static function findTopLevelJsonReturns(ClassMethod $method): array
    {
        $returns = [];

        foreach ($method->stmts ?? [] as $stmt) {
            if ($stmt instanceof Return_ && self::looksLikeJsonExpr($stmt->expr)) {
                $returns[] = $stmt;
            }
        }

        return $returns;
    }

    private static function looksLikeJsonExpr(?Node\Expr $expr): bool
    {
        if ($expr === null) {
            return false;
        }

        // unwrap a (string) cast, e.g. `return (string) json_encode(...)`
        if ($expr instanceof StringCast) {
            $expr = $expr->expr;
        }

        if (
            $expr instanceof FuncCall
            && $expr->name instanceof Name
            && strtolower($expr->name->toString()) === 'json_encode'
        ) {
            return true;
        }

        if ($expr instanceof String_) {
            $value = ltrim($expr->value);

            if ($value === '') {
                return false;
            }

            return (bool) preg_match('/^(\[|\{|"|true|false|null)/i', $value);
        }

        return false;
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
        return self::collect($method->stmts ?? [], static function (Node $node): bool {
            return $node instanceof Exit_;
        });
    }

    /**
     * Recursively collects the nodes matching $matcher, without descending into nested scopes
     * (closures, arrow functions, nested functions, anonymous classes): their code runs in a
     * separate frame and is not part of the analysed method's own control flow.
     *
     * @param Node[] $nodes
     * @param callable(Node): bool $matcher
     * @return Node[]
     */
    private static function collect(array $nodes, callable $matcher): array
    {
        $found = [];

        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }

            if (self::isNestedScopeBoundary($node)) {
                continue;
            }

            if ($matcher($node)) {
                $found[] = $node;
            }

            foreach ($node->getSubNodeNames() as $subNodeName) {
                $subNode = $node->{$subNodeName};
                $children = is_array($subNode) ? $subNode : [$subNode];
                $found = array_merge($found, self::collect($children, $matcher));
            }
        }

        return $found;
    }

    /**
     * Whether the node opens a nested scope (closure, arrow function, nested function or class-like)
     * whose body executes in a separate frame and must not be treated as the method's own flow.
     */
    private static function isNestedScopeBoundary(Node $node): bool
    {
        return $node instanceof FunctionLike || $node instanceof ClassLike;
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

    private static function isRawJsonContentTypeCall(StaticCall $call, Scope $scope): bool
    {
        if (!$call->class instanceof Name || !$call->name instanceof Identifier) {
            return false;
        }

        if (strtolower($call->name->toString()) !== self::SEND_HEADER_METHOD) {
            return false;
        }

        if ($scope->resolveName($call->class) !== self::COMMON_CLASS) {
            return false;
        }

        $args = $call->getArgs();

        if (!isset($args[0]) || !$args[0]->value instanceof String_) {
            return false;
        }

        return (bool) preg_match('#content-type\s*:.*json#i', $args[0]->value->value);
    }
}
