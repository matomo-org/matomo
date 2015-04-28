<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\SitesManager\tests\Fixtures;

use Piwik\Plugin;
use Piwik\Tests\Framework\Fixture;

/**
 * Track actions with bandwidth
 */
class ManySites extends Fixture
{
    public $dateTime = '2010-01-03 11:22:33';

    public function setUp()
    {
        for ($idSite = 1; $idSite < 64; $idSite++) {
            if (!self::siteCreated($idSite)) {
                if ($idSite < 35) {
                    $siteName = 'SiteTest' . $idSite; // we generate different site names to be used in search
                } else {
                    $siteName = 'Site ' . $idSite;
                }

                self::createWebsite($this->dateTime, $ecommerce = 0, $siteName);
            }
        }
    }

}