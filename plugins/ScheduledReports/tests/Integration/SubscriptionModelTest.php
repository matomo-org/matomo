<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests;

use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\ScheduledReports\SubscriptionModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ScheduledReports
 * @group SubscriptionModelTest
 * @group Plugins
 */
class SubscriptionModelTest extends IntegrationTestCase
{
    public function testUpdateReportSubscriptions()
    {
        $model = new SubscriptionModel();

        $emails = ['test@matomo.org', 'test2@matomo.org'];
        $model->updateReportSubscriptions(1, $emails);

        $subscriptions = $model->getReportSubscriptions(1);
        $this->assertSubscriptionEmails($subscriptions, $emails);

        $emails = ['test2@matomo.org', 'test7@matomo.org'];
        $model->updateReportSubscriptions(1, $emails);

        $subscriptions = $model->getReportSubscriptions(1);
        $this->assertSubscriptionEmails($subscriptions, $emails);
    }

    /**
     * @dataProvider getUnsubscribeTests
     */
    public function testUnsubscribe($emailMe, $additionalEmails, $emailToUnsubscribe, $subscriptionCount, $expectedEmailMe, $expectedAdditionalEmails)
    {
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        Fixture::createSuperUser(false);

        FakeAccess::$identity  = 'superUserLogin';
        FakeAccess::$superUser = true;

        $model = new SubscriptionModel();

        $reportId = $this->createAndSendReport($emailMe, $additionalEmails);
        $subscriptions = $model->getReportSubscriptions($reportId);

        $this->assertCount($subscriptionCount, $subscriptions);

        foreach ($subscriptions as $subscription) {
            if ($subscription['email'] == $emailToUnsubscribe) {
                $model->unsubscribe($subscription['token']);
                break;
            }
        }

        $report = $this->getReport($reportId);

        $this->assertEquals($expectedEmailMe, $report['parameters'][ScheduledReports::EMAIL_ME_PARAMETER]);
        $this->assertEquals($expectedAdditionalEmails, $report['parameters'][ScheduledReports::ADDITIONAL_EMAILS_PARAMETER]);

        $subscriptions = $model->getReportSubscriptions($reportId, true);
        foreach ($subscriptions as $subscription) {
            if ($subscription['email'] == $emailToUnsubscribe) {
                $this->assertEmpty($subscription['token']);
                $this->assertNotEmpty($subscription['ts_unsubscribed']);
            }
        }
    }

    public function getUnsubscribeTests()
    {
        return [
            // user subscribed report
            [true, [], 'hello@example.org', 1, false, []],
            // user subscribed report and added his email additionally [also counted as one subscription]
            [true, ['hello@example.org'], 'hello@example.org', 1, false, []],
            // user didn't subscribe, but added his email additionally
            [false, ['hello@example.org'], 'hello@example.org', 1, false, []],
            // user subscribed report and added an additional one
            [true, ['test@matomo.org'], 'test@matomo.org', 2, true, []],
            // user didn't subscribe but added two additional emails
            [false, ['guest@example.org', 'test@matomo.org'], 'guest@example.org', 2, false, ['test@matomo.org']],
        ];
    }

    private function assertSubscriptionEmails($subscriptions, $emails)
    {
        $this->assertEquals(count($subscriptions), count($emails));

        foreach ($subscriptions as $subscription) {
            $this->assertTrue(in_array($subscription['email'], $emails));
        }
    }

    private function createAndSendReport($emailMe = true, $additionalEmails = [])
    {
        $reportType   = 'email';
        $reportFormat = 'pdf';
        $reports      = array('VisitsSummary_get', 'UserCountry_getCountry');
        $parameters   = [
            ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY,
            ScheduledReports::EMAIL_ME_PARAMETER => $emailMe,
            ScheduledReports::ADDITIONAL_EMAILS_PARAMETER => $additionalEmails
        ];

        $reportId = API::getInstance()->addReport(1, 'description', 'day', 3, $reportType, $reportFormat, $reports, $parameters);

        API::getInstance()->sendReport($reportId, false, false, true);

        return $reportId;
    }

    private function getReport($idReport)
    {
        $reports = API::getInstance()->getReports(false, false, $idReport);
        return reset($reports);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
