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
        $_GET = ['idSite' => 1, 'period' => 'day', 'date' => 'today'];
        $_REQUEST = $_GET;

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

        $output = 'original output';
        $plugin = new CoreAdminHome();
        $plugin->onModifyErrorPage(
            $output,
            new UnexpectedWebsiteFoundException("The requested website id = 999 couldn't be found")
        );

        $this->assertStringContainsString('This URL is not valid. The content may have been moved, deleted, or is no longer available', $output);
        $this->assertStringContainsString("website id was set to '999' on the URL", $output);
        $this->assertStringContainsString('Please go back to your previous page', $output);
        $this->assertStringContainsString('return to your dashboard', $output);
        $this->assertStringNotContainsString('An unexpected website was found in the request', $output);
    }


    public function testModifyErrorPageDoesNotReplaceOtherUnexpectedWebsiteExceptions(): void
    {
        $_GET = ['idSite' => '1', 'period' => 'day', 'date' => 'today'];
        $_REQUEST = $_GET;

        $output = 'original output';
        $plugin = new CoreAdminHome();
        $plugin->onModifyErrorPage(
            $output,
            new UnexpectedWebsiteFoundException("The requested website id = 999 couldn't be found")
        );

        $this->assertSame('original output', $output);
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
}
