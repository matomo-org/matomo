<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use DI as PHPDI;

/**
 * Proxy class for using DI related methods
 *
 * @api
 */
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
     * @see PHPDI\create()
     */
    public static function create(?string $className = null)
    {
        return PHPDI\create($className);
    }

    /**
     * @param string|null $className
     * @return \DI\Definition\Helper\AutowireDefinitionHelper
     * @see PHPDI\autowire()
     */
    public static function autowire(?string $className = null)
    {
        return PHPDI\autowire($className);
    }

    /**
     * @param callable $factory
     * @return \DI\Definition\Helper\FactoryDefinitionHelper
     * @see PHPDI\factory()
     */
    public static function factory($factory)
    {
        return PHPDI\factory($factory);
    }

    /**
     * @param callable $callable
     * @return \DI\Definition\Helper\FactoryDefinitionHelper
     * @see PHPDI\decorate()
     */
    public static function decorate($callable)
    {
        return PHPDI\decorate($callable);
    }

    /**
     * @param string $entryName
     * @return \DI\Definition\Reference
     * @see PHPDI\get()
     */
    public static function get(string $entryName)
    {
        return PHPDI\get($entryName);
    }

    /**
     * @param string $variableName
     * @param mixed  $defaultValue
     * @return \DI\Definition\EnvironmentVariableDefinition
     * @see PHPDI\env()
     */
    public static function env(string $variableName, $defaultValue = null)
    {
        return PHPDI\env($variableName, $defaultValue);
    }

    /**
     * @param array|mixed $values
     * @return \DI\Definition\ArrayDefinitionExtension
     * @see PHPDI\add()
     */
    public static function add($values)
    {
        return PHPDI\add($values);
    }

    /**
     * @param string $expression
     * @return \DI\Definition\StringDefinition
     * @see PHPDI\string()
     */
    public static function string(string $expression)
    {
        return PHPDI\string($expression);
    }
}
