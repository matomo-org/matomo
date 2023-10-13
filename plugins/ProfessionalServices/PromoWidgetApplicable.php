<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Plugin\Manager;
use Piwik\ProfessionalServices\Advertising;

class PromoWidgetApplicable {

    /**
     * @var Advertising
     */
    private $advertising;

    /**
     * @var Manager
     */
    private $manager;

    public function __construct(Advertising $advertising, Manager $manager) {
        $this->advertising = $advertising;
        $this->manager = $manager;
    }

    public function check(string $pluginName): bool {
        return $this->advertising->areAdsForProfessionalServicesEnabled() &&
            $this->manager->isPluginActivated($pluginName) === false;
    }
}
