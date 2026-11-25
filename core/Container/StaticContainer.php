<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use Piwik\Container\Container;
use Piwik\Exception\DI\DependencyException;
use Piwik\Exception\DI\NotFoundException;

/**
 * Static access to the DI container.
 *
 * Prefer constructor injection where core creates the class through the container (controllers, API classes,
 * Tasks, RecordBuilders).
 */
class StaticContainer
{
    /**
     * @var Container[]
     */
    private static array $containerStack = array();

    /**
     * Definitions to register in the container.
     *
     * @var array[]
     */
    private static array $definitions = array();

    /**
     * @return Container
     * @internal
     */
    public static function getContainer()
    {
        if (empty(self::$containerStack)) {
            throw new ContainerDoesNotExistException("The root container has not been created yet.");
        }

        return end(self::$containerStack);
    }

    /**
     * @internal
     */
    public static function clearContainer()
    {
        self::pop();
    }

    /**
     * Only use this in tests.
     *
     * @internal
     */
    public static function push(Container $container)
    {
        self::$containerStack[] = $container;
    }

    /**
     * @internal
     */
    public static function pop()
    {
        array_pop(self::$containerStack);
    }

    /**
     * @internal
     */
    public static function addDefinitions(array $definitions)
    {
        self::$definitions[] = $definitions;
    }

    /**
     * Returns the container entry registered under the given name, such as a class or interface name.
     *
     * @template T of object
     * @param class-string<T>|string $name Container entry name.
     * @return ($name is class-string<T> ? T : mixed) The resolved entry.
     * @throws NotFoundException|DependencyException
     * @api
     */
    public static function get($name)
    {
        try {
            return self::getContainer()->get($name);
        } catch (\DI\NotFoundException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        } catch (\DI\DependencyException $e) {
            throw new DependencyException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @internal
     */
    public static function getDefinitions()
    {
        return self::$definitions;
    }
}
