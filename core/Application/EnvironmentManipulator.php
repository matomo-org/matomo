<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

/**
 * Used to manipulate Environment instances before the container is created.
 * Only used by the testing environment setup code, shouldn't be used anywhere
 * else.
 */
interface EnvironmentManipulator
{
    /**
     * Create a custom kernel object, overriding the default behavior.
     *
     * @param $className
     * @param array $kernelObjects
     * @return mixed
     */
    public function makeKernelObject($className, array $kernelObjects);
}