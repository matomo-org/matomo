<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Changes\Model as ChangesModel;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Mail\OAuth\AccessTokenProvider;
use Piwik\Plugins\CoreAdminHome\CoreAdminHome;
use Piwik\Plugins\CoreAdminHome\Controller;
use Piwik\Plugins\CoreAdminHome\OptOutManager;
use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translation\Loader\DevelopmentLoader;
use Piwik\Translation\Loader\JsonFileLoader;
use Piwik\Translation\Translator;

/**
 * @group CoreAdminHome
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /** @var Controller */
    private $controller;
    /** @var array */
    private $backupGet;
    /** @var array */
    private $backupPost;
    /** @var array */
    private $backupRequest;
    /** @var array */
    private $backupServer;
    /** @var array */
    private $backupMail;
    /** @var array */
    private $backupGeneral;
    /** @var bool */
    private $mailSettingsSaved = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupPost = $_POST;
        $this->backupRequest = $_REQUEST;
        $this->backupServer = $_SERVER;
        $this->backupMail = Config::getInstance()->mail;
        $this->backupGeneral = Config::getInstance()->General;

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }
        FakeAccess::clearAccess(
            $superUser = true,
            $idSitesAdmin = [1],
            $idSitesView = [1],
            $identity = 'superUserLogin'
        );
        Fixture::resetTranslations();
        Fixture::loadAllTranslations();
        if (!Fixture::siteCreated(2)) {
            Fixture::createWebsite('2012-01-02 00:00:00');
        }
        $_GET = ['idSite' => 1, 'period' => 'day', 'date' => 'today'];
        $_REQUEST = $_GET;

        UsersManagerAPI::getInstance()->setUserPreference(
            'superUserLogin',
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            2
        );

        $this->controller = new Controller(
            new Translator(new DevelopmentLoader(new JsonFileLoader())),
            new OptOutManager(),
            new PasswordVerifier()
        );
    }

    public function testSetMailSettingsRejectsGetRequests(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = $this->getMailSettingsRequest('get.example.test');
        $_POST = [];
        $_REQUEST = $_GET;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid HTTP method.');

        $this->controller->setMailSettings();
    }

    public function testSetMailSettingsUsesPostParametersOnly(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = $this->getMailSettingsRequest('get.example.test');
        $_POST = $this->getMailSettingsRequest('post.example.test');
        $_REQUEST = $_GET + $_POST;

        $controller = $this->getControllerSkippingTokenCheck();

        $controller->setMailSettings();
        $this->mailSettingsSaved = true;

        $this->assertSame('post.example.test', Config::getInstance()->mail['host']);
        $this->assertSame('smtp-user-post', Config::getInstance()->mail['username']);
        $this->assertSame('smtp-password-post', Config::getInstance()->mail['password']);
        $this->assertSame('mail-post@example.test', Config::getInstance()->General['noreply_email_address']);
        $this->assertSame('Acme <Team> & "post"', Config::getInstance()->General['noreply_email_name']);
    }

    public function testSetMailSettingsSavesTheOAuthSettings(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $this->getOAuthMailSettingsRequest();

        $this->getControllerSkippingTokenCheck()->setMailSettings();
        $this->mailSettingsSaved = true;

        $mailConfig = Config::getInstance()->mail;
        $this->assertSame('Xoauth2', $mailConfig['type']);
        $this->assertSame('https://login.example.test/token', $mailConfig['oauth_token_url']);
        $this->assertSame('the-client-id', $mailConfig['oauth_client_id']);
        $this->assertSame('the-client-secret', $mailConfig['oauth_client_secret']);
        $this->assertSame('https://mail.example.test/.default', $mailConfig['oauth_scope']);
    }

    public function testSetMailSettingsKeepsTheOAuthClientSecretWhenItIsNotPosted(): void
    {
        $this->setStoredOAuthSettings();

        $request = $this->getOAuthMailSettingsRequest();
        unset($request['mailOAuthClientSecret']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $request;

        $this->getControllerSkippingTokenCheck()->setMailSettings();
        $this->mailSettingsSaved = true;

        $this->assertSame('the-stored-secret', Config::getInstance()->mail['oauth_client_secret']);
    }

    /**
     * @dataProvider getSettingsTheOAuthClientSecretIsTiedTo
     */
    public function testSetMailSettingsDoesNotKeepTheOAuthClientSecretWhenASettingItIsTiedToChanges(
        string $parameter,
        string $value
    ): void {
        $this->setStoredOAuthSettings();

        $request = $this->getOAuthMailSettingsRequest();
        $request[$parameter] = $value;
        unset($request['mailOAuthClientSecret']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $request;

        $response = json_decode($this->getControllerSkippingTokenCheck()->setMailSettings(), true);

        $this->assertSame('error', $response['result']);
        $this->assertSame('the-stored-secret', Config::getInstance()->mail['oauth_client_secret']);
        $this->assertSame('https://login.example.test/token', Config::getInstance()->mail['oauth_token_url']);
        $this->assertSame('post.example.test', Config::getInstance()->mail['host']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function getSettingsTheOAuthClientSecretIsTiedTo(): iterable
    {
        yield 'SMTP host' => ['mailHost', 'another.example.test'];
        yield 'token URL' => ['mailOAuthTokenUrl', 'https://another.example.test/token'];
        yield 'client ID' => ['mailOAuthClientId', 'another-client-id'];
        yield 'scope' => ['mailOAuthScope', 'https://another.example.test/.default'];
    }

    public function testSetMailSettingsForgetsTheCachedOAuthAccessToken(): void
    {
        $provider = $this->createMock(AccessTokenProvider::class);
        $provider->expects($this->once())->method('forgetAccessToken');
        StaticContainer::getContainer()->set(AccessTokenProvider::class, $provider);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $this->getOAuthMailSettingsRequest();

        $this->getControllerSkippingTokenCheck()->setMailSettings();
        $this->mailSettingsSaved = true;
    }

    /**
     * @dataProvider getInvalidOAuthMailSettings
     */
    public function testSetMailSettingsRejectsInvalidOAuthSettings(array $settings, string $expectedMessage): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $settings + $this->getOAuthMailSettingsRequest();

        $response = json_decode($this->getControllerSkippingTokenCheck()->setMailSettings(), true);

        $this->assertSame('error', $response['result']);
        $this->assertStringStartsWith($expectedMessage, $response['message']);
        $this->assertSame($this->backupMail, Config::getInstance()->mail);
    }

    public function getInvalidOAuthMailSettings(): iterable
    {
        $incomplete = 'To use OAuth 2.0, please enter the SMTP username, the token URL, the client ID and the client secret.';

        yield 'no username' => [['mailUsername' => ''], $incomplete];
        yield 'no token URL' => [['mailOAuthTokenUrl' => ''], $incomplete];
        yield 'no client ID' => [['mailOAuthClientId' => ''], $incomplete];
        yield 'no client secret' => [['mailOAuthClientSecret' => ''], $incomplete];
        $notValid = 'The OAuth 2.0 token URL has to be a valid URL that starts with https://';

        yield 'token URL is not https' => [['mailOAuthTokenUrl' => 'http://login.example.test/token'], $notValid];
        yield 'token URL is only the scheme' => [['mailOAuthTokenUrl' => 'https://'], $notValid];
        yield 'token URL has no host' => [['mailOAuthTokenUrl' => 'https:///token'], $notValid];
        yield 'token URL contains a space' => [['mailOAuthTokenUrl' => 'https://login example.test/token'], $notValid];
    }

    public function testSetMailSettingsKeepsTheCertificateSettingsFromTheConfigFile(): void
    {
        $certificateSettings = [
            'oauth_credential_type'      => 'certificate',
            'oauth_certificate_path'     => '/etc/matomo/smtp.crt',
            'oauth_private_key_path'     => '/etc/matomo/smtp.key',
            'oauth_private_key_password' => 'the-key-password',
        ];
        $this->setStoredOAuthSettings();
        Config::getInstance()->mail = $certificateSettings + ['oauth_client_secret' => ''] + Config::getInstance()->mail;

        $request = $this->getOAuthMailSettingsRequest();
        unset($request['mailOAuthClientSecret']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $request;

        $response = json_decode($this->getControllerSkippingTokenCheck()->setMailSettings(), true);
        $this->mailSettingsSaved = true;

        $this->assertSame('success', $response['result']);
        $this->assertSame($certificateSettings, array_intersect_key(Config::getInstance()->mail, $certificateSettings));
    }

    public function testSetMailSettingsDoesNotKeepTheCertificateWhenASettingItIsTiedToChanges(): void
    {
        $this->setStoredOAuthSettings();
        Config::getInstance()->mail = [
            'oauth_certificate_path'     => '/etc/matomo/smtp.crt',
            'oauth_private_key_path'     => '/etc/matomo/smtp.key',
            'oauth_private_key_password' => 'the-key-password',
        ] + Config::getInstance()->mail;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['mailOAuthTokenUrl' => 'https://another.example.test/token'] + $this->getOAuthMailSettingsRequest();

        $this->getControllerSkippingTokenCheck()->setMailSettings();
        $this->mailSettingsSaved = true;

        $mailConfig = Config::getInstance()->mail;
        $this->assertSame('https://another.example.test/token', $mailConfig['oauth_token_url']);
        $this->assertSame('', $mailConfig['oauth_certificate_path']);
        $this->assertSame('', $mailConfig['oauth_private_key_path']);
        $this->assertSame('', $mailConfig['oauth_private_key_password']);
    }

    /**
     * @dataProvider getCredentialTypesToSwitchTo
     */
    public function testSetMailSettingsSavesTheCredentialTypeChosenInTheForm(string $storedType, string $postedType): void
    {
        $this->setStoredOAuthSettings();
        Config::getInstance()->mail = [
            'oauth_credential_type'  => $storedType,
            'oauth_certificate_path' => '/etc/matomo/smtp.crt',
        ] + Config::getInstance()->mail;

        $request = ['mailOAuthCredentialType' => $postedType] + $this->getOAuthMailSettingsRequest();
        unset($request['mailOAuthClientSecret']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $request;

        $response = json_decode($this->getControllerSkippingTokenCheck()->setMailSettings(), true);
        $this->mailSettingsSaved = true;

        $this->assertSame('success', $response['result']);
        $this->assertSame(strtolower($postedType), Config::getInstance()->mail['oauth_credential_type']);
        $this->assertSame('/etc/matomo/smtp.crt', Config::getInstance()->mail['oauth_certificate_path']);
    }

    public function getCredentialTypesToSwitchTo(): iterable
    {
        yield 'secret to certificate' => ['secret', 'certificate'];
        yield 'certificate to secret' => ['certificate', 'secret'];
        yield 'any case' => ['secret', 'Certificate'];
    }

    public function testSetMailSettingsKeepsTheStoredCredentialTypeForAnUnknownOne(): void
    {
        $this->setStoredOAuthSettings();
        Config::getInstance()->mail = [
            'oauth_credential_type'  => 'certificate',
            'oauth_certificate_path' => '/etc/matomo/smtp.crt',
        ] + Config::getInstance()->mail;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['mailOAuthCredentialType' => 'unknown'] + $this->getOAuthMailSettingsRequest();

        $this->getControllerSkippingTokenCheck()->setMailSettings();
        $this->mailSettingsSaved = true;

        $this->assertSame('certificate', Config::getInstance()->mail['oauth_credential_type']);
    }

    public function testSetMailSettingsNeverTakesTheCertificateSettingsFromTheRequest(): void
    {
        $this->setStoredOAuthSettings();
        Config::getInstance()->mail = [
            'oauth_certificate_path'     => '/etc/matomo/smtp.crt',
            'oauth_private_key_path'     => '',
            'oauth_private_key_password' => '',
        ] + Config::getInstance()->mail;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'mailOAuthCredentialType'    => 'certificate',
            'mailOAuthCertificatePath'   => '/etc/ssl/certs/server.pem',
            'mailOAuthPrivateKeyPath'    => '/etc/ssl/private/server.key',
            'oauth_certificate_path'     => '/etc/ssl/certs/server.pem',
            'oauth_private_key_password' => 'a-password',
        ] + $this->getOAuthMailSettingsRequest();

        $this->getControllerSkippingTokenCheck()->setMailSettings();
        $this->mailSettingsSaved = true;

        $mailConfig = Config::getInstance()->mail;
        $this->assertSame('/etc/matomo/smtp.crt', $mailConfig['oauth_certificate_path']);
        $this->assertSame('', $mailConfig['oauth_private_key_path']);
        $this->assertSame('', $mailConfig['oauth_private_key_password']);
    }

    /**
     * @dataProvider getInvalidCertificateMailSettings
     */
    public function testSetMailSettingsRejectsTheCertificateTypeWithoutAUsableCertificate(
        string $storedCertificatePath,
        array $settings,
        string $expectedMessage
    ): void {
        $this->setStoredOAuthSettings();
        Config::getInstance()->mail = ['oauth_certificate_path' => $storedCertificatePath] + Config::getInstance()->mail;
        $storedMail = Config::getInstance()->mail;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $settings + ['mailOAuthCredentialType' => 'certificate'] + $this->getOAuthMailSettingsRequest();

        $response = json_decode($this->getControllerSkippingTokenCheck()->setMailSettings(), true);

        $this->assertSame('error', $response['result']);
        $this->assertStringStartsWith($expectedMessage, $response['message']);
        $this->assertSame($storedMail, Config::getInstance()->mail);
    }

    public function getInvalidCertificateMailSettings(): iterable
    {
        yield 'no certificate set up' => [
            '',
            [],
            'To use a certificate, please set oauth_certificate_path in the [mail] section of config.ini.php first.',
        ];

        $changed = 'The certificate is set up for the current SMTP server, token URL, client ID and scope. '
            . 'To change them, please change them in the [mail] section of config.ini.php together with the certificate.';
        yield 'token URL changed' => ['/etc/matomo/smtp.crt', ['mailOAuthTokenUrl' => 'https://another.example.test/token'], $changed];
        yield 'SMTP server changed' => ['/etc/matomo/smtp.crt', ['mailHost' => 'another.example.test'], $changed];

        yield 'no username' => [
            '/etc/matomo/smtp.crt',
            ['mailUsername' => ''],
            'To use OAuth 2.0 with a certificate, please enter the SMTP username, the token URL and the client ID.',
        ];
    }

    public function testGeneralSettingsDoesNotSendTheOAuthSecretsToThePage(): void
    {
        Config::getInstance()->mail = [
            'oauth_client_secret'        => 'the-client-secret',
            'oauth_private_key_password' => 'the-key-password',
        ] + Config::getInstance()->mail;

        $page = StaticContainer::get(Controller::class)->generalSettings();

        $this->assertStringNotContainsString('the-client-secret', $page);
        $this->assertStringNotContainsString('the-key-password', $page);
        $this->assertStringContainsString('oauth_client_secret&quot;:&quot;*****&quot;', $page);
        $this->assertStringContainsString('oauth_private_key_password&quot;:&quot;*****&quot;', $page);
    }

    /**
     * @dataProvider getMailTypesInAnyCase
     */
    public function testGeneralSettingsMatchesTheMailTypeToTheFormOptionsIgnoringCase(
        string $configuredType,
        string $expectedType
    ): void {
        Config::getInstance()->mail = ['type' => $configuredType] + Config::getInstance()->mail;

        $page = StaticContainer::get(Controller::class)->generalSettings();

        $this->assertStringContainsString('&quot;type&quot;:&quot;' . $expectedType . '&quot;', $page);
    }

    public function getMailTypesInAnyCase(): iterable
    {
        yield 'upper case OAuth' => ['XOAUTH2', 'Xoauth2'];
        yield 'upper case login' => ['LOGIN', 'Login'];
        yield 'lower case CRAM-MD5' => ['cram-md5', 'Cram-md5'];
        yield 'unknown type is kept' => ['NTLM', 'NTLM'];
    }

    public function testSetMailSettingsRejectsIncorrectPasswordConfirmation(): void
    {
        $originalHost = Config::getInstance()->mail['host'];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $this->getMailSettingsRequest('reject.example.test');
        $_REQUEST = $_POST;

        $controller = $this->getControllerSkippingTokenCheck($passwordCorrect = false);

        $response = $controller->setMailSettings();

        $this->assertStringContainsString(Piwik::translate('UsersManager_CurrentPasswordNotCorrect'), $response);
        // nothing must have been persisted
        $this->assertSame($originalHost, Config::getInstance()->mail['host']);
    }

    public function testSetMailSettingsRejectsMissingPasswordConfirmation(): void
    {
        $originalHost = Config::getInstance()->mail['host'];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = $this->getMailSettingsRequest('reject.example.test');
        unset($request['passwordConfirmation']);
        $_POST = $request;
        $_REQUEST = $_POST;

        $controller = $this->getControllerSkippingTokenCheck();

        $response = $controller->setMailSettings();

        $this->assertStringContainsString(Piwik::translate('UsersManager_CurrentPasswordNotCorrect'), $response);
        // nothing must have been persisted
        $this->assertSame($originalHost, Config::getInstance()->mail['host']);
    }

    public function testWhatIsNewDoesNotPrefixBundledPluginsAndPrefixesThirdPartyPlugins(): void
    {
        $this->deleteAllChanges();

        $changesModel = new ChangesModel();
        $changesModel->addChange('CoreHome', [
            'version' => '1.0.0',
            'title' => 'Core bundled change',
            'description' => 'Core bundled description',
        ]);
        $changesModel->addChange('ProfessionalServices', [
            'version' => '1.0.0',
            'title' => 'Professional services bundled change',
            'description' => 'Professional services bundled description',
        ]);
        $changesModel->addChange('ThirdPartyPlugin', [
            'version' => '1.0.0',
            'title' => 'Third party change',
            'description' => 'Third party description',
        ]);

        $html = $this->controller->whatIsNew();

        $this->assertStringContainsString('>Core bundled change<', $html);
        $this->assertStringContainsString('>Professional services bundled change<', $html);
        $this->assertStringContainsString('>ThirdPartyPlugin - Third party change<', $html);

        $this->assertStringNotContainsString('>CoreHome - Core bundled change<', $html);
        $this->assertStringNotContainsString('>ProfessionalServices - Professional services bundled change<', $html);
    }

    public function testModifyErrorPageReplacesInvalidWebsiteMessage(): void
    {
        $_GET = ['idSite' => '999', 'period' => 'day', 'date' => 'today'];
        $_REQUEST = $_GET;

        $output = Piwik_GetErrorMessagePage(
            'Original message',
            false,
            true,
            true,
            'custom-logo.svg',
            'custom-favicon.png',
            false,
            '',
            false,
            'https://example.test/redirect',
            5
        );
        $plugin = new CoreAdminHome();
        $plugin->onModifyErrorPage(
            $output,
            new UnexpectedWebsiteFoundException("The requested website id = 999 couldn't be found")
        );

        $this->assertStringContainsString('This URL is not valid. The content may have been moved, deleted, or is no longer available', $output);
        $this->assertStringContainsString("website id was set to '999' on the URL", $output);
        $this->assertStringContainsString('Please go back to your previous page', $output);
        $this->assertStringContainsString('return to your dashboard', $output);
        $this->assertStringNotContainsString('Original message', $output);
        $this->assertStringContainsString('custom-logo.svg', $output);
        $this->assertStringContainsString('custom-favicon.png', $output);
        $this->assertStringContainsString('https://example.test/redirect', $output);
        $this->assertStringContainsString('setTimeout(function(){window.location.href="https://example.test/redirect"}', $output);
    }

    public function testModifyErrorPageDoesNotReplaceOtherUnexpectedWebsiteExceptions(): void
    {
        $_GET = ['idSite' => '1', 'period' => 'day', 'date' => 'today'];
        $_REQUEST = $_GET;

        $output = Piwik_GetErrorMessagePage('Original message', false, true, true, false, false, false, '', false);
        $plugin = new CoreAdminHome();
        $plugin->onModifyErrorPage(
            $output,
            new UnexpectedWebsiteFoundException("The requested website id = 999 couldn't be found")
        );

        $this->assertStringContainsString('Original message', $output);
        $this->assertStringNotContainsString('This URL is not valid. The content may have been moved, deleted, or is no longer available', $output);
    }

    public function testWhatIsNewRewritesInternalLinksToDefaultReportIdSite(): void
    {
        $this->deleteAllChanges();

        $changesModel = new ChangesModel();
        $changesModel->addChange('UsersManager', [
            'version' => '1.0.0',
            'title' => 'Settings change',
            'description' => 'Description',
            'link_name' => 'Open settings',
            'link' => 'index.php?module=UsersManager&action=userSettings&idSite=1',
        ]);
        $changesModel->addChange('SegmentEditor', [
            'version' => '1.0.0',
            'title' => 'Segment change',
            'description' => 'Description',
            'link_name' => 'Open segments',
            'link' => '/index.php?module=CoreHome&action=index&idSite=1#?idSite=1&category=General_Visitors',
        ]);

        $html = $this->controller->whatIsNew();

        $this->assertStringContainsString('index.php?module=UsersManager&amp;action=userSettings&amp;idSite=2', $html);
        $this->assertStringContainsString('index.php?module=CoreHome&amp;action=index&amp;idSite=2#?idSite=2&amp;category=General_Visitors', $html);
        $this->assertStringNotContainsString('href="/index.php?', $html);
        $this->assertStringNotContainsString('idSite=1', $html);
    }

    public function testWhatIsNewLeavesExternalLinksUnchanged(): void
    {
        $this->deleteAllChanges();

        $changesModel = new ChangesModel();
        $changesModel->addChange('PrivacyManager', [
            'version' => '1.0.0',
            'title' => 'External change',
            'description' => 'Description',
            'link_name' => 'Open docs',
            'link' => 'https://matomo.org/blog/2022/09/improvements-to-matomo-opt-out-form-feature/',
        ]);

        $html = $this->controller->whatIsNew();

        $this->assertStringContainsString(
            'https://matomo.org/blog/2022/09/improvements-to-matomo-opt-out-form-feature/',
            $html
        );
    }

    public function testWhatIsNewFallsBackToDefaultWebsiteIdWhenDefaultReportIsMultiSites(): void
    {
        $this->deleteAllChanges();

        UsersManagerAPI::getInstance()->setUserPreference(
            'superUserLogin',
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            'MultiSites'
        );

        $changesModel = new ChangesModel();
        $changesModel->addChange('UsersManager', [
            'version' => '1.0.0',
            'title' => 'Settings change',
            'description' => 'Description',
            'link_name' => 'Open settings',
            'link' => 'index.php?module=UsersManager&action=userSettings&idSite=99',
        ]);

        $html = $this->controller->whatIsNew();

        $this->assertStringContainsString('index.php?module=UsersManager&amp;action=userSettings&amp;idSite=1', $html);
    }

    public function testWhatIsNewLeavesInternalLinksUnchangedWhenNoDefaultIdSiteIsAvailable(): void
    {
        FakeAccess::clearAccess(
            $superUser = false,
            $idSitesAdmin = [],
            $idSitesView = [],
            $identity = 'superUserLogin'
        );

        UsersManagerAPI::getInstance()->setUserPreference(
            'superUserLogin',
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            'MultiSites'
        );

        $changes = [[
            'plugin_name' => 'UsersManager',
            'version' => '1.0.0',
            'title' => 'Settings change',
            'description' => 'Description',
            'link_name' => 'Open settings',
            'link' => 'index.php?module=UsersManager&action=userSettings&idSite=99',
        ]];

        $changes = $this->callEnrichChangesForWhatIsNew($changes);

        $this->assertSame(
            'index.php?module=UsersManager&action=userSettings&idSite=99',
            $changes[0]['link']
        );
    }

    public function tearDown(): void
    {
        $this->deleteAllChanges();
        $_GET = $this->backupGet;
        $_POST = $this->backupPost;
        $_REQUEST = $this->backupRequest;
        $_SERVER = $this->backupServer;
        Config::getInstance()->mail = $this->backupMail;
        Config::getInstance()->General = $this->backupGeneral;
        if ($this->mailSettingsSaved) {
            Config::getInstance()->forceSave();
        }
        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            'test.vars.loadChanges' => true,
        ];
    }

    private function deleteAllChanges(): void
    {
        Db::query('DELETE FROM `' . Common::prefixTable('changes') . '`');
    }

    private function callEnrichChangesForWhatIsNew(array $changes): array
    {
        $method = new \ReflectionMethod($this->controller, 'enrichChangesForWhatIsNew');
        $method->setAccessible(true);

        return $method->invoke($this->controller, $changes);
    }

    private function getControllerSkippingTokenCheck(bool $passwordCorrect = true): Controller
    {
        $controller = $this->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkTokenInUrl'])
            ->getMock();

        $controller->expects($this->once())
            ->method('checkTokenInUrl');

        $passwordVerify = $this->createMock(PasswordVerifier::class);
        $passwordVerify->method('isPasswordCorrect')->willReturn($passwordCorrect);

        $property = new \ReflectionProperty(Controller::class, 'passwordVerify');
        $property->setAccessible(true);
        $property->setValue($controller, $passwordVerify);

        return $controller;
    }

    private function getOAuthMailSettingsRequest(): array
    {
        return [
            'mailType' => 'Xoauth2',
            'mailOAuthTokenUrl' => 'https://login.example.test/token',
            'mailOAuthClientId' => 'the-client-id',
            'mailOAuthClientSecret' => 'the-client-secret',
            'mailOAuthScope' => 'https://mail.example.test/.default',
        ] + $this->getMailSettingsRequest('post.example.test');
    }

    private function setStoredOAuthSettings(): void
    {
        Config::getInstance()->mail = [
            'host' => 'post.example.test',
            'oauth_token_url' => 'https://login.example.test/token',
            'oauth_client_id' => 'the-client-id',
            'oauth_client_secret' => 'the-stored-secret',
            'oauth_scope' => 'https://mail.example.test/.default',
        ] + Config::getInstance()->mail;
    }

    private function getMailSettingsRequest(string $mailHost): array
    {
        $hostPrefix = explode('.', $mailHost)[0];

        return [
            'mailUseSmtp' => '1',
            'mailPort' => '25',
            'mailHost' => $mailHost,
            'mailType' => '',
            'mailUsername' => 'smtp-user-' . $hostPrefix,
            'mailPassword' => 'smtp-password-' . $hostPrefix,
            'mailEncryption' => 'none',
            'mailFromAddress' => 'mail-' . $hostPrefix . '@example.test',
            'mailFromName' => 'Acme <Team> & "' . $hostPrefix . '"',
            'passwordConfirmation' => 'super-user-password',
        ];
    }
}
