<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\DI;
use Piwik\Exception\NoPrivilegesException;
use Piwik\IP;
use Piwik\Plugins\Login\SecondFactorVerifier;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Plugins\Login\SystemSettings;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group SecondFactorVerifierTest
 * @group Plugins
 */
class SecondFactorVerifierTest extends IntegrationTestCase
{
    private const USER_WITH_SECOND_FACTOR = 'userWithSecondFactor';
    private const USER_WITHOUT_SECOND_FACTOR = 'userWithoutSecondFactor';
    private const VALID_CODE = 'valid-code';

    private SecondFactorVerifier $verifier;

    private BruteForceDetection $bruteForceDetection;

    /**
     * @var string[]
     */
    private array $verifiedCodes = [];

    public function setUp(): void
    {
        parent::setUp();

        \Zend_Session::$_unitTestEnabled = true;
        $_SESSION = [];

        FakeAccess::$superUser = false;
        FakeAccess::$identity = self::USER_WITH_SECOND_FACTOR;

        $this->verifier = new SecondFactorVerifier();
        $this->verifier->setDisableRedirect();

        $this->bruteForceDetection = StaticContainer::get(BruteForceDetection::class);
        $this->bruteForceDetection->deleteAll();
    }

    public function tearDown(): void
    {
        $this->bruteForceDetection->deleteAll();
        $_SESSION = [];
        unset($_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER]);

        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            // a second factor provider, as e.g. TwoFactorAuth implements it
            'observers.global' => DI::add([
                ['Login.userRequiresSecondFactorConfirmation', DI::value(function (bool &$required, string $login): void {
                    if ($login !== self::USER_WITHOUT_SECOND_FACTOR) {
                        $required = true;
                    }
                })],
                ['Login.verifySecondFactor', DI::value(function (bool &$isValid, string $login, string $code): void {
                    $this->verifiedCodes[] = $code;
                    if ($login !== self::USER_WITHOUT_SECOND_FACTOR && $code === self::VALID_CODE) {
                        $isValid = true;
                    }
                })],
            ]),
        ];
    }

    public function testRequireSecondFactorConfirmedPassesWithoutConfirmationForUsersWithoutSecondFactor(): void
    {
        FakeAccess::$identity = self::USER_WITHOUT_SECOND_FACTOR;

        $this->assertFalse($this->verifier->isSecondFactorRequired(self::USER_WITHOUT_SECOND_FACTOR));
        $this->assertTrue($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
    }

    public function testRequireSecondFactorConfirmedRedirectsToTheConfirmationOfANewRequest(): void
    {
        $this->assertTrue($this->verifier->isSecondFactorRequired(self::USER_WITH_SECOND_FACTOR));

        $verifier = new SecondFactorVerifier();
        $message = '';

        try {
            $verifier->requireSecondFactorConfirmed($this->getActionParams());
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertMatchesRegularExpression(
            '/action=confirmSecondFactor&secondFactorRequest=[0-9a-f]{32}/',
            $message,
            'Expected a redirect to the confirmation page'
        );
        preg_match('/secondFactorRequest=([0-9a-f]{32})/', $message, $matches);
        $this->assertTrue($verifier->hasConfirmationBeenRequested($matches[1]));
    }

    public function testConfirmationPageFallsBackToLoginWhenTheLoginPluginInUseDoesNotProvideIt(): void
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('getName')->willReturn('LoginPluginWithoutConfirmationPage');
        StaticContainer::getContainer()->set(Auth::class, $auth);

        $message = '';
        try {
            (new SecondFactorVerifier())->requireSecondFactorConfirmed($this->getActionParams());
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertStringContainsString('module=Login&action=confirmSecondFactor', $message);
    }

    public function testConfirmationPageIsShownByThePluginOfTheActionWhenLoginIsNotActivated(): void
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('getName')->willReturn('Login');
        StaticContainer::getContainer()->set(Auth::class, $auth);

        $verifier = new class extends SecondFactorVerifier {
            protected function isPluginActivated(string $plugin): bool
            {
                return $plugin !== 'Login';
            }
        };

        $message = '';
        try {
            // TwoFactorAuth's controller provides the confirmation page
            $verifier->requireSecondFactorConfirmed(['module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertStringContainsString('module=TwoFactorAuth&action=confirmSecondFactor', $message);
    }

    public function testRequireSecondFactorConfirmedFailsWithoutConfirmation(): void
    {
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
    }

    public function testConfirmSecondFactorFailsForUnknownRequest(): void
    {
        $this->verifier->requestConfirmation($this->getActionParams());

        $this->assertFalse($this->verifier->hasConfirmationBeenRequested('unknown'));
        $this->assertNull($this->verifier->confirmSecondFactor('unknown', self::VALID_CODE));
        $this->assertSame([], $this->verifiedCodes);
    }

    public function testConfirmSecondFactorRejectsInvalidCodeAndRecordsFailedAttempt(): void
    {
        $requestId = $this->verifier->requestConfirmation($this->getActionParams());

        $this->assertNull($this->verifier->confirmSecondFactor($requestId, 'wrong-code'));
        $this->assertTrue($this->verifier->hasConfirmationBeenRequested($requestId));

        $attempts = $this->bruteForceDetection->getAll();
        $this->assertCount(1, $attempts);
        $this->assertSame(self::USER_WITH_SECOND_FACTOR, $attempts[0]['login']);
    }

    public function testConfirmSecondFactorRedirectsToTheActionWithAConfirmationToken(): void
    {
        $requestId = $this->verifier->requestConfirmation(['module' => 'MyPlugin', 'action' => 'myAction', 'value' => 'a&b=c']);

        $url = $this->verifier->confirmSecondFactor($requestId, self::VALID_CODE);

        $this->assertStringStartsWith('index.php?', (string) $url);
        $this->assertStringContainsString('module=MyPlugin&action=myAction', (string) $url);
        // values are encoded so they cannot add parameters
        $this->assertStringContainsString('value=a%26b%3Dc', (string) $url);
        $this->assertMatchesRegularExpression('/secondFactorToken=[0-9a-f]{32}/', (string) $url);
        $this->assertFalse($this->verifier->hasConfirmationBeenRequested($requestId));
    }

    public function testConfirmationCanBeUsedOnlyOnce(): void
    {
        $this->confirm($this->getActionParams());

        $this->assertTrue($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
    }

    public function testConfirmationRequiresItsToken(): void
    {
        $this->confirm($this->getActionParams());

        $_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER] = str_repeat('0', 32);
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));

        unset($_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER]);
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
    }

    public function testConfirmingOneRequestDoesNotConfirmAnotherRequestInTheSameSession(): void
    {
        $otherParams = ['module' => 'MyPlugin', 'action' => 'otherAction'];
        $otherRequestId = $this->verifier->requestConfirmation($otherParams);

        $this->confirm($this->getActionParams());

        $this->assertTrue($this->verifier->hasConfirmationBeenRequested($otherRequestId));
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($otherParams));
    }

    public function testConfirmationIsOnlyValidForTheRequestedAction(): void
    {
        $this->confirm($this->getActionParams());

        $this->assertFalse($this->verifier->requireSecondFactorConfirmed(['module' => 'MyPlugin', 'action' => 'otherAction']));
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed(['module' => 'OtherPlugin', 'action' => 'myAction']));
    }

    public function testConfirmationIsOnlyValidForTheUserWhoConfirmed(): void
    {
        $this->confirm($this->getActionParams());

        FakeAccess::$identity = 'otherUser';
        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
    }

    public function testConfirmationExpires(): void
    {
        $this->confirm($this->getActionParams());

        $this->verifier->setNow(Date::now()->addPeriod(SecondFactorVerifier::CONFIRMATION_VALID_FOR_MINUTES + 1, 'minute'));

        $this->assertFalse($this->verifier->requireSecondFactorConfirmed($this->getActionParams()));
    }

    public function testRequestExpires(): void
    {
        $requestId = $this->verifier->requestConfirmation($this->getActionParams());

        $this->verifier->setNow(Date::now()->addPeriod(SecondFactorVerifier::REQUEST_VALID_FOR_MINUTES + 1, 'minute'));

        $this->assertFalse($this->verifier->hasConfirmationBeenRequested($requestId));
        $this->assertNull($this->verifier->confirmSecondFactor($requestId, self::VALID_CODE));
    }

    public function testRequestIsOnlyValidForTheUserWhoRequestedIt(): void
    {
        $requestId = $this->verifier->requestConfirmation($this->getActionParams());

        FakeAccess::$identity = 'otherUser';

        $this->assertFalse($this->verifier->hasConfirmationBeenRequested($requestId));
        $this->assertNull($this->verifier->confirmSecondFactor($requestId, self::VALID_CODE));
    }

    public function testConfirmSecondFactorIsRefusedWhenIpIsBlocked(): void
    {
        $requestId = $this->verifier->requestConfirmation($this->getActionParams());

        $ip = IP::getIpFromHeader();
        $attempts = (new SystemSettings())->maxFailedLoginsPerMinutes->getValue() + 1;
        for ($i = 0; $i < $attempts; $i++) {
            $this->bruteForceDetection->addFailedAttempt($ip);
        }

        $this->expectException(NoPrivilegesException::class);
        $this->expectExceptionMessage('Login_LoginNotAllowedBecauseBlocked');

        $this->verifier->confirmSecondFactor($requestId, self::VALID_CODE);
    }

    public function testConfirmSecondFactorIsRefusedWhenUserLoginIsBlocked(): void
    {
        $requestId = $this->verifier->requestConfirmation($this->getActionParams());

        // spread over IPs, so only the user login is blocked
        $attempts = max(
            BruteForceDetection::OVERALL_LOGIN_LOCKOUT_THRESHOLD_MIN,
            (new SystemSettings())->maxFailedLoginsPerMinutes->getValue() * 3
        ) + 1;
        for ($i = 1; $i <= $attempts; $i++) {
            $this->bruteForceDetection->addFailedAttempt('10.0.0.' . $i, self::USER_WITH_SECOND_FACTOR);
        }

        $this->expectException(NoPrivilegesException::class);
        $this->expectExceptionMessage('Login_LoginNotAllowedBecauseUserLoginBlocked');

        $this->verifier->confirmSecondFactor($requestId, self::VALID_CODE);
    }

    public function testConfirmSecondFactorNeitherChecksNorRecordsAttemptsWhenBruteForceDetectionIsDisabled(): void
    {
        $settings = new SystemSettings();
        $this->setBruteForceDetectionEnabled($settings, false);

        try {
            $requestId = $this->verifier->requestConfirmation($this->getActionParams());

            // would block the IP if the detection was enabled
            $ip = IP::getIpFromHeader();
            $attempts = $settings->maxFailedLoginsPerMinutes->getValue() + 1;
            for ($i = 0; $i < $attempts; $i++) {
                $this->bruteForceDetection->addFailedAttempt($ip);
            }

            $this->assertNull($this->verifier->confirmSecondFactor($requestId, 'wrong-code'));
            $this->assertCount($attempts, $this->bruteForceDetection->getAll());
        } finally {
            $this->setBruteForceDetectionEnabled($settings, true);
        }
    }

    public function testPendingRequestsAreCapped(): void
    {
        $firstRequestId = $this->verifier->requestConfirmation($this->getActionParams());
        for ($i = 0; $i < 10; $i++) {
            $lastRequestId = $this->verifier->requestConfirmation($this->getActionParams());
        }

        $this->assertFalse($this->verifier->hasConfirmationBeenRequested($firstRequestId));
        $this->assertTrue($this->verifier->hasConfirmationBeenRequested($lastRequestId));
    }

    private function setBruteForceDetectionEnabled(SystemSettings $settings, bool $enabled): void
    {
        FakeAccess::$superUser = true;
        $settings->enableBruteForceDetection->setValue($enabled);
        $settings->save();
        FakeAccess::$superUser = false;
    }

    /**
     * Confirms the given action and puts the confirmation token into the request, as the redirect back does.
     *
     * @param array<string, string> $params
     */
    private function confirm(array $params): void
    {
        $requestId = $this->verifier->requestConfirmation($params);
        $url = $this->verifier->confirmSecondFactor($requestId, self::VALID_CODE);
        $this->assertNotNull($url);

        parse_str((string) parse_url((string) $url, PHP_URL_QUERY), $query);
        $_GET[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER] = $query[SecondFactorVerifier::CONFIRMATION_TOKEN_PARAMETER];
    }

    /**
     * @return array<string, string>
     */
    private function getActionParams(): array
    {
        return ['module' => 'MyPlugin', 'action' => 'myAction', 'nonce' => 'abc'];
    }
}
