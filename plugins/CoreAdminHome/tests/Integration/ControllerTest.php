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
use Piwik\Db;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Plugins\CoreAdminHome\CoreAdminHome;
use Piwik\Plugins\CoreAdminHome\Controller;
use Piwik\Plugins\CoreAdminHome\OptOutManager;
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
    private $backupRequest;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;

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
            new OptOutManager()
        );
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
        $_REQUEST = $this->backupRequest;
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
}
