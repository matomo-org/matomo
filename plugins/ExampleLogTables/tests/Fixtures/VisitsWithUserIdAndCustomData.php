<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\ExampleLogTables\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Tests\Framework\Fixture;

class VisitsWithUserIdAndCustomData extends Fixture
{
    public $dateTime = '2018-02-01 11:22:33';
    public $idSite = 1;

    private static $countryCodes = ['CA', 'CN', 'DE', 'ES', 'FR', 'IE', 'IN', 'IT', 'MX', 'PT', 'RU', 'GB', 'US'];

    public function setUp(): void
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        // set up database tables
        $userLog = new CustomUserLog();
        $userLog->install();
        $groupLog = new CustomGroupLog();
        $groupLog->install();

        $this->trackVisits();
        $this->insertCustomUserLogData();
        $this->insertCustomGroupLogData();
    }

    private function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        foreach (array('user1', 'user2', 'user3', 'user4', false) as $key => $userId) {
            for ($numVisits = 0; $numVisits < ($key + 1) * 10; $numVisits++) {
                $visitDateTime = Date::factory($this->dateTime)->addHour($numVisits)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);
                $t->setUserId($userId);
                $t->setVisitorId(str_pad($numVisits . $key, 16, 'a'));
                $t->setCountry(self::$countryCodes[$numVisits % count(self::$countryCodes)]);

                if ($numVisits % 5 == 0) {
                    $t->doTrackSiteSearch('some search term' . $numVisits);
                }

                if ($numVisits % 4 == 0) {
                    $t->doTrackEvent('Event action ' . $numVisits, 'event cat ' . $numVisits);
                }

                if ($numVisits % 7 == 0) {
                    $t->doTrackContentInteraction('click', 'slider ' . $numVisits % 4);
                }

                if ($numVisits % 7 == 4) {
                    $t->doTrackAction('http://out.link', 'outlink');
                }

                if ($numVisits % 5 == 3) {
                    $t->setEcommerceView('SKU VERY nice indeed ' . ($numVisits % 3), 'PRODUCT name ' . ($numVisits % 4), 'category ' . ($numVisits % 5), $numVisits * 2.79);
                }

                $t->setForceNewVisit();
                $t->setUrl('http://example.org/my/dir/page' . ($numVisits % 4));

                $visitDateTime = Date::factory($this->dateTime)->addHour($numVisits + 6)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);

                if ($numVisits % 7 == 0) {
                    $t->doTrackAction('http://example.org/download.pdf', 'download');
                }

                self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));

                if ($numVisits % 9 == 0) {
                    $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($numVisits + 6.1)->getDatetime());
                    $t->addEcommerceItem('SKU VERY nice indeed ' . ($numVisits % 3), 'PRODUCT name ' . ($numVisits % 4), 'category ' . ($numVisits % 5), $numVisits * 2.79);
                    self::assertTrue($t->doTrackEcommerceCartUpdate($numVisits * 17));
                }
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }

    private function insertCustomUserLogData()
    {
        $customLog = new CustomUserLog();
        $customLog->addUserInformation('user1', 'admin', 'men');
        $customLog->addUserInformation('user2', 'user', 'women');
        $customLog->addUserInformation('user3', 'admin', 'women');
        $customLog->addUserInformation('user4', '', 'men');
    }
    
    private function insertCustomGroupLogData()
    {
        $customGroup = new CustomGroupLog();
        $customGroup->addGroupInformation('admin', 1);
        $customGroup->addGroupInformation('user', 0);
    }
}
