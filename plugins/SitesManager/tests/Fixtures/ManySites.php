<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Fixtures;

use Piwik\Plugin\Manager;
use Piwik\Plugins\MobileAppMeasurable;
use Piwik\Tests\Framework\Fixture;

/**
 * Track actions with bandwidth
 */
class ManySites extends Fixture
{
    public $dateTime = '2010-01-03 11:22:33';

    public function setUp(): void
    {
        // Ensure plugin is activated, otherwise adding a site with this type will fail
        Manager::getInstance()->activatePlugin('MobileAppMeasurable');

        for ($idSite = 1; $idSite < 64; $idSite++) {
            if (!self::siteCreated($idSite)) {
                if ($idSite < 35) {
                    $siteName = 'SiteTest' . $idSite; // we generate different site names to be used in search
                } else {
                    $siteName = 'Site ' . $idSite;
                }

                $type = null;
                if ($idSite === 2) {
                    $type = MobileAppMeasurable\Type::ID;
                }

                self::createWebsite(
                    $this->dateTime,
                    $ecommerce = 0,
                    $siteName,
                    $siteUrl = false,
                    $siteSearch = 1,
                    $searchKeywordParameters = null,
                    $searchCategoryParameters = null,
                    $timezone = null,
                    $type
                );
            }
        }
    }
}
