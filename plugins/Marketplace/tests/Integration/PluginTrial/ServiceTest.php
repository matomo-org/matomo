<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Config;
use Piwik\Config\GeneralConfig;
use Piwik\Notification\Manager;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Marketplace\PluginTrial\DecisionNotifications;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\RequestsNotification;
use Piwik\Plugins\Marketplace\PluginTrial\Service;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class ServiceTest extends IntegrationTestCase
{
    private bool $sessionWasEnabled;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::loadAllTranslations();
        $this->switchToSuperUser();
        // the local config may turn the flag on, so the tests of it being on say so themselves
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'disabled'];
        \Zend_Session::$_unitTestEnabled = true;
        $_SESSION = [];
        $this->sessionWasEnabled = \Zend_Session::isWritable() && \Zend_Session::isReadable();
        $this->setSessionEnabled(false);
        Manager::cancelAllNotifications();
    }

    public function tearDown(): void
    {
        $this->setSessionEnabled($this->sessionWasEnabled);
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testRequestDisabled()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $this->assertRequested(false);
    }

    public function testRequestSucceeds()
    {
        $this->assertRequested(false);

        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $this->assertRequested(true);
    }

    public function testWasRequestedDisabled()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        $service = new Service();
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testWasNotRequested()
    {
        $service = new Service();
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testWasRequested()
    {
        $this->setRequested();

        $service = new Service();
        self::assertTrue($service->wasRequested('PremiumPlugin'));
    }

    public function testEachUserCanRequestAPluginOnce()
    {
        $service = new Service();

        FakeAccess::$identity = 'alice';
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        self::assertTrue($service->wasRequested('PremiumPlugin'));

        FakeAccess::$identity = 'bob';
        self::assertFalse($service->wasRequested('PremiumPlugin'));
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        self::assertTrue($service->wasRequested('PremiumPlugin'));

        $logins = array_column((new RequestHistory())->getRequests('PremiumPlugin'), 'login');
        sort($logins);
        self::assertSame(['alice', 'bob'], $logins);
    }

    public function testIsPendingForEveryUserUntilTheRequestEnds()
    {
        $service = new Service();
        FakeAccess::$identity = 'alice';
        self::assertFalse($service->isPending('PremiumPlugin'));
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        FakeAccess::$identity = 'bob';
        self::assertTrue($service->isPending('PremiumPlugin'));

        $service->cancelRequest('PremiumPlugin');
        self::assertFalse($service->isPending('PremiumPlugin'));
    }

    public function testIsPendingDisabled()
    {
        $this->setRequested();
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        self::assertFalse((new Service())->isPending('PremiumPlugin'));
    }

    public function testCancelEndsThePendingRequestButEachUsersRequestStaysPermanent()
    {
        $service = new Service();
        FakeAccess::$identity = 'alice';
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        FakeAccess::$identity = 'bob';
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $service->cancelRequest('PremiumPlugin');

        $this->assertRequested(false);
        self::assertTrue($service->wasRequested('PremiumPlugin'));
        FakeAccess::$identity = 'alice';
        self::assertTrue($service->wasRequested('PremiumPlugin'));

        FakeAccess::$identity = 'carol';
        self::assertFalse($service->wasRequested('PremiumPlugin'));
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $this->assertRequested(true);
    }

    public function testCreateAndDismissNotifications()
    {
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->request('PremiumPlugin2', 'Pretty Premium Plugin 2');

        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(2, $notifications);

        Manager::cancelAllNotifications();

        $service->dismissNotification('Marketplace_PluginTrialRequest_' . md5(FakeAccess::$superUserLogin) . '_PremiumPlugin2');
        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(1, $notifications);
    }

    public function testCreateNotificationsEndsARequestForAPluginAlreadyActivated()
    {
        $service = new Service();
        $service->request('CoreHome', 'Core Home');

        $service->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
        self::assertFalse((new Storage('CoreHome'))->wasRequested());
        self::assertCount(1, (new RequestHistory())->getRequests('CoreHome'));
    }

    public function testCreateNotificationsShowsTheOtherPluginsWhenEndingARequestFails()
    {
        // a malformed requester makes ending the request throw a TypeError, an Error rather than an Exception
        Option::set('Marketplace.PluginTrialRequest.CoreHome', json_encode([
            'requestTime' => time(),
            'displayName' => 'Core Home',
            'dismissed' => [],
            'requestedBy' => ['olaf'],
        ]));
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
        self::assertTrue((new Storage('CoreHome'))->wasRequested());
    }

    public function testCreateNotificationsIsSkippedUntilARequestChanges()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        $service->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        $service->request('PremiumPlugin2', 'Pretty Premium Plugin 2');
        $service->createNotificationsIfNeeded();
        self::assertCount(2, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainOnceTheLastCheckIsTenMinutesOld()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        foreach ($_SESSION as &$namespace) {
            if (is_array($namespace) && isset($namespace['checkedAt'])) {
                $namespace['checkedAt'] -= 600;
            }
        }
        unset($namespace);
        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainForAnotherSuperUserInTheSameSession()
    {
        $this->setSessionEnabled(true);
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        (new UsersModel())->setSuperUserAccess('alice', true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        FakeAccess::$identity = 'alice';
        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainForASuperUserTheRequestWasHiddenFrom()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        (new UsersModel())->setSuperUserAccess('alice', true);

        FakeAccess::$identity = 'alice';
        $service->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        (new UsersModel())->setSuperUserAccess(FakeAccess::$superUserLogin, false);
        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksAgainOnceARequestedPluginIsActivatedWithoutTheEvent()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();

        $pluginsToLoad = new \ReflectionProperty(PluginManager::class, 'pluginsToLoad');
        $activatedPlugins = $pluginsToLoad->getValue(PluginManager::getInstance());
        $pluginsToLoad->setValue(PluginManager::getInstance(), array_merge($activatedPlugins, ['PremiumPlugin']));
        try {
            $service->createNotificationsIfNeeded();
        } finally {
            $pluginsToLoad->setValue(PluginManager::getInstance(), $activatedPlugins);
        }

        self::assertFalse((new Storage('PremiumPlugin'))->wasRequested());
    }

    public function testTheNotificationLinkCarriesOnlyTheSiteOfTheRequestThatBuiltIt()
    {
        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::loadAllTranslations();
        $_GET = ['module' => 'Widgetize', 'action' => 'iframe', 'widget' => '1', 'idSite' => '1'];
        try {
            (new Service())->request('PremiumPlugin', 'Pretty Premium Plugin');
            (new Service())->createNotificationsIfNeeded();
        } finally {
            $_GET = [];
            Fixture::resetTranslations();
        }

        $message = array_values(Manager::getPendingInMemoryNotifications())[0]->message;
        self::assertStringContainsString('href="?idSite=1&module=Marketplace&action=overview#?showPlugin=PremiumPlugin"', $message);
    }

    public function testCreateNotificationsChecksAgainAfterEndingARequestFailed()
    {
        $this->setSessionEnabled(true);
        Option::set('Marketplace.PluginTrialRequest.CoreHome', json_encode([
            'requestTime' => time(),
            'displayName' => 'Core Home',
            'dismissed' => [],
            'requestedBy' => ['olaf'],
        ]));
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testCreateNotificationsChecksOnEveryRequestWithoutASession()
    {
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');
        $service->createNotificationsIfNeeded();
        Manager::cancelAllNotifications();

        $service->createNotificationsIfNeeded();

        self::assertCount(1, Manager::getPendingInMemoryNotifications());
    }

    public function testSuperUsersGetOneNoticeNamingTheRequestedPlugin()
    {
        $this->enablePluginRequests();
        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $service->createNotificationsIfNeeded();

        $message = $this->getOnlyRequestsNoticeMessage();
        self::assertStringContainsString('A user has requested a plugin: <b>Pretty Premium Plugin</b>.', $message);
        self::assertStringContainsString('action=manageRequests', $message);
        self::assertStringContainsString('>Review requests</a>', $message);
    }

    public function testSuperUsersGetOneNoticeListingAllRequestedPlugins()
    {
        $this->enablePluginRequests();
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->request('PremiumPlugin2', 'Plugin B');
        $service->request('PremiumPlugin3', 'Plugin C');

        $service->createNotificationsIfNeeded();

        $message = $this->getOnlyRequestsNoticeMessage();
        self::assertStringContainsString('Your users have requested 3 plugins: ', $message);
        self::assertSame(1, preg_match('/(<b>Plugin [ABC]<\/b>), (<b>Plugin [ABC]<\/b>) and (<b>Plugin [ABC]<\/b>)\./', $message, $names));
        self::assertCount(3, array_unique(array_slice($names, 1)));
    }

    public function testDismissingTheNoticeHidesItUntilAnotherPluginIsRequested()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->request('PremiumPlugin2', 'Plugin B');
        $service->createNotificationsIfNeeded();

        $service->dismissNotification($this->getOnlyRequestsNoticeId());
        Manager::cancelAllNotifications();
        $service->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        $service->request('PremiumPlugin3', 'Plugin C');
        $service->createNotificationsIfNeeded();

        $message = $this->getOnlyRequestsNoticeMessage();
        self::assertStringContainsString('A user has requested a plugin: <b>Plugin C</b>.', $message);
    }

    public function testDismissingTheNoticeKeepsAPluginRequestedSinceItWasShown()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->createNotificationsIfNeeded();

        $service->request('PremiumPlugin2', 'Plugin B');
        $service->dismissNotification($this->getOnlyRequestsNoticeId());
        Manager::cancelAllNotifications();
        $service->createNotificationsIfNeeded();

        $message = $this->getOnlyRequestsNoticeMessage();
        self::assertStringContainsString('A user has requested a plugin: <b>Plugin B</b>.', $message);
    }

    public function testDismissingTheNoticeWithoutASessionDismissesWhatIsPending()
    {
        $this->enablePluginRequests();
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->createNotificationsIfNeeded();

        $service->dismissNotification($this->getOnlyRequestsNoticeId());
        Manager::cancelAllNotifications();
        $service->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    public function testARequesterIsToldOnceThatTheRequestWasApproved()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);

        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        self::assertSame(
            'Good news! Your request for <b>PremiumPlugin</b> has been approved, and the feature is now ready to use.',
            $this->getOnlyApprovedNoticeMessage()
        );

        // a new session, so only the stored decision keeps it from being shown again
        $_SESSION = [];
        Manager::cancelAllNotifications();
        (new Service())->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    public function testARequesterGetsOneNoticeForAllApprovedRequests()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('alice', 'PremiumPlugin2');
        $this->requestAs('alice', 'PremiumPlugin3');
        foreach (['PremiumPlugin', 'PremiumPlugin2', 'PremiumPlugin3'] as $pluginName) {
            (new Service())->decide($pluginName, RequestHistory::DECISION_APPROVED);
        }

        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        self::assertSame(
            'Good news! Your requests for <b>PremiumPlugin</b>, <b>PremiumPlugin2</b> and <b>PremiumPlugin3</b> have been approved, and the features are now ready to use.',
            $this->getOnlyApprovedNoticeMessage()
        );
    }

    public function testARequesterIsToldOnceThatTheRequestWasDeclined()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED, 'Not this year');

        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        $message = $this->getOnlyNotificationMessage('Marketplace_PluginRequestDeclined_PremiumPlugin');
        self::assertStringContainsString('Your request for <b>PremiumPlugin</b> was declined. See details in <a href="', $message);
        self::assertStringContainsString('action=manageRequests', $message);

        $_SESSION = [];
        Manager::cancelAllNotifications();
        (new Service())->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    public function testDeletingARequesterForgetsWhichDecisionsTheyWereShown()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();
        self::assertNotFalse(Option::get('Marketplace.PluginRequestDecisionsSeen.alice'));

        $this->switchToSuperUser();
        UsersManagerAPI::getInstance()->deleteUser('alice');

        self::assertFalse(Option::get('Marketplace.PluginRequestDecisionsSeen.alice'));
    }

    public function testOnlyTheRequesterIsToldOfADecision()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);

        $this->switchTo('bob');
        (new Service())->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    public function testARequesterIsNotCheckedAgainUntilARequestChanges()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('alice', 'PremiumPlugin2');
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        // a decision written without changing the marker is only found once a request changes
        (new RequestHistory())->addDecision('PremiumPlugin', RequestHistory::DECISION_APPROVED, null, null, time());
        (new Service())->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        $this->switchToSuperUser();
        (new Service())->decide('PremiumPlugin2', RequestHistory::DECISION_DECLINED);
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        self::assertNotNull($this->findApprovedNoticeId());
        self::assertArrayHasKey('Marketplace_PluginRequestDeclined_PremiumPlugin2', Manager::getPendingInMemoryNotifications());
    }

    public function testARequesterIsNotToldWithoutASession()
    {
        $this->enablePluginRequests();
        $this->requestAs('alice', 'PremiumPlugin');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);

        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        $this->setSessionEnabled(true);
        (new Service())->createNotificationsIfNeeded();
        self::assertNotNull($this->findApprovedNoticeId());
    }

    public function testARequesterIsNotToldWhileRequestsAreNotActive()
    {
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);

        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    public function testTurningRequestsOnReplacesTheNoticesShownPerPlugin()
    {
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->createNotificationsIfNeeded();
        self::assertCount(1, Manager::getPendingInMemoryNotifications());

        $this->enablePluginRequests();
        $service->createNotificationsIfNeeded();

        $this->getOnlyRequestsNoticeMessage();
    }

    public function testTurningRequestsOffReplacesTheCombinedNotice()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->createNotificationsIfNeeded();

        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'disabled'];
        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();
        self::assertCount(1, $notifications);
        self::assertFalse(RequestsNotification::isOwnId((string) array_key_first($notifications)));
    }

    public function testDecidingARequestRemovesTheCombinedNotice()
    {
        $this->enablePluginRequests();
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->createNotificationsIfNeeded();

        $started = new \ReflectionProperty(\Zend_Session::class, '_sessionStarted');
        $started->setValue(null, true);
        try {
            $service->decide('PremiumPlugin', RequestHistory::DECISION_DECLINED);
        } finally {
            $started->setValue(null, false);
        }

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    public function testAUserPromotedToSuperUserIsCheckedAgain()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        // a request is only shown to the super users it was made under
        (new UsersModel())->setSuperUserAccess('alice', true);
        $this->requestAs('bob', 'PremiumPlugin');

        // the session checked before the promotion took effect
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();
        self::assertCount(0, Manager::getPendingInMemoryNotifications());

        FakeAccess::$superUser = true;
        (new Service())->createNotificationsIfNeeded();

        $this->getOnlyRequestsNoticeMessage();
    }

    public function testDismissingANoticeKeepsAPluginOnlyALaterCheckListed()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $service = new Service();
        $service->request('PremiumPlugin', 'Plugin A');
        $service->createNotificationsIfNeeded();
        $displayedId = $this->getOnlyRequestsNoticeId();

        // the dismiss request, or an AJAX request before it, checks again
        $service->request('PremiumPlugin2', 'Plugin B');
        $service->createNotificationsIfNeeded();
        $service->dismissNotification($displayedId);
        Manager::cancelAllNotifications();
        $service->createNotificationsIfNeeded();

        $message = $this->getOnlyRequestsNoticeMessage();
        self::assertStringContainsString('A user has requested a plugin: <b>Plugin B</b>.', $message);
    }

    public function testAnApprovalFoundByALaterCheckKeepsOneNotYetDisplayed()
    {
        $this->enablePluginRequests();
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        $this->requestAs('alice', 'PremiumPlugin2');
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        $this->switchToSuperUser();
        (new Service())->decide('PremiumPlugin2', RequestHistory::DECISION_APPROVED);
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();
        self::assertCount(2, $notifications);
        $messages = implode(' ', array_column($notifications, 'message'));
        self::assertStringContainsString('<b>PremiumPlugin</b>', $messages);
        self::assertStringContainsString('<b>PremiumPlugin2</b>', $messages);
    }

    public function testDecisionsMadeBeforeRequestersWereFirstCheckedAreNotShown()
    {
        $this->setSessionEnabled(true);
        $this->requestAs('alice', 'PremiumPlugin');
        // like the approvals the 6.1.0-b1 update records for every earlier request
        (new Service())->decide('PremiumPlugin', RequestHistory::DECISION_APPROVED);

        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'enabled'];
        $this->switchTo('alice');
        (new Service())->createNotificationsIfNeeded();

        self::assertCount(0, Manager::getPendingInMemoryNotifications());
    }

    private function enablePluginRequests(): void
    {
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'enabled'];
        // on an instance the first page view does this, long before a request is decided
        DecisionNotifications::createIfNeeded();
    }

    private function requestAs(string $login, string $pluginName): void
    {
        if (!(new UsersModel())->userExists($login)) {
            UsersManagerAPI::getInstance()->addUser($login, 'secret-password-1', $login . '@example.com');
        }

        $this->switchTo($login);
        (new Service())->request($pluginName, $pluginName);
        $this->switchToSuperUser();
    }

    private function switchTo(string $login): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = $login;
    }

    private function switchToSuperUser(): void
    {
        FakeAccess::$superUser = true;
        FakeAccess::$identity = FakeAccess::$superUserLogin;
    }

    private function getOnlyRequestsNoticeId(): string
    {
        $ids = array_keys(Manager::getPendingInMemoryNotifications());
        self::assertCount(1, $ids);
        self::assertTrue(RequestsNotification::isOwnId((string) $ids[0]), (string) $ids[0]);

        return (string) $ids[0];
    }

    private function getOnlyRequestsNoticeMessage(): string
    {
        return $this->getOnlyNotificationMessage($this->getOnlyRequestsNoticeId());
    }

    private function findApprovedNoticeId(): ?string
    {
        foreach (array_keys(Manager::getPendingInMemoryNotifications()) as $id) {
            if (str_starts_with((string) $id, DecisionNotifications::APPROVED_ID_PREFIX)) {
                return (string) $id;
            }
        }

        return null;
    }

    private function getOnlyApprovedNoticeMessage(): string
    {
        $id = $this->findApprovedNoticeId();
        self::assertNotNull($id);

        return $this->getOnlyNotificationMessage($id);
    }

    private function getOnlyNotificationMessage(string $id): string
    {
        $notifications = Manager::getPendingInMemoryNotifications();
        self::assertSame([$id], array_keys($notifications));

        return $notifications[$id]->message;
    }

    /**
     * Under the CLI the session is only marked usable once a namespace is created, which the web front controller
     * does by starting it
     */
    private function setSessionEnabled(bool $enabled): void
    {
        foreach (['_readable', '_writable'] as $flag) {
            (new \ReflectionProperty(\Zend_Session_Abstract::class, $flag))->setValue(null, $enabled);
        }
    }

    protected function assertRequested(bool $expected): void
    {
        $storage = new Storage('PremiumPlugin');
        self::assertEquals($expected, $storage->wasRequested());
    }

    protected function setRequested(): void
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
