<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Integration;

use Piwik\Access;
use Piwik\Config;
use Piwik\DI;
use Piwik\FrontController;
use Piwik\Nonce;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\Plugin\MetadataLoader;
use Piwik\Plugins\CorePluginsAdmin\PluginInstaller;
use Piwik\Plugins\CorePluginsAdmin\PluginInstallResults;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\Marketplace\Controller as MarketplaceController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CorePluginsAdmin
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    // an existing plugin, so the result page finds it on disk
    private const UPLOADED_PLUGIN = 'TreemapVisualization';

    /**
     * @var array<string, mixed>
     */
    private $originalGet = [];

    /**
     * @var array<string, mixed>
     */
    private $originalPost = [];

    /**
     * @var string
     */
    private $uploadedZip;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        $this->originalGet = $_GET;
        $this->originalPost = $_POST;

        // the action only checks the upload exists, the mocked installer never reads it
        $this->uploadedZip = tempnam(sys_get_temp_dir(), 'pluginZip');

        Config::getInstance()->General['enable_plugin_upload'] = 1;

        // the result page renders a full admin page, whose menu resolves a site
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
        unset($_FILES['pluginZip']);

        if (is_file($this->uploadedZip)) {
            unlink($this->uploadedZip);
        }

        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testUploadPluginRedirectsToTheResultPageInsteadOfRenderingIt()
    {
        $redirectUrl = $this->uploadPluginUntilRedirect();
        parse_str((string) parse_url($redirectUrl, PHP_URL_QUERY), $query);

        self::assertSame('CorePluginsAdmin', $query['module']);
        self::assertSame('uploadPluginResult', $query['action']);
        self::assertArrayNotHasKey('nonce', $query);
        self::assertSame(
            ['plugins' => [self::UPLOADED_PLUGIN]],
            PluginInstallResults::get($query[PluginInstallResults::TOKEN_PARAMETER])
        );
    }

    public function testUploadPluginResultShowsTheVersionInstalledOnDisk()
    {
        parse_str((string) parse_url($this->uploadPluginUntilRedirect(), PHP_URL_QUERY), $query);
        $_GET = $query;

        $response = Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('CorePluginsAdmin', 'uploadPluginResult');
        });

        $installedVersion = (new MetadataLoader(self::UPLOADED_PLUGIN))->load()['version'];
        self::assertStringContainsString(
            Piwik::translate('Marketplace_StepPluginSuccessfullyInstalled', [self::UPLOADED_PLUGIN, $installedVersion]),
            $response
        );
    }

    public function testUploadPluginLeavesASuccessSummaryUntilTheResultPageShowsIt()
    {
        parse_str((string) parse_url($this->uploadPluginUntilRedirect(), PHP_URL_QUERY), $query);

        $installedVersion = (new MetadataLoader(self::UPLOADED_PLUGIN))->load()['version'];
        $successMessage = Piwik::translate('Marketplace_StepPluginSuccessfullyInstalled', [self::UPLOADED_PLUGIN, $installedVersion]);

        // what the "Database upgrade required" screen does before it continues to the dashboard
        NotificationManager::cancelAllNonPersistent();
        self::assertContains($successMessage, $this->getNotificationMessages());

        $_GET = $query;
        $response = Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('CorePluginsAdmin', 'uploadPluginResult');
        });

        self::assertSame(1, substr_count($response, $successMessage));
        self::assertNotContains($successMessage, $this->getNotificationMessages());
    }

    /**
     * @return string[]
     */
    private function getNotificationMessages(): array
    {
        return array_values(array_map(static function ($notification) {
            return $notification->message;
        }, NotificationManager::getAllNotificationsToDisplay()));
    }

    public function testUploadPluginResultRedirectsToThePluginsPageForAnUnknownToken()
    {
        $_GET = [
            'module' => 'CorePluginsAdmin',
            'action' => 'uploadPluginResult',
            PluginInstallResults::TOKEN_PARAMETER => 'unknownToken',
        ];

        $redirectUrl = $this->dispatchUntilRedirect('uploadPluginResult');

        self::assertStringContainsString('module=CorePluginsAdmin&action=plugins', $redirectUrl);
    }

    private function uploadPluginUntilRedirect(): string
    {
        $_GET = [
            'module' => 'CorePluginsAdmin',
            'action' => 'uploadPlugin',
            'nonce' => Nonce::getNonce(MarketplaceController::INSTALL_NONCE),
        ];
        $_POST = ['confirmPassword' => 'password'];
        $_FILES['pluginZip'] = ['tmp_name' => $this->uploadedZip, 'error' => UPLOAD_ERR_OK];

        return $this->dispatchUntilRedirect('uploadPlugin');
    }

    /**
     * @return string the URL the action redirects to
     */
    private function dispatchUntilRedirect(string $action): string
    {
        // a redirect is raised as an exception on the CLI, which leaves fetchDispatch()'s output buffer open
        $outputBufferLevel = ob_get_level();

        try {
            Access::doAsSuperUser(function () use ($action) {
                FrontController::getInstance()->fetchDispatch('CorePluginsAdmin', $action);
            });
        } catch (\Exception $e) {
            if (!preg_match('/would redirect you to this URL: (\S+)/', $e->getMessage(), $matches)) {
                throw $e;
            }

            return $matches[1];
        } finally {
            while (ob_get_level() > $outputBufferLevel) {
                ob_end_clean();
            }
        }

        self::fail(sprintf('The "%s" action did not redirect', $action));
    }

    public function provideContainerConfig()
    {
        $installer = $this->createMock(PluginInstaller::class);
        $installer->method('installOrUpdatePluginFromFile')->willReturn(
            (object) ['name' => self::UPLOADED_PLUGIN, 'version' => '0.0.1']
        );

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier->method('isPasswordCorrect')->willReturn(true);

        return [
            PluginInstaller::class => DI::value($installer),
            PasswordVerifier::class => DI::value($passwordVerifier),
        ];
    }
}
