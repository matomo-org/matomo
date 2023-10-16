<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\ProfessionalServices\Advertising;

class PromoWidgetApplicable
{

    /**
     * @var Advertising
     */
    private $advertising;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Advertising $advertising, Manager $manager, Config $config)
    {
        $this->advertising = $advertising;
        $this->manager = $manager;
        $this->config = $config;
    }

    public function check(string $pluginName): bool
    {
        if ($this->advertising->areAdsForProfessionalServicesEnabled() === false) {
            return false;
        }

        if ($this->manager->isPluginActivated('Marketplace') === false) {
            return false;
        }

        if ((bool) $this->config->General['enable_internet_features'] === false) {
            return false;
        }

        return $this->manager->isPluginActivated($pluginName) === false;
    }
}
