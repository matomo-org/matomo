<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Matomo\Dependencies\Twig;

if (!function_exists('Matomo\Dependencies\DI\value')) {
    function value()
    {
        return \DI\value(...func_get_args());
    }
}
