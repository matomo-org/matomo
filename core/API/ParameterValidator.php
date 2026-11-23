<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\API;

use InvalidArgumentException;
use Piwik\Piwik;
use Piwik\Validators\AcceptsArrayValues;
use Piwik\Validators\BaseValidator;
use ReflectionAttribute;
use ReflectionMethod;
use Throwable;

/**
 * Runs the validator attributes of API method parameters.
 *
 * Any {@see BaseValidator} that is marked as an attribute can be put on a parameter of an API method:
 *
 *     public function getUsers(#[NumberRange(0)] ?int $limit = null)
 *     {
 *         $this->checkParameters(__METHOD__, get_defined_vars());
 *         // ...
 *     }
 *
 * The method's first statement {@see \Piwik\Plugin\API::checkParameters()} runs the validators on the
 * values the method received. That covers every caller: HTTP requests and `Request::processRequest()`
 * (after the proxy has converted the request values to the declared types) as well as direct PHP calls.
 *
 * Validators only check the format of a value. Whether an entity exists or belongs to the caller stays in
 * the method, after its access check.
 *
 * @internal
 */
class ParameterValidator
{
    /**
     * @var array<string, array<string, array{validators: list<BaseValidator>, hasDefault: bool, default: mixed, sensitive: bool}>>
     */
    private $parameters = [];

    /**
     * Runs the validator attributes of the given method on its arguments. Parameters whose value is
     * `null` or identical to their declared default are skipped.
     *
     * @param string $method the method as `Class::method`, see `__METHOD__`
     * @param array<string, mixed> $arguments the method's arguments by name, see `get_defined_vars()`
     * @throws ParameterValidationException listing every parameter that failed
     */
    public function validate(
        string $method,
        #[\SensitiveParameter]
        array $arguments
    ): void {
        $violations = [];

        foreach ($this->getParameters($method) as $name => $parameter) {
            $value = $arguments[$name] ?? null;
            if ($value === null || ($parameter['hasDefault'] && $value === $parameter['default'])) {
                continue;
            }

            try {
                if (is_array($value) && !$this->acceptArrays($parameter['validators'])) {
                    throw new InvalidArgumentException(Piwik::translate('General_ValidatorErrorNotSingleValue'));
                }

                foreach ($parameter['validators'] as $validator) {
                    $validator->validate($value);
                }
            } catch (InvalidArgumentException $e) {
                // messages may contain the value, which a sensitive parameter must not reveal
                $message = $parameter['sensitive'] ? Piwik::translate('General_ValidatorErrorInvalidValue') : $e->getMessage();
                $violations[] = ['parameter' => $name, 'message' => $message];
            }
        }

        if (!empty($violations)) {
            throw new ParameterValidationException($violations);
        }
    }

    /**
     * Returns the validator attributes of each parameter with their arguments, for example
     * `NumberRange(1, max: 1000)`. Constants in the arguments appear with their values.
     *
     * @return array<string, list<string>>
     */
    public function describe(ReflectionMethod $method): array
    {
        $descriptions = [];
        foreach ($method->getParameters() as $parameter) {
            foreach ($parameter->getAttributes(BaseValidator::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $descriptions[$parameter->getName()][] = $this->describeAttribute($attribute);
            }
        }

        return $descriptions;
    }

    /**
     * Validator instances are built once per process and shared by all calls of the method.
     *
     * @return array<string, array{validators: list<BaseValidator>, hasDefault: bool, default: mixed, sensitive: bool}>
     */
    private function getParameters(string $method): array
    {
        if (!isset($this->parameters[$method])) {
            [$className, $methodName] = explode('::', $method, 2);

            $parameters = [];
            foreach ((new ReflectionMethod($className, $methodName))->getParameters() as $parameter) {
                $attributes = $parameter->getAttributes(BaseValidator::class, ReflectionAttribute::IS_INSTANCEOF);
                if (empty($attributes)) {
                    continue;
                }

                $hasDefault = $parameter->isDefaultValueAvailable();
                $parameters[$parameter->getName()] = [
                    'validators' => array_map(function (ReflectionAttribute $attribute): BaseValidator {
                        return $attribute->newInstance();
                    }, $attributes),
                    'hasDefault' => $hasDefault,
                    'default' => $hasDefault ? $parameter->getDefaultValue() : null,
                    'sensitive' => !empty($parameter->getAttributes(\SensitiveParameter::class)),
                ];
            }

            // only cached once complete, so a validator that fails to instantiate fails every call
            $this->parameters[$method] = $parameters;
        }

        return $this->parameters[$method];
    }

    /**
     * @param list<BaseValidator> $validators
     */
    private function acceptArrays(array $validators): bool
    {
        foreach ($validators as $validator) {
            if (!$validator instanceof AcceptsArrayValues) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ReflectionAttribute<BaseValidator> $attribute
     */
    private function describeAttribute(ReflectionAttribute $attribute): string
    {
        $shortName = $this->getShortClassName($attribute->getName());

        try {
            $arguments = [];
            foreach ($attribute->getArguments() as $name => $argument) {
                $arguments[] = (is_string($name) ? $name . ': ' : '') . $this->describeValue($argument);
            }
        } catch (Throwable $e) {
            // an argument that cannot be evaluated (e.g. a constant of a missing class) must not break the listing
            return $shortName;
        }

        return empty($arguments) ? $shortName : $shortName . '(' . implode(', ', $arguments) . ')';
    }

    /**
     * @param mixed $value
     */
    private function describeValue($value): string
    {
        if (is_array($value)) {
            return '[' . implode(', ', array_map([$this, 'describeValue'], $value)) . ']';
        }

        if (is_string($value)) {
            return "'" . $value . "'";
        }

        if (is_object($value)) {
            return $this->getShortClassName(get_class($value)) . '(...)';
        }

        return $value === null ? 'null' : var_export($value, true);
    }

    private function getShortClassName(string $className): string
    {
        return substr((string) strrchr('\\' . $className, '\\'), 1);
    }
}
