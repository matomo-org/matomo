<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Test;

use Piwik\Config;
use Piwik\Option;
use Piwik\Plugins\CoreUpdater\UpdateCommunication;
use Piwik\Tests\Framework\Fixture;
use Piwik\UpdateCheck;
use Piwik\Version;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 */
class UpdateCommunicationTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_isEnabled()
    {
        $updateCommunication = new UpdateCommunication();

        $this->assertTrue($updateCommunication->isEnabled());

        Config::getInstance()->General['enable_update_communication'] = 0;
        $this->assertFalse($updateCommunication->isEnabled());

        Config::getInstance()->General['enable_update_communication'] = 1;
        $this->assertTrue($updateCommunication->isEnabled());
    }

    /**
     * @dataProvider provideSendNotificationData
     */
    public function test_sendNotificationIfUpdateAvailable($latestVersion, $lastSentVersion, $expects, $expectedLastSentVersion)
    {
        $this->setLatestVersion($latestVersion);
        $this->setLastSentVersion($lastSentVersion);

        $mock = $this->getCommunicationMock(array('sendNotifications'));
        $mock->expects($expects)->method('sendNotifications');
        $mock->sendNotificationIfUpdateAvailable();

        $this->assertEquals($expectedLastSentVersion, $this->getLastSentVersion());
    }

    public function provideSendNotificationData()
    {
        return array(
            array(Version::VERSION, false, $this->never(), false), // shouldNotSend_IfNoUpdateAvailable
            array('33.0.0', '33.0.0', $this->never(), '33.0.0'),   // shouldNotSend_IfAlreadyNotified
            array('31.0.0', '33.0.0', $this->never(), '33.0.0'),   // shouldNotSend_IfAlreadyNotifiedAboutLaterRelease
            array('3333.3333.3333-bbeta10', '31.0.0', $this->never(), '31.0.0'),  // shouldNotSend_IfLatestVersionIsNotVersionLike,
            array('33.0.0', false,    $this->once(), '33.0.0'),    // shouldSend_IfUpdateAvailableAndNeverSentAnyBefore
            array('33.0.0', '31.0.0', $this->once(), '33.0.0'),    // shouldSend_IfUpdateAvailable
        );
    }

    public function test_sendNotifications_shouldSentCorrectEmail()
    {
        $rootUrl = Fixture::getTestRootUrl();
        $message = "ScheduledReports_EmailHello

CoreUpdater_ThereIsNewVersionAvailableForUpdate

CoreUpdater_YouCanUpgradeAutomaticallyOrDownloadPackage
{$rootUrl}index.php?module=CoreUpdater&action=newVersionAvailable

CoreUpdater_ViewVersionChangelog
http://piwik.org/changelog/piwik-33-0-0/

CoreUpdater_FeedbackRequest
http://piwik.org/contact/";

        $this->assertEmailForVersion('33.0.0', $message);
    }

    public function test_sendNotifications_shouldNotIncludeChangelogIfNotMajorVersionUpdate()
    {
        $rootUrl = Fixture::getTestRootUrl();
        $message = "ScheduledReports_EmailHello

CoreUpdater_ThereIsNewVersionAvailableForUpdate

CoreUpdater_YouCanUpgradeAutomaticallyOrDownloadPackage
{$rootUrl}index.php?module=CoreUpdater&action=newVersionAvailable

CoreUpdater_FeedbackRequest
http://piwik.org/contact/";

        $this->assertEmailForVersion('33.0.0-b1', $message);
    }

    private function assertEmailForVersion($version, $expectedMessage)
    {
        $this->setLatestVersion($version);

        $subject = 'CoreUpdater_NotificationSubjectAvailableCoreUpdate';

        $mock = $this->getCommunicationMock(array('sendEmailNotification'));
        $mock->expects($this->once())
            ->method('sendEmailNotification')
            ->with($this->equalTo($subject), $this->equalTo($expectedMessage));
        $mock->sendNotificationIfUpdateAvailable();
    }

    private function setLastSentVersion($value)
    {
        Option::set('last_update_communication_sent_core', $value);
    }

    private function getLastSentVersion()
    {
        return Option::get('last_update_communication_sent_core');
    }

    private function setLatestVersion($value)
    {
        $this->preventVersionIsOverwrittenByActualVersionCheck();
        Option::set(UpdateCheck::LATEST_VERSION, $value);
    }

    private function preventVersionIsOverwrittenByActualVersionCheck()
    {
        Config::getInstance()->General['enable_auto_update'] = false;
    }

    /**
     * @param array $methodsToOverwrite
     * @return UpdateCommunication
     */
    private function getCommunicationMock($methodsToOverwrite)
    {
        return $this->getMock('\Piwik\Plugins\CoreUpdater\UpdateCommunication', $methodsToOverwrite);
    }
}
