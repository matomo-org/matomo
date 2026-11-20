<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use InvalidArgumentException;
use Piwik\Piwik;
use Piwik\Validators\AcceptsArrayValues;
use Piwik\Validators\BaseValidator;
use ReflectionAttribute;
use ReflectionMethod;

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
     * @var array<string, array<string, list<BaseValidator>>>
     */
    private $validators = [];

    /**
     * Runs the validator attributes of the given method on its arguments. Parameters whose value is
     * `null` are skipped.
     *
     * @param string $method the method as `Class::method`, see `__METHOD__`
     * @param array<string, mixed> $arguments the method's arguments by name, see `get_defined_vars()`
     * @throws ParameterValidationException listing every parameter that failed
     */
    public function validate(string $method, array $arguments): void
    {
        $violations = [];

        foreach ($this->getValidators($method) as $name => $validators) {
            $value = $arguments[$name] ?? null;
            if ($value === null) {
                continue;
            }

            try {
                if (is_array($value) && !$this->acceptArrays($validators)) {
                    throw new InvalidArgumentException(Piwik::translate('General_ValidatorErrorNotSingleValue'));
                }

                foreach ($validators as $validator) {
                    $validator->validate($value);
                }
            } catch (InvalidArgumentException $e) {
                $violations[] = ['parameter' => $name, 'message' => $e->getMessage()];
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
     * @return array<string, list<BaseValidator>>
     */
    private function getValidators(string $method): array
    {
        if (!isset($this->validators[$method])) {
            $validators = [];
            foreach ((new ReflectionMethod($method))->getParameters() as $parameter) {
                foreach ($parameter->getAttributes(BaseValidator::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                    $validators[$parameter->getName()][] = $attribute->newInstance();
                }
            }

            // only cached once complete, so a validator that fails to instantiate fails every call
            $this->validators[$method] = $validators;
        }

        return $this->validators[$method];
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

    private function describeAttribute(ReflectionAttribute $attribute): string
    {
        $arguments = [];
        foreach ($attribute->getArguments() as $name => $argument) {
            $arguments[] = (is_string($name) ? $name . ': ' : '') . $this->describeValue($argument);
        }

        $shortName = $this->getShortClassName($attribute->getName());

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
