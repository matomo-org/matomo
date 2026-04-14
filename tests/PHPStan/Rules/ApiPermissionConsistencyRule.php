<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<ClassMethod>
 */
class ApiPermissionConsistencyRule implements Rule
{
    private const BODY_CHECK_METHODS = [
        'superuser' => 'checkUserHasSuperUserAccess',
        'notAnonymous' => 'checkUserIsNotAnonymous',
        'someView' => 'checkUserHasSomeViewAccess',
        'someWrite' => 'checkUserHasSomeWriteAccess',
        'someAdmin' => 'checkUserHasSomeAdminAccess',
        'view' => 'checkUserHasViewAccess',
        'write' => 'checkUserHasWriteAccess',
        'admin' => 'checkUserHasAdminAccess',
        'superUserOrUser' => 'checkUserHasSuperUserAccessOrIsTheUser',
    ];

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->isPublic() || $node->stmts === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (empty($classReflection) || !$classReflection->isSubclassOf(\Piwik\Plugin\API::class)) {
            return [];
        }

        $errors = [];
        $methodName = $node->name->toString();

        try {
            $docblockPermission = PermissionDeclaration::fromDocComment($node->getDocComment() ? $node->getDocComment()->getText() : null);
        } catch (\InvalidArgumentException $e) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'API method %s::%s has invalid @matomo-permission metadata: %s',
                    $classReflection->getName(),
                    $methodName,
                    $e->getMessage()
                ))->line($node->getLine())->build(),
            ];
        }

        try {
            $attributePermissions = $this->getAttributeDeclarations($node, $scope);
        } catch (\InvalidArgumentException $e) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'API method %s::%s has invalid #[Permission] metadata: %s',
                    $classReflection->getName(),
                    $methodName,
                    $e->getMessage()
                ))->line($node->getLine())->build(),
            ];
        }

        if ($docblockPermission === null && $attributePermissions === []) {
            return [];
        }

        if (count($attributePermissions) > 1) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'API method %s::%s declares multiple #[Permission] attributes; exactly one is allowed.',
                $classReflection->getName(),
                $methodName
            ))->line($node->getLine())->build();
        }

        $attributePermission = $attributePermissions[0] ?? null;

        if ($docblockPermission === null) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'API method %s::%s has a #[Permission] attribute but is missing a matching @matomo-permission docblock tag.',
                $classReflection->getName(),
                $methodName
            ))->line($node->getLine())->build();
        }

        if ($attributePermission === null) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'API method %s::%s has a @matomo-permission tag but is missing a matching #[Permission] attribute.',
                $classReflection->getName(),
                $methodName
            ))->line($node->getLine())->build();
        }

        if ($docblockPermission !== null && $attributePermission !== null && !$docblockPermission->equals($attributePermission)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'API method %s::%s has mismatched permission metadata: docblock declares %s but attribute declares %s.',
                $classReflection->getName(),
                $methodName,
                $docblockPermission->toReadableString(),
                $attributePermission->toReadableString()
            ))->line($node->getLine())->build();
        }

        $expectedPermission = $attributePermission ?? $docblockPermission;
        if ($expectedPermission === null) {
            return $errors;
        }

        $bodyCheckAnalysis = $this->analyzeBodyChecks($node, $expectedPermission);
        if ($bodyCheckAnalysis['matches']) {
            return $errors;
        }

        if ($bodyCheckAnalysis['firstMismatch'] === null) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'API method %s::%s declares %s but does not contain a matching direct Piwik::%s(...) permission check.',
                $classReflection->getName(),
                $methodName,
                $expectedPermission->toReadableString(),
                self::BODY_CHECK_METHODS[$expectedPermission->getName()]
            ))->line($node->getLine())->build();
            return $errors;
        }

        $firstCheck = $bodyCheckAnalysis['firstMismatch'];
        $actualPermission = $firstCheck['permission'];
        $actualMethod = $firstCheck['method'];
        $errors[] = RuleErrorBuilder::message(sprintf(
            'API method %s::%s declares %s but the direct body check is %s via Piwik::%s(...).',
            $classReflection->getName(),
            $methodName,
            $expectedPermission->toReadableString(),
            $actualPermission->toReadableString(),
            $actualMethod
        ))->line($firstCheck['line'])->build();

        return $errors;
    }

    /**
     * @param array<string, true> $visitedMethods
     * @return array{matches: bool, firstMismatch: array{permission: PermissionDeclaration, method: string, line: int}|null}
     */
    private function analyzeBodyChecks(ClassMethod $node, PermissionDeclaration $expectedPermission, array $visitedMethods = []): array
    {
        $methodName = $node->name->toString();
        if (isset($visitedMethods[$methodName])) {
            return ['matches' => false, 'firstMismatch' => null];
        }

        $visitedMethods[$methodName] = true;
        $bodyChecks = $this->findPermissionChecks($node->stmts ?? []);
        foreach ($bodyChecks as $bodyCheck) {
            if ($bodyCheck['permission']->equals($expectedPermission)) {
                return ['matches' => true, 'firstMismatch' => null];
            }
        }

        $firstMismatch = $bodyChecks[0] ?? null;

        foreach ($this->findDelegatedMethodNames($node->stmts ?? []) as $delegatedMethodName) {
            $delegatedMethod = $this->findSiblingMethod($node, $delegatedMethodName);
            if ($delegatedMethod === null) {
                continue;
            }

            $analysis = $this->analyzeBodyChecks($delegatedMethod, $expectedPermission, $visitedMethods);
            if ($analysis['matches']) {
                return $analysis;
            }

            if ($firstMismatch === null && $analysis['firstMismatch'] !== null) {
                $firstMismatch = $analysis['firstMismatch'];
            }
        }

        return ['matches' => false, 'firstMismatch' => $firstMismatch];
    }

    /**
     * @return PermissionDeclaration[]
     */
    private function getAttributeDeclarations(ClassMethod $node, Scope $scope): array
    {
        $permissions = [];
        $attributeLines = $this->getPermissionAttributeLines($node, $scope);

        foreach ($attributeLines as $attributeLine) {
            $permission = $this->getPermissionFromAttributeLine($attributeLine);
            if ($permission !== null) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * @return string[]
     */
    private function getPermissionAttributeLines(ClassMethod $node, Scope $scope): array
    {
        $file = $scope->getFile();
        if (!is_file($file)) {
            return [];
        }

        $source = file_get_contents($file);
        if ($source === false) {
            return [];
        }

        $tokens = token_get_all($source);
        $functionIndex = $this->findFunctionTokenIndex($tokens, $node->name->toString(), $node->getStartLine());
        if ($functionIndex === null) {
            return [];
        }

        $functionToken = $tokens[$functionIndex];
        if (!is_array($functionToken)) {
            return [];
        }

        return $this->collectAttributeLinesBeforeFunctionLine($source, $functionToken[2]);
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function findFunctionTokenIndex(array $tokens, string $methodName, int $startLine): ?int
    {
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; ++$i) {
            $token = $tokens[$i];
            if (!is_array($token) || $token[0] !== T_FUNCTION || $token[2] < $startLine) {
                continue;
            }

            for ($j = $i + 1; $j < $tokenCount; ++$j) {
                $nextToken = $tokens[$j];
                if (is_array($nextToken) && $nextToken[0] === T_STRING) {
                    if ($nextToken[1] === $methodName) {
                        return $i;
                    }

                    break;
                }

                if (is_string($nextToken) && $nextToken === '(') {
                    break;
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @return string[]
     */
    /**
     * @return string[]
     */
    private function collectAttributeLinesBeforeFunctionLine(string $source, int $functionLine): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $source);
        if ($lines === false) {
            return [];
        }

        $attributeLines = [];
        for ($lineIndex = $functionLine - 2; $lineIndex >= 0; --$lineIndex) {
            $line = trim($lines[$lineIndex]);
            if ($line === '') {
                continue;
            }

            if (strpos($line, '#[') === 0) {
                array_unshift($attributeLines, $line);
                continue;
            }

            break;
        }

        return $attributeLines;
    }

    private function getPermissionFromAttributeLine(string $attributeLine): ?PermissionDeclaration
    {
        if (!preg_match('/^\s*#\[\s*(?:\\\\?Piwik\\\\Attributes\\\\)?Permission\s*\((.*)\)\s*\]\s*$/', $attributeLine, $matches)) {
            return null;
        }

        preg_match_all('/([\'"])(.*?)\1/', $matches[1], $argumentMatches);
        $arguments = $argumentMatches[2] ?? [];
        if (count($arguments) === 0 || count($arguments) > 2) {
            throw new \InvalidArgumentException('Permission attribute arguments must be one or two string literals.');
        }

        $requirement = $arguments[0];
        $parameter = $arguments[1] ?? null;

        return PermissionDeclaration::fromAttribute($requirement, $parameter);
    }

    private function extractStringValue(Expr $expr): ?string
    {
        if (!$expr instanceof Node\Scalar\String_) {
            return null;
        }

        return $expr->value;
    }

    /**
     * @param Node[] $nodes
     * @return array<int, array{permission: PermissionDeclaration, method: string, line: int}>
     */
    private function findPermissionChecks(array $nodes): array
    {
        $result = [];

        foreach ($nodes as $node) {
            if ($node instanceof StaticCall) {
                $bodyCheck = $this->getPermissionCheckFromStaticCall($node);
                if ($bodyCheck !== null) {
                    $result[] = $bodyCheck;
                }
            }

            foreach ($node->getSubNodeNames() as $subNodeName) {
                $subNode = $node->$subNodeName;
                if ($subNode instanceof Node) {
                    $result = array_merge($result, $this->findPermissionChecks([$subNode]));
                } elseif (is_array($subNode)) {
                    $childNodes = [];
                    foreach ($subNode as $childNode) {
                        if ($childNode instanceof Node) {
                            $childNodes[] = $childNode;
                        }
                    }

                    if (!empty($childNodes)) {
                        $result = array_merge($result, $this->findPermissionChecks($childNodes));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param Node[] $nodes
     * @return string[]
     */
    private function findDelegatedMethodNames(array $nodes): array
    {
        $result = [];

        foreach ($nodes as $node) {
            if (
                $node instanceof MethodCall
                && $node->var instanceof Variable
                && $node->var->name === 'this'
                && $node->name instanceof Identifier
            ) {
                $result[] = $node->name->toString();
            }

            foreach ($node->getSubNodeNames() as $subNodeName) {
                $subNode = $node->$subNodeName;
                if ($subNode instanceof Node) {
                    $result = array_merge($result, $this->findDelegatedMethodNames([$subNode]));
                } elseif (is_array($subNode)) {
                    $childNodes = [];
                    foreach ($subNode as $childNode) {
                        if ($childNode instanceof Node) {
                            $childNodes[] = $childNode;
                        }
                    }

                    if (!empty($childNodes)) {
                        $result = array_merge($result, $this->findDelegatedMethodNames($childNodes));
                    }
                }
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @return array{permission: PermissionDeclaration, method: string, line: int}|null
     */
    private function getPermissionCheckFromStaticCall(StaticCall $node): ?array
    {
        if (!$node->class instanceof Name || !$node->name instanceof Identifier) {
            return null;
        }

        $className = ltrim($node->class->toString(), '\\');
        if ($className !== 'Piwik\\Piwik' && $className !== 'Piwik') {
            return null;
        }

        $methodName = $node->name->toString();
        $permissionName = array_search($methodName, self::BODY_CHECK_METHODS, true);
        if ($permissionName === false) {
            return null;
        }

        $parameter = null;
        if (isset($node->args[0])) {
            $parameter = $this->extractVariableName($node->args[0]->value);
            if ($parameter === null) {
                return null;
            }
        }

        return [
            'permission' => PermissionDeclaration::fromBodyCheck($permissionName, $parameter),
            'method' => $methodName,
            'line' => $node->getLine(),
        ];
    }

    private function extractVariableName(Expr $expr): ?string
    {
        if (!$expr instanceof Variable || !is_string($expr->name)) {
            return null;
        }

        return $expr->name;
    }

    private function findSiblingMethod(ClassMethod $node, string $methodName): ?ClassMethod
    {
        $parent = $node->getAttribute('parent');
        if (!$parent instanceof Class_) {
            return null;
        }

        foreach ($parent->stmts as $statement) {
            if ($statement instanceof ClassMethod && $statement->name->toString() === $methodName) {
                return $statement;
            }
        }

        return null;
    }
}
