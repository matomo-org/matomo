<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugins\CoreUpdater\SystemSettings;
use Piwik\Plugins\Marketplace\UpdateCommunication;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Twig;
use Piwik\View;

/**
 * @group Plugins
 * @group Marketplace
 */
class UpdateCommunicationTest extends IntegrationTestCase
{
    /**
     * @var UpdateCommunication
     */
    private $updateCommunication;

    /**
     * @var SystemSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = StaticContainer::get('Piwik\Plugins\CoreUpdater\SystemSettings');
        $this->settings->sendPluginUpdateEmail->setValue(true);

        $this->updateCommunication = new UpdateCommunication($this->settings);
    }

    public function test_canBeEnabled()
    {
        $this->assertTrue(UpdateCommunication::canBeEnabled());

        Config::getInstance()->General['enable_update_communication'] = 0;
        $this->assertFalse(UpdateCommunication::canBeEnabled());

        Config::getInstance()->General['enable_update_communication'] = 1;
        $this->assertTrue(UpdateCommunication::canBeEnabled());
    }

    public function test_isEnabled_shouldReturnFalse_IfCannotBeEnabled()
    {
        $this->assertTrue($this->updateCommunication->isEnabled());

        Config::getInstance()->General['enable_update_communication'] = 0;
        $this->assertFalse($this->updateCommunication->isEnabled());
    }

    public function test_sendNotificationIfUpdatesAvailable_shouldNotSendNotification_IfNoUpdateAvailable()
    {
        $mock = $this->getCommunicationMock(array());
        $mock->expects($this->never())->method('sendEmailNotification');
        $mock->sendNotificationIfUpdatesAvailable();
    }

    /**
     * @dataProvider provideSendNotificationData
     */
    public function test_sendNotificationIfUpdatesAvailable($latestVersion, $lastSentVersion, $expects, $expectedLastSentVersion)
    {
        $pluginsHavingUpdate = array(
            array('name' => 'MyTest', 'latestVersion' => $latestVersion, 'isTheme' => false)
        );
        $this->setLastSentVersion('MyTest', $lastSentVersion);

        $mock = $this->getCommunicationMock($pluginsHavingUpdate);
        $mock->expects($expects)->method('sendEmailNotification');
        $mock->sendNotificationIfUpdatesAvailable();

        $this->assertEquals($expectedLastSentVersion, $this->getLastSentVersion('MyTest'));
    }

    public function provideSendNotificationData()
    {
        return array(
            array('33.0.0', '33.0.0', $this->never(), '33.0.0'), // shouldNotSend_IfAlreadyNotified
            array('31.0.0', '33.0.0', $this->never(), '33.0.0'), // shouldNotSend_IfAlreadyNotifiedAboutLaterRelease
            array('33.0.0', false,    $this->once(), '33.0.0'),  // shouldSend_IfUpdateAvailableAndNeverSentAnyBefore
            array('33.0.0', '31.0.0', $this->once(), '33.0.0'),  // shouldSend_IfUpdateAvailable,
        );
    }

    public function test_sendNotificationIfUpdatesAvailable_ShouldSendOnlyOneEmail_IfMultipleUpdatesAreAvailable()
    {
        $mock = $this->getCommunicationMockHavingManyUpdates();
        $mock->expects($this->once())->method('sendEmailNotification');
        $mock->sendNotificationIfUpdatesAvailable();
    }

    public function test_sendNotificationIfUpdatesAvailable_ShouldUpdateAllSentVersions_IfMultipleUpdatesAreAvailable()
    {
        $mock = $this->getCommunicationMockHavingManyUpdates();
        $mock->expects($this->once())->method('sendEmailNotification');
        $mock->sendNotificationIfUpdatesAvailable();

        $this->assertEquals('33.0.0', $this->getLastSentVersion('MyTest1'));
        $this->assertEquals('32.0.0', $this->getLastSentVersion('MyTest2'));
        $this->assertEquals('31.0.0', $this->getLastSentVersion('MyTest3'));
    }

    public function test_sendNotificationIfUpdatesAvailable_ShouldSendCorrectText()
    {
        $subject = 'CoreUpdater_NotificationSubjectAvailablePluginUpdate';
        $rootUrl = Fixture::getTestRootUrl();
        $twig = new Twig();

        $message = "<p>ScheduledReports_EmailHello</p>
<p>CoreUpdater_ThereIsNewPluginVersionAvailableForUpdate</p>

<ul>
<li>MyTest1 33.0.0</li>
<li>MyTest2 32.0.0</li>
<li>MyTest3 31.0.0</li>
</ul>


<p>
CoreUpdater_NotificationClickToUpdatePlugins<br/>
<a href=\"" . twig_escape_filter($twig->getTwigEnvironment(), $rootUrl, 'html_attr') . "index.php?module=CorePluginsAdmin&action=plugins\">{$rootUrl}index.php?module=CorePluginsAdmin&action=plugins</a>
</p>

<p>
Installation_HappyAnalysing
</p>
";

        $mock = $this->getCommunicationMockHavingManyUpdates();

        $mock->expects($this->once())->method('sendEmailNotification')
             ->with($this->equalTo($subject), $this->callback(function (View $view) use ($message) {
                 $this->assertEquals($message, $view->render());
                 return true;
             }));

        $mock->sendNotificationIfUpdatesAvailable();
    }

    private function setLastSentVersion($pluginName, $version)
    {
        Option::set('last_update_communication_sent_plugin_' . $pluginName, $version);
    }

    private function getLastSentVersion($pluginName)
    {
        return Option::get('last_update_communication_sent_plugin_' . $pluginName);
    }

    /**
     * @param array $pluginsHavingUpdate
     * @return UpdateCommunication
     */
    private function getCommunicationMock($pluginsHavingUpdate)
    {
        $mock = $this->getMockBuilder('\Piwik\Plugins\Marketplace\UpdateCommunication')
                     ->setMethods(array('getPluginsHavingUpdate', 'sendEmailNotification'))
                     ->setConstructorArgs(array($this->settings))
                     ->getMock();

        $mock->expects($this->any())
             ->method('getPluginsHavingUpdate')
             ->will($this->returnValue($pluginsHavingUpdate));

        return $mock;
    }

    private function getCommunicationMockHavingManyUpdates()
    {
        $pluginsHavingUpdate = array(
            array('name' => 'MyTest1', 'latestVersion' => '33.0.0', 'isTheme' => false),
            array('name' => 'MyTest2', 'latestVersion' => '32.0.0', 'isTheme' => false),
            array('name' => 'MyTest3', 'latestVersion' => '31.0.0', 'isTheme' => false),
        );

        $this->setLastSentVersion('MyTest1', false);
        $this->setLastSentVersion('MyTest2', false);
        $this->setLastSentVersion('MyTest3', false);

        $mock = $this->getCommunicationMock($pluginsHavingUpdate);

        return $mock;
    }
}
