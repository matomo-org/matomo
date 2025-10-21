<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces;

/**
 * @template T of mixed
 */
interface ConfigSettingInterface
{
    /**
     * @return T
     */
    public static function getConfigValue();
}
