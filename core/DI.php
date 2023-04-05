<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use DI as PHPDI;

class DI
{
    /**
     * @param mixed $value
     * @return \DI\Definition\ValueDefinition
     */
    public static function value($value)
    {
        return PHPDI\value($value);
    }

    /**
     * @param string|null $className
     * @return \DI\Definition\Helper\CreateDefinitionHelper
     */
    public static function create(string $className = null)
    {
        return PHPDI\create($className);
    }

    /**
     * @param string|null $className
     * @return \DI\Definition\Helper\AutowireDefinitionHelper
     */
    public static function autowire(string $className = null)
    {
        return PHPDI\autowire($className);
    }

    /**
     * @param callable $factory
     * @return \DI\Definition\Helper\FactoryDefinitionHelper
     */
    public static function factory($factory)
    {
        return PHPDI\factory($factory);
    }

    /**
     * @param callable $callable
     * @return \DI\Definition\Helper\FactoryDefinitionHelper
     */
    public static function decorate($callable)
    {
        return PHPDI\decorate($callable);
    }

    /**
     * @param string $entryName
     * @return \DI\Definition\Reference
     */
    public static function get(string $entryName)
    {
        return PHPDI\get($entryName);
    }

    /**
     * @param string $variableName
     * @param mixed  $defaultValue
     * @return \DI\Definition\EnvironmentVariableDefinition
     */
    public static function env(string $variableName, $defaultValue = null)
    {
        return PHPDI\env($variableName, $defaultValue);
    }

    /**
     * @param array|mixed $values
     * @return \DI\Definition\ArrayDefinitionExtension
     */
    public static function add($values)
    {
        return PHPDI\add($values);
    }

    /**
     * @param string $expression
     * @return \DI\Definition\StringDefinition
     */
    public static function string(string $expression)
    {
        return PHPDI\string($expression);
    }
}
