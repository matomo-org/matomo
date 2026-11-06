<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Exception;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\API;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;
use Piwik\Plugins\Marketplace\LicenseKey;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Marketplace\Api\Service\Exception as ServiceException;

/**
 * @group Marketplace
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
{
    private const INVALID_LICENSES_CACHE_KEY = 'Marketplace_ExpiredPlugins';

    /**
     * @var API
     */
    private $api;

    /**
     * @var Service
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        API::unsetInstance();
        $this->api = API::getInstance();

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        $this->setSuperUser();
    }

    public function testCreateAccountShouldSucceedIfMarketplaceCallsDidNotFail(): void
    {
        $this->assertNotHasLicenseKey();

        $this->service->setOnDownloadCallback(static function ($action, $params, $postData) {
            self::assertSame('createAccount', $action);
            self::assertArrayHasKey('email', $postData);
            self::assertSame('test@matomo.org', $postData['email']);

            return [
                'status' => 200,
                'headers' => [],
                'data' => json_encode(['license_key' => 'key']),
            ];
        });

        self::assertTrue($this->api->createAccount('test@matomo.org'));

        $this->assertHasLicenseKey();
    }

    public function testCreateAccountRequiresSuperUserAccessIfUser()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setUser();
        $this->api->createAccount('test@matomo.org');
    }

    public function testCreateAccountRequiresSuperUserAccessIfAnonymous(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setAnonymousUser();
        $this->api->createAccount('test@matomo.org');
    }

    public function testCreateAccountShouldThrowExceptionIfLicenseKeyIsAlreadySet(): void
    {
        $this->buildLicenseKey()->set('key');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Marketplace_CreateAccountErrorLicenseExists');

        $this->api->createAccount('test@matomo.org');
    }

    public function testCreateAccountShouldThrowExceptionIfEmailIsEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorEmptyValue');

        $this->api->createAccount('');
    }

    public function testCreateAccountShouldThrowExceptionIfEmailIsInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Marketplace_CreateAccountErrorEmailInvalid');

        $this->api->createAccount('invalid.email@');
    }

    public function testCreateAccountShouldThrowExceptionIfEmailIsNotAllowed(): void
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->allowedEmailDomains->setValue(['example.org']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('UsersManager_ErrorEmailDomainNotAllowed');

        $this->api->createAccount('test@matomo.org');
    }

    /**
     * @dataProvider dataCreateAccountErrorDownloadResponses
     */
    public function testCreateAccountShouldThrowExceptionIfSomethingGoesWrong(
        array $responseInfo,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->service->setOnDownloadCallback(static function () use ($responseInfo) {
            return $responseInfo;
        });

        $this->api->createAccount('test@matomo.org');
    }

    public function testCreateAccountClearsInvalidLicensesCacheEvenIfTheMarketplaceRejectsTheEmail(): void
    {
        $this->service->setOnDownloadCallback(static function () {
            return ['status' => 409, 'headers' => [], 'data' => ''];
        });

        $this->seedInvalidLicensesCache();

        try {
            $this->api->createAccount('test@matomo.org');
            self::fail('createAccount should have rejected the duplicate email');
        } catch (Exception $e) {
            self::assertSame('Marketplace_CreateAccountErrorAPIEmailExists', $e->getMessage());
        }

        $this->assertInvalidLicensesCacheCleared();
    }

    public function dataCreateAccountErrorDownloadResponses(): iterable
    {
        yield 'marketplace response not readable' => [
            [
                'status' => 500,
                'headers' => [],
                'data' => 'not valid json',
            ],
            Exception::class,
            'Marketplace_CreateAccountErrorAPI',
        ];

        yield 'marketplace rejected email as invalid' => [
            [
                'status' => 400,
                'headers' => [],
                'data' => '',
            ],
            Exception::class,
            'Marketplace_CreateAccountErrorAPIEmailInvalid',
        ];

        yield 'marketplace rejected email as duplicate' => [
            [
                'status' => 409,
                'headers' => [],
                'data' => '',
            ],
            Exception::class,
            'Marketplace_CreateAccountErrorAPIEmailExists',
        ];

        yield 'unexpected response status code' => [
            [
                'status' => 204,
                'headers' => [],
                'data' => '',
            ],
            Exception::class,
            'Marketplace_CreateAccountErrorAPI',
        ];

        yield 'missing license key' => [
            [
                'status' => 200,
                'headers' => [],
                'data' => '',
            ],
            Exception::class,
            'Marketplace_CreateAccountErrorAPI',
        ];
    }

    public function testDeleteLicenseKeyRequiresSuperUserAccessIfUser()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setUser();
        $this->api->deleteLicenseKey();
    }

    public function testDeleteLicenseKeyRequiresSuperUserAccessIfAnonymous()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setAnonymousUser();
        $this->api->deleteLicenseKey();
    }

    public function testDeleteLicenseKeyShouldRemoveAnExistingKey()
    {
        $this->buildLicenseKey()->set('key');
        $this->assertHasLicenseKey();

        $this->api->deleteLicenseKey();

        $this->assertNotHasLicenseKey();
    }

    public function testRequestTrialRequiresRegularUserAccessIfSuperUser()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot request trial as a super user');

        $this->api->requestTrial('testPlugin');
    }

    public function testRequestTrialRequiresRegularUserAccessIfAnonymous(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('General_YouMustBeLoggedIn');

        $this->setAnonymousUser();
        $this->api->requestTrial('testPlugin');
    }

    public function testRequestTrialRequiresValidPluginName(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid plugin name given');

        $this->setUser();
        $this->api->requestTrial('this/is/not/valid');
    }

    public function testRequestTrialSendsEmailToAllSuperUsers(): void
    {
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$sentMail) {
            $sentMail = $mail;
        });

        $this->setUser();
        $service = $this->service;

        $this->service->setOnDownloadCallback(static function ($action, $params, $postData) use ($service) {
            return $service->getFixtureContent('v2.0_plugins_PaidPlugin1_info.json');
        });

        self::assertTrue($this->api->requestTrial('PaidPlugin1'));

        self::assertNotNull($sentMail);
        self::assertInstanceOf(RequestTrialNotificationEmail::class, $sentMail);
        self::assertSame(['hello@example.org' => ''], $sentMail->getRecipients());

        self::assertSame(
            'Marketplace_RequestTrialNotificationEmailSubject',
            $sentMail->getSubject()
        );

        self::assertStringContainsString(
            'Marketplace_RequestTrialNotificationEmailIntro',
            $sentMail->getBodyHtml()
        );
    }

    public function testRequestTrialStoresTheNote(): void
    {
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');

        self::assertTrue($this->api->requestTrial('PaidPlugin1', '  We need this  '));

        self::assertSame([['PaidPlugin1', 'trial', 'We need this']], $this->getStoredRequests());
    }

    public function testRequestTrialRejectsANoteOver1000Characters(): void
    {
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');

        try {
            $this->api->requestTrial('PaidPlugin1', str_repeat('é', 1001));
            self::fail('A note over 1000 characters was accepted');
        } catch (Exception $e) {
            self::assertSame('General_ValidatorErrorCharacterTooLong', $e->getMessage());
        }

        self::assertSame([], $this->getStoredRequests());
    }

    public function testRequestInstallStoresAnInstallRequestWithItsNote(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('Barometer');

        self::assertTrue($this->api->requestInstall('Barometer', str_repeat('é', 1000)));

        self::assertSame([['Barometer', 'install', str_repeat('é', 1000)]], $this->getStoredRequests());
    }

    public function testRequestInstallMailsSuperUsersAboutAnInstall(): void
    {
        Piwik::addAction('Mail.send', function (Mail $mail) use (&$sentMail) {
            $sentMail = $mail;
        });
        Piwik::addAction('Marketplace.pluginTrialRequested', function () {
            self::fail('An install request was announced as a trial request');
        });
        Piwik::addAction('Marketplace.pluginInstallRequested', function (...$args) use (&$eventArgs) {
            $eventArgs = $args;
        });

        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('Barometer');

        self::assertTrue($this->api->requestInstall('Barometer'));

        self::assertSame(['Barometer', 'Barometer'], $eventArgs);
        self::assertInstanceOf(RequestTrialNotificationEmail::class, $sentMail);
        self::assertSame('Marketplace_RequestInstallNotificationEmailSubject', $sentMail->getSubject());
        self::assertStringContainsString('Marketplace_RequestInstallNotificationEmailIntro', $sentMail->getBodyHtml());
        self::assertStringContainsString('Marketplace_RequestInstallNotificationEmailHowToInstallStep3', $sentMail->getBodyHtml());
        self::assertStringNotContainsString('Marketplace_RequestTrialNotificationEmailIntro', $sentMail->getBodyHtml());
        self::assertStringNotContainsString('Marketplace_RequestTrialNotificationEmailHowToStartStep3', $sentMail->getBodyHtml());
    }

    public function testRequestTrialRefusesAFreePlugin(): void
    {
        $this->setUser();
        $this->returnPluginInfo('Barometer');

        try {
            $this->api->requestTrial('Barometer');
            self::fail('A trial of a free plugin was requested');
        } catch (Exception $e) {
            self::assertSame('Marketplace_FreePluginHasNoTrial', $e->getMessage());
        }

        self::assertSame([], $this->getStoredRequests());
    }

    public function testRequestTrialIsRejectedWhenTheUserHasAlreadyRequestedThePlugin(): void
    {
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');
        $this->api->requestTrial('PaidPlugin1', 'First');

        try {
            $this->api->requestTrial('PaidPlugin1', 'Second');
            self::fail('A second request by the same user was accepted');
        } catch (Exception $e) {
            self::assertSame('Marketplace_PluginAlreadyRequested', $e->getMessage());
        }

        self::assertSame([['PaidPlugin1', 'trial', 'First']], $this->getStoredRequests());
    }

    public function testRequestInstallIsRejectedWhenTheUsersRequestWasDeclined(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('Barometer');
        $this->api->requestInstall('Barometer');

        $this->setSuperUser();
        $this->api->declinePluginRequest('Barometer');

        $this->setUser();
        try {
            $this->api->requestInstall('Barometer', 'Please reconsider');
            self::fail('A request after a declined one by the same user was accepted');
        } catch (Exception $e) {
            self::assertSame('Marketplace_PluginAlreadyRequested', $e->getMessage());
        }

        self::assertSame([['Barometer', 'install', null]], $this->getStoredRequests());
    }

    public function testRequestInstallIsRejectedWhileTheFeatureFlagIsOff(): void
    {
        $this->disablePluginRequests();
        $this->setUser();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Plugin requests are not enabled');

        $this->api->requestInstall('Barometer');
    }

    public function testRequestInstallRequiresRegularUserAccessIfSuperUser(): void
    {
        $this->enablePluginRequests();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot request install as a super user');

        $this->api->requestInstall('Barometer');
    }

    public function testRequestInstallRequiresRegularUserAccessIfAnonymous(): void
    {
        $this->enablePluginRequests();
        $this->setAnonymousUser();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('General_YouMustBeLoggedIn');

        $this->api->requestInstall('Barometer');
    }

    public function testRequestInstallIsRejectedForAPaidPlugin(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only free plugins can be requested for install');

        $this->api->requestInstall('PaidPlugin1');
    }

    public function testRequestInstallIsRejectedForAnInstalledPlugin(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        // a free plugin's info under the name of one that is installed in every Matomo
        $service = $this->service;
        $this->service->setOnDownloadCallback(static function () use ($service) {
            $info = json_decode($service->getFixtureContent('v2.0_plugins_Barometer_info.json'), true);
            $info['name'] = 'Marketplace';

            return json_encode($info);
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The plugin is already installed: Marketplace');

        $this->api->requestInstall('Marketplace');
    }

    public function testRequestInstallIsRejectedWhenPluginsCannotBeInstalled(): void
    {
        $this->enablePluginRequests();
        Config::getInstance()->General['enable_plugins_admin'] = 0;
        $this->setUser();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Plugins cannot be installed on this Matomo');

        $this->api->requestInstall('Barometer');
    }

    public function testDeclinePluginRequestDeclinesAllPendingRequests(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('Barometer');
        $this->api->requestInstall('Barometer');

        $this->setSuperUser();
        self::assertSame(1, $this->api->declinePluginRequest('Barometer', 'Not now'));

        self::assertSame(
            [['Barometer', 'declined', 'Not now', 1]],
            array_map(fn ($decision) => [$decision['plugin_name'], $decision['decision'], $decision['note'], (int) $decision['num_requests']], Db::fetchAll(
                'SELECT plugin_name, decision, note, num_requests FROM ' . Common::prefixTable(RequestHistory::DECISION_TABLE_NAME)
            ))
        );
    }

    /**
     * @dataProvider dataDecisionMethods
     */
    public function testDecisionsRequireSuperUserAccessIfUser(string $method): void
    {
        $this->enablePluginRequests();
        $this->setUser();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->api->$method('Barometer');
    }

    /**
     * @dataProvider dataDecisionMethods
     */
    public function testDecisionsRequireSuperUserAccessIfAnonymous(string $method): void
    {
        $this->enablePluginRequests();
        $this->setAnonymousUser();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->api->$method('Barometer');
    }

    /**
     * @dataProvider dataDecisionMethods
     */
    public function testDecisionsAreRejectedWhileTheFeatureFlagIsOff(string $method): void
    {
        $this->disablePluginRequests();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Plugin requests are not enabled');

        $this->api->$method('Barometer');
    }

    /**
     * @dataProvider dataDecisionMethods
     */
    public function testDecisionsSayWhenThePluginHasNoPendingRequests(string $method): void
    {
        $this->enablePluginRequests();
        $this->service->setOnDownloadCallback(static function () {
            self::fail('A trial was started');
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Marketplace_NoPendingPluginRequests');

        $this->api->$method('PaidPlugin1');
    }

    /**
     * @dataProvider dataRequestsDisabledMethods
     */
    public function testMethodsAreRejectedWhileRequestsAreDisabledInTheConfig(string $method, bool $asSuperUser): void
    {
        $this->enablePluginRequests();
        $asSuperUser ? $this->setSuperUser() : $this->setUser();

        $config = Config::getInstance();
        $general = $config->General;
        $config->General = array_merge($general, ['plugin_trial_request_expiration_in_days' => -1]);

        try {
            $this->api->$method('Barometer');
            self::fail('The method ran while requests are disabled');
        } catch (Exception $e) {
            self::assertSame('Plugin requests are not enabled', $e->getMessage());
        } finally {
            $config->General = $general;
        }
    }

    public function dataRequestsDisabledMethods(): iterable
    {
        yield 'request install' => ['requestInstall', false];
        yield 'decline' => ['declinePluginRequest', true];
        yield 'approve trial' => ['approvePluginTrial', true];
    }

    public function dataDecisionMethods(): iterable
    {
        yield 'decline' => ['declinePluginRequest'];
        yield 'approve trial' => ['approvePluginTrial'];
    }

    public function testApprovePluginTrialApprovesOnceTheTrialStarted(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');
        $this->api->requestTrial('PaidPlugin1');

        $this->setSuperUser();
        $this->service->setOnDownloadCallback(static function () {
            return ['status' => 201, 'headers' => [], 'data' => ''];
        });

        self::assertSame(1, $this->api->approvePluginTrial('PaidPlugin1'));
        self::assertSame(['approved'], $this->getStoredDecisions());
    }

    /**
     * @dataProvider dataFreePluginRequestTypes
     */
    public function testApprovePluginTrialRefusesAFreePluginWithoutStartingATrial(string $requestType): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        // stored directly, as requestTrial() refuses a free plugin, which a request made before it did is not
        (new Storage('Barometer'))->setRequested('Barometer', $requestType);

        $this->setSuperUser();
        $service = $this->service;
        $this->service->setOnDownloadCallback(static function ($action) use ($service) {
            if (str_ends_with($action, '/freeTrial')) {
                self::fail('A trial was started for a free plugin');
            }
            return $service->getFixtureContent('v2.0_plugins_Barometer_info.json');
        });

        try {
            $this->api->approvePluginTrial('Barometer');
            self::fail('A free plugin was approved for a trial');
        } catch (Exception $e) {
            self::assertSame('Marketplace_FreePluginHasNoTrial', $e->getMessage());
        }

        self::assertSame([], $this->getStoredDecisions());
        self::assertTrue((new Storage('Barometer'))->wasRequested());
    }

    public function dataFreePluginRequestTypes(): iterable
    {
        yield 'install request' => [RequestHistory::REQUEST_TYPE_INSTALL];
        yield 'trial request' => [RequestHistory::REQUEST_TYPE_TRIAL];
    }

    /**
     * Matomo before 6.1 approves nothing when it activates a plugin, so its requests stay pending if it still serves
     * after the update, eg during a deployment
     *
     * @dataProvider dataDecisionMethods
     */
    public function testDecisionsApproveTheRequestsOfAnAlreadyActivatedPlugin(string $method): void
    {
        $this->enablePluginRequests();
        $this->setSuperUser();
        StaticContainer::get(RequestHistory::class)->add('Marketplace', 'aUser', time());
        $this->service->setOnDownloadCallback(static function () {
            self::fail('The Marketplace was asked about an activated plugin');
        });

        try {
            $this->api->$method('Marketplace');
            self::fail('A decision was made for an activated plugin');
        } catch (Exception $e) {
            self::assertSame('Marketplace_PluginAlreadyActiveRequestsApproved', $e->getMessage());
        }

        self::assertSame(['approved'], $this->getStoredDecisions());
        self::assertFalse((new Storage('Marketplace'))->hasPendingRequests());
    }

    public function testApprovePluginTrialKeepsTheRequestsPendingWhenTheTrialFails(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');
        $this->api->requestTrial('PaidPlugin1');

        $this->setSuperUser();
        $this->service->setOnDownloadCallback(static function () {
            return ['status' => 500, 'headers' => [], 'data' => ''];
        });

        try {
            $this->api->approvePluginTrial('PaidPlugin1');
            self::fail('A failed trial approved the requests');
        } catch (Exception $e) {
            self::assertSame('Marketplace_TrialStartErrorAPI', $e->getMessage());
        }

        self::assertSame([], $this->getStoredDecisions());
        self::assertTrue((new Storage('PaidPlugin1'))->wasRequested());
    }

    public function testApprovePluginTrialReportsNoFailureWhenAnotherSuperUserDeclinedWhileTheTrialStarted(): void
    {
        $this->enablePluginRequests();
        $this->setUser();
        $this->returnPluginInfo('PaidPlugin1');
        $this->api->requestTrial('PaidPlugin1');

        $this->setSuperUser();
        $this->service->setOnDownloadCallback(static function () {
            (new Storage('PaidPlugin1'))->setDecided(RequestHistory::DECISION_DECLINED);
            Piwik::addAction('Marketplace.pluginRequestDecided', function () {
                self::fail('An approval that ended no request posted the event');
            });
            return ['status' => 201, 'headers' => [], 'data' => ''];
        });

        self::assertSame(0, $this->api->approvePluginTrial('PaidPlugin1'));
        self::assertSame(['declined'], $this->getStoredDecisions());
    }

    public function testSaveLicenseKeyRequiresSuperUserAccessIfUser()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setUser();
        $this->api->saveLicenseKey('key');
    }

    public function testSaveLicenseKeyRequiresSuperUserAccessIfAnonymous()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setAnonymousUser();
        $this->api->saveLicenseKey('key');
    }

    public function testSaveLicenseKeyShouldThrowExceptionIfTokenIsNotValid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Marketplace_ExceptionLinceseKeyIsNotValid');

        $this->service->returnFixture('v2.0_consumer_validate-access_token-notexistingtoken.json');
        $this->api->saveLicenseKey('key');
    }

    public function testSaveLicenseKeyShouldCallTheApiTheCorrectWay()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-valid_but_expired.json');

        try {
            $this->api->saveLicenseKey('key123');
        } catch (Exception $e) {
        }

        // make sure calls API the correct way
        $this->assertSame('consumer/validate', $this->service->action);
        $this->assertSame(array(), $this->service->params);
        $this->assertSame('key123', $this->service->getAccessToken());
        $this->assertNotHasLicenseKey();
    }

    public function testSaveLicenseKeyShouldActuallySaveTokenIfValid()
    {
        $this->service->returnFixture('v2.0_consumer_validate-access_token-consumer1_paid2_custom1.json');
        $success = $this->api->saveLicenseKey('123licensekey');
        $this->assertTrue($success);

        $this->assertHasLicenseKey();
        $this->assertSame('123licensekey', $this->buildLicenseKey()->get());
    }

    public function testSaveLicenseKeyShouldThrowExceptionIfConnectionToMarketplaceFailed()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Host not reachable');

        $this->service->throwException(new ServiceException('Host not reachable', ServiceException::HTTP_ERROR));
        $this->api->saveLicenseKey('123licensekey');
    }

    public function testStartFreeTrialRequiresSuperUserAccessIfUser()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setUser();
        $this->api->startFreeTrial('testPlugin');
    }

    public function testStartFreeTrialRequiresSuperUserAccessIfAnonymous(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setAnonymousUser();
        $this->api->startFreeTrial('testPlugin');
    }

    public function testStartFreeTrialRequiresValidPluginName(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid plugin name given');

        $this->api->startFreeTrial('this/is/not/valid');
    }

    public function testStartFreeTrialShouldThrowExceptionIfMarketplaceRequestEncountersHttpError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Host not reachable');

        $this->service->throwException(new ServiceException('Host not reachable', ServiceException::HTTP_ERROR));
        $this->api->startFreeTrial('testPlugin');
    }

    public function testStartFreeTrialShouldSucceedIfMarketplaceCallsDidNotFail(): void
    {
        $pluginName = 'testPlugin';
        $expectedAction = 'plugins/' . $pluginName . '/freeTrial';

        $this->service->setOnDownloadCallback(static function ($action) use ($expectedAction) {
            self::assertSame($expectedAction, $action);

            return [
                'status' => 201,
                'headers' => [],
                'data' => '',
            ];
        });

        $this->seedInvalidLicensesCache();

        self::assertTrue($this->api->startFreeTrial($pluginName));

        $this->assertInvalidLicensesCacheCleared();
    }

    /**
     * @dataProvider dataStartFreeTrialErrorDownloadResponses
     */
    public function testStartFreeTrialShouldThrowExceptionIfSomethingGoesWrong(
        array $responseInfo,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->service->setOnDownloadCallback(static function () use ($responseInfo) {
            return $responseInfo;
        });

        $this->api->startFreeTrial('testPlugin');
    }

    public function dataStartFreeTrialErrorDownloadResponses(): iterable
    {
        yield 'marketplace response not readable' => [
            [
                'status' => 500,
                'headers' => [],
                'data' => 'not valid json',
            ],
            ServiceException::class,
            'There was an error reading the response from the Marketplace. Please try again later.',
        ];

        yield 'error in marketplace response' => [
            [
                'status' => 400,
                'headers' => [],
                'data' => json_encode(['error' => 'something went wrong']),
            ],
            Exception::class,
            'Marketplace_TrialStartErrorAPI',
        ];

        yield 'unexpected response status code' => [
            [
                'status' => 200,
                'headers' => [],
                'data' => '',
            ],
            Exception::class,
            'Marketplace_TrialStartErrorAPI',
        ];

        yield 'unexpected response content string' => [
            [
                'status' => 201,
                'headers' => [],
                'data' => json_encode('operation successful'),
            ],
            Exception::class,
            'Marketplace_TrialStartErrorAPI',
        ];

        yield 'unexpected response content array' => [
            [
                'status' => 201,
                'headers' => [],
                'data' => json_encode(['success' => true]),
            ],
            Exception::class,
            'Marketplace_TrialStartErrorAPI',
        ];
    }

    public function provideContainerConfig()
    {
        $this->service = new Service();

        return array(
            'Piwik\Access' => new FakeAccess(),
            'Piwik\Plugins\Marketplace\Api\Service' => $this->service,
        );
    }

    protected function setSuperUser()
    {
        FakeAccess::clearAccess(true);
    }

    protected function setUser()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$idSitesView = array(1);
        FakeAccess::$idSitesAdmin = array();
        FakeAccess::$identity = 'aUser';
    }

    protected function setAnonymousUser()
    {
        FakeAccess::clearAccess();
        FakeAccess::$identity = 'anonymous';
    }

    private function enablePluginRequests(): void
    {
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'enabled'];
    }

    /**
     * The local config may turn the flag on, so a test of it being off says so itself
     */
    private function disablePluginRequests(): void
    {
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => 'disabled'];
    }

    private function returnPluginInfo(string $pluginName): void
    {
        $service = $this->service;
        $this->service->setOnDownloadCallback(static function () use ($service, $pluginName) {
            return $service->getFixtureContent('v2.0_plugins_' . $pluginName . '_info.json');
        });
    }

    /**
     * @return list<array{string, string, string|null}> plugin name, request type and note of each stored request
     */
    private function getStoredRequests(): array
    {
        return array_map('array_values', Db::fetchAll(
            'SELECT plugin_name, request_type, message FROM ' . Common::prefixTable(RequestHistory::TABLE_NAME) . ' ORDER BY idrequest'
        ));
    }

    /**
     * @return string[]
     */
    private function getStoredDecisions(): array
    {
        return array_column(Db::fetchAll(
            'SELECT decision FROM ' . Common::prefixTable(RequestHistory::DECISION_TABLE_NAME) . ' ORDER BY iddecision'
        ), 'decision');
    }

    protected function buildLicenseKey()
    {
        return new LicenseKey();
    }

    private function assertHasLicenseKey()
    {
        $this->assertTrue($this->buildLicenseKey()->has());
    }

    private function assertNotHasLicenseKey()
    {
        $this->assertFalse($this->buildLicenseKey()->has());
    }

    private function seedInvalidLicensesCache(): void
    {
        Cache::getEagerCache()->save(self::INVALID_LICENSES_CACHE_KEY, ['exceeded' => ['FooPlugin']]);
    }

    private function assertInvalidLicensesCacheCleared(): void
    {
        $this->assertFalse(Cache::getEagerCache()->contains(self::INVALID_LICENSES_CACHE_KEY));
    }
}
