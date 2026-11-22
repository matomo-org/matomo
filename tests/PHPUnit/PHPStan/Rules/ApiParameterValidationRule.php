<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

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
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
use Piwik\Validators\BaseValidator;

/**
 * A public API method whose parameters carry validator attributes must start with
 * `$this->checkParameters(__METHOD__, get_defined_vars());`. PHP ignores attributes at call time, so
 * without that first statement the validators never run, neither for HTTP requests nor for direct PHP
 * calls.
 *
 * A parameter's declared default must also pass its validators. It is not validated at runtime, so a
 * default the validators reject points to a mistake in either.
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
            || !$classReflection->isSubclassOf(self::API_CLASS)
            || !$this->hasValidatorAttribute($node, $scope)
        ) {
            return [];
        }

        $errors = [];

        if (!$node->isAbstract() && !$this->startsWithSelfCheck($node)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'API method %s() has parameter validator attributes, so its first statement must be'
                . ' $this->checkParameters(__METHOD__, get_defined_vars()); otherwise the validators never run.',
                $node->name->toString()
            ))
                ->identifier('matomo.apiValidation.missingSelfCheck')
                ->line($node->getStartLine())
                ->build();
        }

        foreach ($this->getRejectedDefaults($classReflection, $node->name->toString()) as $parameterName) {
            $errors[] = $this->buildRejectedDefaultError($node, $parameterName);
        }

        return $errors;
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

    /**
     * Runs each parameter's validators on its declared default. Defaults and validator arguments that are
     * not constant are skipped.
     *
     * @return list<string> names of the parameters whose default fails
     */
    private function getRejectedDefaults(ClassReflection $classReflection, string $methodName): array
    {
        if (!$classReflection->hasNativeMethod($methodName)) {
            return [];
        }

        $rejected = [];
        foreach ($classReflection->getNativeMethod($methodName)->getOnlyVariant()->getParameters() as $parameter) {
            $defaultType = $parameter->getDefaultValue();
            if ($defaultType === null) {
                continue;
            }

            [$hasDefault, $default] = $this->toValue($defaultType);
            if (!$hasDefault || $default === null) {
                continue;
            }

            foreach ($parameter->getAttributes() as $attribute) {
                $validatorClass = $attribute->getName();
                if (
                    !$this->reflectionProvider->hasClass($validatorClass)
                    || !$this->reflectionProvider->getClass($validatorClass)->isSubclassOf(self::BASE_VALIDATOR_CLASS)
                ) {
                    continue;
                }

                $arguments = [];
                foreach ($attribute->getArgumentTypes() as $name => $argumentType) {
                    [$isConstant, $arguments[$name]] = $this->toValue($argumentType);
                    if (!$isConstant) {
                        continue 2;
                    }
                }

                try {
                    $validator = new $validatorClass(...$arguments);
                    if ($validator instanceof BaseValidator) {
                        $validator->validate($default);
                    }
                } catch (\Throwable $e) {
                    // failing validators may not even manage to build their message outside of Matomo
                    $rejected[] = $parameter->getName();
                    continue 2;
                }
            }
        }

        return $rejected;
    }

    /**
     * @return array{bool, mixed} whether the type is a single constant value, and that value
     */
    private function toValue(Type $type): array
    {
        $scalars = $type->getConstantScalarValues();
        if (count($scalars) === 1) {
            return [true, $scalars[0]];
        }

        $arrays = $type->getConstantArrays();
        if (count($arrays) !== 1) {
            return [false, null];
        }

        $value = [];
        $valueTypes = $arrays[0]->getValueTypes();
        foreach ($arrays[0]->getKeyTypes() as $i => $keyType) {
            [$isConstant, $item] = $this->toValue($valueTypes[$i]);
            if (!$isConstant) {
                return [false, null];
            }

            $value[$keyType->getValue()] = $item;
        }

        return [true, $value];
    }

    private function buildRejectedDefaultError(ClassMethod $method, string $parameterName): IdentifierRuleError
    {
        $line = $method->getStartLine();
        foreach ($method->params as $param) {
            if ($param->var instanceof Variable && $param->var->name === $parameterName) {
                $line = $param->getStartLine();
            }
        }

        return RuleErrorBuilder::message(sprintf(
            'The default value of parameter $%s of API method %s() does not pass its validator attributes.',
            $parameterName,
            $method->name->toString()
        ))
            ->identifier('matomo.apiValidation.rejectedDefault')
            ->line($line)
            ->build();
    }
}
