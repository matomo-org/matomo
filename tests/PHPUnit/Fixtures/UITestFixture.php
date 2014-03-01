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
use Piwik\FrontController;
use Piwik\Option;

/**
 * Fixture for UI tests.
 */
class UITestFixture extends OmniFixture
{
    public function setUp()
    {
        parent::setUp();

        $this->addNewSitesForSiteSelector();
        $this->createEmptyDashboard();

        DbHelper::createAnonymousUser();
        UsersManagerAPI::getInstance()->setSuperUserAccess('superUserLogin', true);

        Option::set("Tests.forcedNowTimestamp", $this->now->getTimestamp());

    // launch archiving so tests don't run out of time
        $date = Date::factory($this->dateTime)->toString();
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date);
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date, urlencode($this->segment));
    }

    public function performSetUp($testCase, $setupEnvironmentOnly = false)
    {
        parent::performSetUp($testCase, $setupEnvironmentOnly);

        AssetManager::getInstance()->removeMergedAssets();

        $visitorIdDeterministic = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));
        $this->testEnvironment->forcedIdVisitor = $visitorIdDeterministic;

        $forcedNowTimestamp = Option::get("Tests.forcedNowTimestamp");
        if ($forcedNowTimestamp == false) {
            throw Exception("Incorrect fixture setup, Tests.forcedNowTimestamp option does not exist! Run the setup again.");
        }

        $this->testEnvironment->forcedNowTimestamp = $forcedNowTimestamp;
        $this->testEnvironment->save();
    }

    public function addNewSitesForSiteSelector()
    {
        for ($i = 0; $i != 8; ++$i) {
            self::createWebsite("2011-01-01 00:00:00", $ecommerce = 1, $siteName = "Site #$i", $siteUrl = "http://site$i.com");
        }
    }

    public function createEmptyDashboard()
    {
        $oldGet = $_GET;

        // create empty dashboard
        $dashboard = array(
            array(
                array(
                    'uniqueId' => "widgetVisitsSummarygetEvolutionGraphcolumnsArray",
                    'parameters' => array(
                        'module' => 'VisitsSummary',
                        'action' => 'getEvolutionGraph',
                        'columns' => 'nb_visits'
                    )
                )
            ),
            array(),
            array()
        );

        $_GET['name'] = 'D4';
        $_GET['layout'] = Common::json_encode($dashboard);
        $_GET['idDashboard'] = 5;
        $_GET['idSite'] = 2;
        FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');

        $_GET = $oldGet;
    }
}