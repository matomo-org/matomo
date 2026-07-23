<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;

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
    private const OUTPUT_FUNCTIONS = [
        'flush', 'ob_flush', 'ob_end_flush', 'printf', 'vprintf', 'readfile', 'fpassthru', 'passthru',
    ];

    /**
     * Whether the analysed method lives in a Piwik\Plugin\Controller subclass.
     */
    public static function isControllerScope(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }

        return in_array(
            self::CONTROLLER_CLASS,
            $scope->getClassReflection()->getParentClassesNames(),
            true
        );
    }

    /**
     * Whether the method is a dispatchable controller action, i.e. a public method of a
     * Piwik\Plugin\Controller subclass. Only such methods can carry (and be dispatched with) the
     * attribute, so the "you must declare the action" rules only apply to them.
     */
    public static function isPublicControllerAction(ClassMethod $method, Scope $scope): bool
    {
        return $method->isPublic() && self::isControllerScope($scope);
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
     * Whether the method overrides an ancestor declaration that carries #[JsonResponse]. PHP does not
     * inherit method attributes and FrontController only honours the attribute declared directly on
     * the dispatched method, so an override of a JSON action must re-declare the attribute; this lets
     * a rule flag an override that forgot to.
     */
    public static function overridesAttributedAction(ClassMethod $method, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return false;
        }

        $methodName = $method->name->toString();
        $parent = $classReflection->getParentClass();

        while ($parent !== null) {
            if ($parent->hasNativeMethod($methodName) && self::parentDeclaresAttribute($parent, $methodName)) {
                return true;
            }

            $parent = $parent->getParentClass();
        }

        return false;
    }

    private static function parentDeclaresAttribute(ClassReflection $parent, string $methodName): bool
    {
        // Native reflection is used deliberately: PHPStan 2.x exposes no API to read a method's PHP
        // attributes, and it analyses one class at a time so the parent's AST is not available here.
        // Any failure (e.g. a parent whose load-time dependencies error) degrades to "no attribute".
        try {
            $native = $parent->getNativeReflection()->getMethod($methodName);
        } catch (\Throwable $e) {
            return false;
        }

        // only the class that actually declares the method here should count its attributes
        return $native->getDeclaringClass()->getName() === $parent->getName()
            && count($native->getAttributes(self::ATTRIBUTE_CLASS)) > 0;
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
     * @return Node[] the matched StaticCall nodes
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
     * @return Node[] the matched StaticCall nodes
     */
    public static function findRawJsonContentTypeCalls(ClassMethod $method, Scope $scope): array
    {
        return self::collect($method->stmts ?? [], static function (Node $node) use ($scope): bool {
            return $node instanceof StaticCall && self::isRawJsonContentTypeCall($node, $scope);
        });
    }

    /**
     * Whether a JSON header call in this method is reported by one of the header-focused rules: a
     * top-level Json::sendHeaderJSON() (MissingJsonResponseAttributeRule, which however exempts a
     * method with a top-level exit) or any raw Common::sendHeader('...json...')
     * (NoRawJsonHeaderInControllerRule). Used to avoid double-reporting the same method; a merely
     * conditional Json::sendHeaderJSON() is NOT covered (neither rule fires on it), so it is
     * intentionally excluded here.
     */
    public static function jsonHeaderIsReportedByHeaderRules(ClassMethod $method, Scope $scope): bool
    {
        return self::findTopLevelJsonHeaderCalls($method, $scope) !== []
            || self::findRawJsonContentTypeCalls($method, $scope) !== [];
    }

    /**
     * The first return statement in the method's own flow whose value is JSON (a json_encode() call,
     * a (string) json_encode() cast, or a JSON literal), or null if there is none. Returns inside
     * nested closures are excluded (they return from the closure, not the method).
     *
     * A controller action may not mix a JSON return with a non-JSON one: an action that returns JSON
     * on any path must be an always-JSON action carrying #[JsonResponse]. If it also needs to return
     * HTML or redirect on another path, it must be split into separate actions.
     */
    public static function firstJsonReturn(ClassMethod $method): ?Return_
    {
        $returns = self::collect($method->stmts ?? [], static function (Node $node): bool {
            return $node instanceof Return_;
        });

        foreach ($returns as $return) {
            /** @var Return_ $return */
            if (self::looksLikeJsonExpr($return->expr)) {
                return $return;
            }
        }

        return null;
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
     * All exit/die statements in the method's own flow.
     *
     * @return Node[] the matched Exit_ nodes
     */
    public static function findExits(ClassMethod $method): array
    {
        return self::collect($method->stmts ?? [], static function (Node $node): bool {
            return $node instanceof Exit_;
        });
    }

    /**
     * Output-producing statements (echo, print, and flush()/ob_flush()/ob_end_flush()) in the
     * method's own flow. Emitting output before the action returns commits the response headers, so
     * the JSON Content-Type could no longer be applied.
     *
     * @return Node[]
     */
    public static function findOutputStatements(ClassMethod $method): array
    {
        return self::collect($method->stmts ?? [], static function (Node $node): bool {
            if ($node instanceof Echo_ || $node instanceof Print_) {
                return true;
            }

            return $node instanceof FuncCall
                && $node->name instanceof Name
                && in_array(strtolower($node->name->toString()), self::OUTPUT_FUNCTIONS, true);
        });
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

        // a ternary/short-ternary whose result operand is JSON, e.g. `$c ? json_encode($a) : ...`
        // or `json_encode($x) ?: '[]'`
        if ($expr instanceof Ternary) {
            return self::looksLikeJsonExpr($expr->if ?? $expr->cond)
                || self::looksLikeJsonExpr($expr->else);
        }

        // a null-coalesce whose either side is JSON, e.g. `json_encode($x) ?? '[]'`
        if ($expr instanceof Coalesce) {
            return self::looksLikeJsonExpr($expr->left) || self::looksLikeJsonExpr($expr->right);
        }

        if ($expr instanceof String_) {
            return self::looksLikeJsonLiteral($expr->value);
        }

        return false;
    }

    private static function looksLikeJsonLiteral(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        // structural JSON (object/array/string): validate by decoding to avoid matching plain text
        // such as '[Deprecated] ...' or a stray '{'.
        if (preg_match('/^[\[{"]/', $value)) {
            json_decode($value);

            return json_last_error() === JSON_ERROR_NONE;
        }

        // bare JSON keyword literals only (numbers are intentionally not matched: a plain '0'/'1' is
        // more often a flag than a JSON response).
        return in_array(strtolower($value), ['true', 'false', 'null'], true);
    }

    /**
     * Recursively collects the nodes matching $matcher within the method's own flow. Nested scopes
     * (closures, arrow functions, nested functions, anonymous classes) are not traversed, since their
     * code runs in a separate frame.
     *
     * @param mixed[] $nodes sub-node values, which may include non-Node scalars/arrays
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

        // anchored to a real Content-Type header start so X-Content-Type etc. do not match
        // match a real JSON media type (application/json or application/<subtype>+json), so headers
        // such as "application/notjson" or "text/plain; profile=json" are not treated as JSON
        return (bool) preg_match(
            '#^\s*content-type\s*:\s*application/(?:[\w.+-]+\+)?json\b#i',
            $args[0]->value->value
        );
    }
}
