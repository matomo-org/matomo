<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\DbHelper;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\AssetManager;
use Piwik\Date;
use Piwik\Common;
use Piwik\Db;

/**
 * Fixture for UI tests.
 */
class UITestFixture extends OmniFixture
{
    public function setUp()
    {
        parent::setUp();

        $this->addNewSitesForSiteSelector();

        DbHelper::createAnonymousUser();
        UsersManagerAPI::getInstance()->setSuperUserAccess('superUserLogin', true);

        AssetManager::getInstance()->removeMergedAssets();

        $this->testingEnvironment->forcedNowTimestamp = $this->now->getTimestamp();

        $visitorIdDeterministic = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));
        $this->testingEnvironment->forcedIdVisitor = $visitorIdDeterministic;

        // launch archiving so tests don't run out of time
        $date = Date::factory($this->dateTime)->toString();
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date);
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date, urlencode($this->segment));
    }

    public function addNewSitesForSiteSelector()
    {
        for ($i = 0; $i != 8; ++$i) {
            self::createWebsite("2011-01-01 00:00:00", $ecommerce = 1, $siteName = "Site #$i", $siteUrl = "http://site$i.com");
        }
    }
}