<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Integration;

use Piwik\Config;
use Piwik\Nonce;
use Piwik\Plugins\CoreUpdater\Controller;
use Piwik\Plugins\CoreUpdater\Updater;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 * @group CoreUpdater
 */
class ControllerTest extends IntegrationTestCase
{
    private $originalGet = [];
    private $originalPost = [];
    private $originalEnableAutoUpdate;
    private $originalEnableInternetFeatures;
    private $originalUpdateDetailsToken;
    private $hadEnableAutoUpdate = false;
    private $hadEnableInternetFeatures = false;
    private $hadUpdateDetailsToken = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalGet = $_GET;
        $this->originalPost = $_POST;

        $general = Config::getInstance()->General;
        $this->hadEnableAutoUpdate = array_key_exists('enable_auto_update', $general);
        $this->hadEnableInternetFeatures = array_key_exists('enable_internet_features', $general);
        $this->hadUpdateDetailsToken = array_key_exists('update_details_token', $general);
        $this->originalEnableAutoUpdate = $general['enable_auto_update'] ?? null;
        $this->originalEnableInternetFeatures = $general['enable_internet_features'] ?? null;
        $this->originalUpdateDetailsToken = $general['update_details_token'] ?? null;

        Config::getInstance()->General['enable_auto_update'] = 1;
        Config::getInstance()->General['enable_internet_features'] = 1;
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;

        FakeAccess::clearAccess();

        $config = Config::getInstance();
        if ($this->hadEnableAutoUpdate) {
            $config->General['enable_auto_update'] = $this->originalEnableAutoUpdate;
        } else {
            unset($config->General['enable_auto_update']);
        }

        if ($this->hadEnableInternetFeatures) {
            $config->General['enable_internet_features'] = $this->originalEnableInternetFeatures;
        } else {
            unset($config->General['enable_internet_features']);
        }

        if ($this->hadUpdateDetailsToken) {
            $config->General['update_details_token'] = $this->originalUpdateDetailsToken;
        } else {
            unset($config->General['update_details_token']);
        }

        $config->forceSave();

        parent::tearDown();
    }

    public function testOneClickResultsDoesNotCreateOrExposeTokenForAnonymousRequest()
    {
        FakeAccess::clearAccess(false);
        unset(Config::getInstance()->General['update_details_token']);

        $_GET = [];
        $_POST = [];

        $result = $this->buildController()->oneClickResults();

        self::assertStringNotContainsString('updateDetailsToken=', $result);
        self::assertEmpty(Config::getInstance()->General['update_details_token'] ?? null);
    }

    public function testOneClickResultsOnlyExposesStoredTokenForSuperUserContext()
    {
        $token = '1234567890abcdef1234567890abcdef';
        Config::getInstance()->General['update_details_token'] = $token;
        Config::getInstance()->forceSave();

        $_GET = [];
        $_POST = [];

        FakeAccess::clearAccess(true);
        $superUserResult = $this->buildController()->oneClickResults();
        self::assertStringContainsString('updateDetailsToken=' . urlencode($token), $superUserResult);

        FakeAccess::clearAccess(false);
        $anonymousResult = $this->buildController()->oneClickResults();
        self::assertStringNotContainsString('updateDetailsToken=' . urlencode($token), $anonymousResult);
    }

    public function testOneClickResultsCreatesAndExposesTokenForSuperUserWhenMissing()
    {
        FakeAccess::clearAccess(true);
        unset(Config::getInstance()->General['update_details_token']);

        $_GET = [];
        $_POST = [];

        $result = $this->buildController()->oneClickResults();
        $createdToken = Config::getInstance()->General['update_details_token'] ?? null;

        self::assertNotEmpty($createdToken);
        self::assertStringContainsString('updateDetailsToken=' . urlencode($createdToken), $result);
    }

    public function testOneClickUpdateRotatesStoredUpdateDetailsTokenAfterSuccessfulUpdate()
    {
        FakeAccess::clearAccess(true);
        Config::getInstance()->General['update_details_token'] = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

        $_GET = [];
        $_POST = [];
        $_GET['nonce'] = Nonce::getNonce('oneClickUpdate');

        ob_start();
        try {
            $this->buildController()->oneClickUpdate();
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $rotatedToken = Config::getInstance()->General['update_details_token'] ?? '';

        self::assertStringContainsString('action=oneClickResults', $output);
        self::assertNotSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $rotatedToken);
        self::assertSame(1, preg_match('/^[a-f0-9]{32}$/', $rotatedToken));
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }

    private function buildController(): Controller
    {
        $updater = $this->createMock(Updater::class);
        $updater->method('updatePiwik')->willReturn(array());

        return new Controller($updater);
    }
}
