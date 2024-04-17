<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests;

use Piwik\Piwik;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ScheduledReports
 * @group ScheduledReportsTest
 * @group Plugins
 */
class ScheduledReportsTest extends IntegrationTestCase
{
    /**
     * @var ScheduledReports
     */
    private $reports;
    private $reportIds = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->reports = new ScheduledReports();
        $this->setIdentity('userlogin');

        for ($i = 1; $i <= 4; $i++) {
            Fixture::createWebsite('2014-01-01 00:00:00');
            $this->addReport('userlogin', $i);
        }

        $this->addReport('otherUser', 1);
        $this->addReport('anotherUser', 2);
    }

    public function test_deleteUserReportForSites_shouldNotRemoveAnythingIfNoSitesOrNoLogin()
    {
        $this->reports->deleteUserReportForSites('userLogin', array());

        $this->assertHasReport('userlogin', 1);
        $this->assertHasReport('userlogin', 2);
        $this->assertHasReport('userlogin', 3);
        $this->assertHasReport('userlogin', 4);
        $this->assertHasReport('otherUser', 1);
        $this->assertHasReport('anotherUser', 2);

        $this->reports->deleteUserReportForSites('', array(1, 2, 3, 4));

        $this->assertHasReport('userlogin', 1);
        $this->assertHasReport('userlogin', 2);
        $this->assertHasReport('userlogin', 3);
        $this->assertHasReport('userlogin', 4);
        $this->assertHasReport('otherUser', 1);
        $this->assertHasReport('anotherUser', 2);
    }

    public function test_deleteUserReportForSites_shouldNotFailIfUserHasNoReports()
    {
        $this->reports->deleteUserReportForSites('unk', array());

        $this->assertHasReport('userlogin', 1);
        $this->assertHasReport('userlogin', 2);
        $this->assertHasReport('userlogin', 3);
        $this->assertHasReport('userlogin', 4);
        $this->assertHasReport('otherUser', 1);
        $this->assertHasReport('anotherUser', 2);
    }

    public function test_deleteUserReportForSites_shouldRemoveOnlyReportsForGivenSitesAndLogin()
    {
        $this->reports->deleteUserReportForSites('userLogin', array(1, 2));

        $this->assertHasNotReport('userlogin', 1);
        $this->assertHasNotReport('userlogin', 2);

        $this->assertHasReport('userlogin', 3);
        $this->assertHasReport('userlogin', 4);
        $this->assertHasReport('otherUser', 1);
        $this->assertHasReport('anotherUser', 2);
    }

    public function test_ScheduledReports_shouldRemoveOnlyReportsForGivenSitesAndLogin_IfEventIsTriggered()
    {
        Piwik::postEvent('UsersManager.removeSiteAccess', array('userLogin', array(1, 2)));

        $this->assertHasNotReport('userlogin', 1);
        $this->assertHasNotReport('userlogin', 2);

        $this->assertHasReport('userlogin', 3);
        $this->assertHasReport('userlogin', 4);
        $this->assertHasReport('otherUser', 1);
        $this->assertHasReport('anotherUser', 2);
    }

    public function test_deleteUserReport_shouldRemoveAllReportsOfASpecificUser()
    {
        $this->reports->deleteUserReport('userLogin');

        $this->assertHasNotReport('userlogin', 1);
        $this->assertHasNotReport('userlogin', 2);
        $this->assertHasNotReport('userlogin', 3);
        $this->assertHasNotReport('userlogin', 4);

        $this->assertHasReport('otherUser', 1);
        $this->assertHasReport('anotherUser', 2);
    }

    private function assertHasReport($login, $idSite)
    {
        $report = $this->getReport($login, $idSite);

        $this->assertNotEmpty($report, "Report for $login, $idSite should exist but does not");
    }

    private function assertHasNotReport($login, $idSite)
    {
        try {
            $this->getReport($login, $idSite);
            $this->fail("Report for $login, $idSite should not exist but does");
        } catch (\Exception $e) {
            self::assertStringContainsString("Requested report couldn't be found", $e->getMessage());
        }
    }

    private function getReport($login, $idSite)
    {
        $this->setIdentity($login);

        return API::getInstance()->getReports($idSite, 'day', $this->reportIds[$login . '_' . $idSite]);
    }

    private function addReport($login, $idSite)
    {
        $this->setIdentity($login);

        $reportType   = 'email';
        $reportFormat = 'pdf';
        $reports      = array();
        $parameters   = array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY);

        $reportId = API::getInstance()->addReport($idSite, 'description', 'day', 3, $reportType, $reportFormat, $reports, $parameters);
        $this->reportIds[$login . '_' . $idSite] = $reportId;
    }

    private function setIdentity($login)
    {
        FakeAccess::$identity  = $login;
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
