<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\ProfessionalServices;

class Advertising extends \Piwik\ProfessionalServices\Advertising
{
    public function __construct()
    {
    }

    public function areAdsForProfessionalServicesEnabled()
    {
        return true;
    }
}
