<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Interfaces;

interface OptionSettingInterface
{
    /**
     * @return string|false
     */
    public static function getOptionValue(?int $idSite = null);
}
