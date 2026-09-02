<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Login\Login;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Plugins\Login\SystemSettings;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group NoAccessTest
 * @group Plugins
 */
class NoAccessTest extends IntegrationTestCase
{
    private const MAX_ALLOWED_DISPATCHES = 3;

    private int $dispatchCount = 0;

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatchCount = 0;

        // makes sure a broken guard fails the test instead of recursing until the memory limit is reached
        Piwik::addAction('Request.dispatch', function () {
            $this->dispatchCount++;

            if ($this->dispatchCount > self::MAX_ALLOWED_DISPATCHES) {
                throw new \RuntimeException('endless dispatch recursion detected');
            }
        });
    }

    public function tearDown(): void
    {
        unset($_POST['form_login']);

        parent::tearDown();
    }

    public function testNoAccessDoesNotRecurseWhenLoginPageRaisesAnotherNoAccessException()
    {
        Piwik::addAction('Controller.Login.login', function () {
            throw new NoAccessException('login page not allowed');
        });

        try {
            $this->getLoginPlugin()->noAccess(new NoAccessException('initial no access'));
            $this->fail('expected a NoAccessException to be thrown');
        } catch (NoAccessException $e) {
            $this->assertSame('login page not allowed', $e->getMessage());
        }

        $this->assertSame(1, $this->dispatchCount);
    }

    public function testNoAccessStillHandlesFurtherExceptionsAfterRethrowingOne()
    {
        Piwik::addAction('Controller.Login.login', function () {
            throw new NoAccessException('login page not allowed');
        });

        $plugin = $this->getLoginPlugin();

        for ($i = 0; $i < 2; $i++) {
            try {
                $plugin->noAccess(new NoAccessException('initial no access'));
                $this->fail('expected a NoAccessException to be thrown');
            } catch (NoAccessException $e) {
                $this->assertSame('login page not allowed', $e->getMessage());
            }
        }

        $this->assertSame(2, $this->dispatchCount);
    }

    public function testDispatchDoesNotRecurseWhenTheUsedLoginIsBlocked()
    {
        $login = 'bruteforced';
        // needs to be the instance used while dispatching, as the test config resets the recorded attempts
        // when it is created
        $bruteForce = StaticContainer::get(BruteForceDetection::class);

        $settings = StaticContainer::get(SystemSettings::class);
        $attempts = max(
            BruteForceDetection::OVERALL_LOGIN_LOCKOUT_THRESHOLD_MIN,
            $settings->maxFailedLoginsPerMinutes->getValue() * 3
        ) + 1;

        // enough failed attempts from other IPs to block the login, but not the IP the request is made from
        for ($i = 1; $i <= $attempts; $i++) {
            $bruteForce->addFailedAttempt('203.0.113.' . $i, $login);
        }

        $_POST['form_login'] = $login;

        try {
            FrontController::getInstance()->dispatch('Login', 'login');
            $this->fail('expected a NoAccessException to be thrown');
        } catch (NoAccessException $e) {
            $this->assertStringContainsString('LoginNotAllowedBecauseUserLoginBlocked', $e->getMessage());
        }

        $this->assertSame(2, $this->dispatchCount);
    }

    private function getLoginPlugin(): Login
    {
        /** @var Login $plugin */
        $plugin = Manager::getInstance()->getLoadedPlugin('Login');

        return $plugin;
    }
}
