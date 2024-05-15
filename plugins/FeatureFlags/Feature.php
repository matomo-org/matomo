<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags;

abstract class Feature
{
    abstract public function getName(): string;

    public static function getInstance(): Feature
    {
        return new static();
    }
}
