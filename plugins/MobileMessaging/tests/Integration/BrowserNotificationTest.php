<?php

namespace Piwik\Plugins\MobileMessaging\tests\Integration;

use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\MobileMessaging\Controller;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\SitesManager\API as APISitesManager;

class BrowserNotificationTest extends \Piwik\Tests\Framework\TestCase\IntegrationTestCase
{
    private $idSiteAccess;

    private $controller;

    public function setUp()
    {
        parent::setUp();

        $this->idSiteAccess = APISitesManager::getInstance()->addSite("test", "http://test");

        PluginManager::getInstance()->loadPlugins(array('ScheduledReports', 'MobileMessaging'));
        PluginManager::getInstance()->installLoadedPlugins();

        $this->controller = new Controller(
            StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider'),
            StaticContainer::get('Piwik\Translation\Translator')
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        Option::deleteLike(MobileMessaging::NOTIFICATION_OPTION_KEY_PREFIX . '%');
    }

    public function test_sendReport_writesNotificationToDb()
    {
        $report = array(
            'login' => 'user123',
            'parameters' => array('value' => 'dummyvalue'),
        );
        $reportContent = 'Your report for XYZABCD';
        $reportSubject = 'Matomo';

        $mobileMessaging = new MobileMessaging();
        $mobileMessaging->sendReport(MobileMessaging::NOTIFICATION_TYPE, $report, $reportContent, null, null, $reportSubject, null, null, null, false);

        $notifications = Option::get(MobileMessaging::NOTIFICATION_OPTION_KEY_PREFIX . 'user123');
        $this->assertNotEmpty($notifications);
        $notifications = json_decode($notifications, true);
        $this->assertEquals(1, count($notifications));
        $notification = $notifications[0];
        $this->assertEquals($notification['title'], 'Matomo');
        $this->assertEquals($notification['contents'], 'Your report for XYZABCD');
    }

    public function test_sendReport_writesNotificationToDb_concatsToExisting()
    {
        Option::set(MobileMessaging::NOTIFICATION_OPTION_KEY_PREFIX . 'user123', json_encode(array(array(
            'title' => 'Your Website',
            'contents' => 'Your daily report'
        ))));

        $report = array(
            'login' => 'user123',
            'parameters' => array('value' => 'dummyvalue'),
        );
        $reportContent = 'Your report for XYZABCD';
        $reportSubject = 'Matomo';

        $mobileMessaging = new MobileMessaging();
        $mobileMessaging->sendReport(MobileMessaging::NOTIFICATION_TYPE, $report, $reportContent, null, null, $reportSubject, null, null, null, false);

        $notifications = Option::get('ScheduledReports.notifications.user123');
        $this->assertNotEmpty($notifications);
        $notifications = json_decode($notifications, true);
        $this->assertEquals(2, count($notifications));
        $notification = $notifications[0];
        $this->assertEquals($notification['title'], 'Your Website');
        $this->assertEquals($notification['contents'], 'Your daily report');
        $notification = $notifications[1];
        $this->assertEquals($notification['title'], 'Matomo');
        $this->assertEquals($notification['contents'], 'Your report for XYZABCD');
    }

    public function test_getBrowserNotifications_singleNotification()
    {
        $notification = array(
            'title' => 'Hello',
            'contents' => 'Your daily Matomo report'
        );
        Access::getInstance()->setSuperUserAccess();
        Option::set(MobileMessaging::NOTIFICATION_OPTION_KEY_PREFIX . 'super user was set', json_encode(array($notification)));

        $output = $this->controller->getBrowserNotifications();
        $this->assertNotEmpty($output);
        $output = json_decode($output, true);
        $this->assertEquals($notification, $output[0]);
    }

    public function test_getBrowserNotifications_multipleNotifications()
    {
        $notifications = array(
            array(
                'title' => 'Notification 1',
                'contents' => 'Your first notification'
            ),
            array(
                'title' => 'Notification 2',
                'contents' => 'Your second notification'
            )
        );
        Access::getInstance()->setSuperUserAccess();
        Option::set(MobileMessaging::NOTIFICATION_OPTION_KEY_PREFIX . 'super user was set', json_encode($notifications));

        $output = $this->controller->getBrowserNotifications();
        $this->assertNotEmpty($output);
        $output = json_decode($output, true);
        $this->assertEquals($notifications, $output);
    }

    public function test_getBrowserNotifications_invalidatesExisting()
    {
        $notification = array(
            'title' => 'Hello',
            'contents' => 'Your daily Matomo report'
        );
        Access::getInstance()->setSuperUserAccess();
        Option::set(MobileMessaging::NOTIFICATION_OPTION_KEY_PREFIX . 'super user was set', json_encode(array($notification)));

        $this->controller->getBrowserNotifications();

        $this->assertFalse(Option::get('ScheduledReports.notifications.super user was set'));
    }

    public function test_getBrowserNotifications_noNotifications()
    {
        Access::getInstance()->setSuperUserAccess();

        $output = $this->controller->getBrowserNotifications();

        $this->assertEquals('[]', $output);
    }

    public function test_getBrowserNotifications_notAuthenticated()
    {
        $this->forceLogout();

        $this->setExpectedException(NoAccessException::class);
        $this->controller->getBrowserNotifications();
        }

    private function forceLogout()
    {
        Access::getInstance()->setSuperUserAccess(false);
        Access::getInstance()->reloadAccess();
    }
}