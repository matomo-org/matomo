<?php
/**
 * Piwik - free/libre analytics platform
 *
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

/**
 * Thrown if StaticContainer cannot find a container. This occurs when the StaticContainer is
 * accessed before the Environment is created.
 */
class ContainerNotFoundException extends \RuntimeException
{
}