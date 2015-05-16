<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

/**
 * TODO
 *
 * TODO: change to EnvironmentInterceptor?
 */
interface EnvironmentManipulator
{
    /**
     * TODO
     *
     * @param $className
     * @param array $kernelObjects
     * @return mixed
     */
    public function makeKernelObject($className, array $kernelObjects);
}