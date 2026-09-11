<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Plugins\CoreAdminHome\CustomLogo;

/**
 * Whether this instance has been given branding of its own.
 *
 * A thin wrapper over `CustomLogo`, whose accessors are static: going through an instance
 * keeps the trigger that reads this injectable, and therefore testable without writing
 * image files into the instance's misc directory.
 */
class CustomBranding
{
    public function hasCustomLogo(): bool
    {
        return CustomLogo::hasUserLogo();
    }
}
