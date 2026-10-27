<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A public API method whose parameters carry validator attributes must start with
 * `$this->checkParameters(__METHOD__, get_defined_vars());`. PHP ignores attributes at call time, so
 * without that first statement the validators never run, neither for HTTP requests nor for direct PHP
 * calls.
 *
 * @implements Rule<ClassMethod>
 */
class ApiParameterValidationRule implements Rule
{
    private const API_CLASS = 'Piwik\\Plugin\\API';
    private const BASE_VALIDATOR_CLASS = 'Piwik\\Validators\\BaseValidator';

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if (
            $classReflection === null
            || !$node->isPublic()
            || $node->isStatic()
            || $node->isAbstract()
            || !$classReflection->isSubclassOf(self::API_CLASS)
            || !$this->hasValidatorAttribute($node, $scope)
            || $this->startsWithSelfCheck($node)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'API method %s() has parameter validator attributes, so its first statement must be'
                . ' $this->checkParameters(__METHOD__, get_defined_vars()); otherwise the validators never run.',
                $node->name->toString()
            ))
                ->identifier('matomo.apiValidation.missingSelfCheck')
                ->line($node->getStartLine())
                ->build(),
        ];
    }

    private function hasValidatorAttribute(ClassMethod $method, Scope $scope): bool
    {
        foreach ($method->params as $param) {
            foreach ($param->attrGroups as $attrGroup) {
                foreach ($attrGroup->attrs as $attr) {
                    $name = $scope->resolveName($attr->name);

                    if (
                        $this->reflectionProvider->hasClass($name)
                        && $this->reflectionProvider->getClass($name)->isSubclassOf(self::BASE_VALIDATOR_CLASS)
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function startsWithSelfCheck(ClassMethod $method): bool
    {
        $first = $method->stmts[0] ?? null;

        if (!$first instanceof Expression || !$first->expr instanceof MethodCall) {
            return false;
        }

        $call = $first->expr;
        $args = $call->getArgs();

        return $call->var instanceof Variable
            && $call->var->name === 'this'
            && $call->name instanceof Identifier
            && $call->name->toLowerString() === 'checkparameters'
            && count($args) === 2
            && $args[0]->value instanceof Method
            && $args[1]->value instanceof FuncCall
            && $args[1]->value->name instanceof Name
            && $args[1]->value->name->toLowerString() === 'get_defined_vars'
            && $args[1]->value->getArgs() === [];
    }
}
