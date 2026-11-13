<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\tests\Integration;

use Piwik\API\Request;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\FrontController;
use Piwik\Menu\MenuAdmin;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\Controller;
use Piwik\Plugins\Transitions\Menu;
use Piwik\Plugins\Transitions\PreprocessedPages\ArchivingMode;
use Piwik\Plugins\Transitions\PreprocessedPages\RebuildWindow;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The status of one page for the report's toggle, and the admin page's menu entry and controller.
 *
 * @group Transitions
 * @group PreprocessedPageStatusTest
 * @group Plugins
 */
class PreprocessedPageStatusTest extends IntegrationTestCase
{
    private API $api;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::clearAccess(true);
        Fixture::createWebsite('2010-01-01 00:00:00', 0, 'Site 1', 'http://example.com', 1, null, null, null, null, 0, 'sessionid');
        Fixture::createWebsite('2010-01-01 00:00:00', 0, 'Site 2', 'http://example.org');

        $this->api = API::getInstance();
        // with cron archiving, which processes the past of an added page
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    /**
     * @dataProvider getDeniedRoles
     */
    public function testNonAdminsCannotReadTheStatus(string $role): void
    {
        $this->setRole($role, 1);

        $this->expectException(NoAccessException::class);
        $this->api->getPreprocessedPageStatus(1, 'https://example.com/a');
    }

    /**
     * @return array<string, array{string}>
     */
    public function getDeniedRoles(): array
    {
        return [
            'anonymous' => ['anonymous'],
            'view' => ['view'],
            'write' => ['write'],
            'admin of another site' => ['admin of site 2'],
        ];
    }

    /**
     * @dataProvider getAllowedRoles
     */
    public function testAdminsReadTheStatus(string $role): void
    {
        $idPage = $this->api->addPreprocessedPage(1, 'https://example.com/a')['idpage'];
        $this->setRole($role, 1);

        self::assertSame(
            ['selected' => true, 'idPage' => $idPage, 'used' => 1, 'limit' => 20, 'rearchiveMonths' => RebuildWindow::getMonths()],
            $this->api->getPreprocessedPageStatus(1, 'https://example.com/a')
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function getAllowedRoles(): array
    {
        return [
            'admin' => ['admin'],
            'super user' => ['superuser'],
        ];
    }

    public function testAnUnknownWebsiteIsRejected(): void
    {
        $this->expectException(UnexpectedWebsiteFoundException::class);
        $this->api->getPreprocessedPageStatus(99, 'https://example.com/a');
    }

    /**
     * @dataProvider getVariantsOfTheSamePage
     */
    public function testThePageIsMatchedAsDuplicatesAreWhenAdding(string $added, string $requested): void
    {
        $idPage = $this->api->addPreprocessedPage(1, $added)['idpage'];

        $status = $this->api->getPreprocessedPageStatus(1, $requested);

        self::assertTrue($status['selected']);
        self::assertSame($idPage, $status['idPage']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getVariantsOfTheSamePage(): array
    {
        return [
            'protocol' => ['https://example.com/a', 'http://example.com/a'],
            'www' => ['https://example.com/a', 'https://www.example.com/a'],
            'host case' => ['https://example.com/a', 'https://EXAMPLE.com/a'],
            'excluded parameter' => ['https://example.com/a?x=1', 'https://example.com/a?x=1&sessionid=abc'],
            'campaign parameter' => ['https://example.com/a', 'https://example.com/a?mtm_campaign=c'],
            'fragment' => ['https://example.com/a', 'https://example.com/a#top'],
        ];
    }

    public function testAQueryStringSentOverHttpMatchesTheOneAddedOverHttp(): void
    {
        $url = 'https://example.com/a?b=1&c=<2>';
        $added = Request::processRequest('Transitions.addPreprocessedPage', ['idSite' => 1, 'pageUrl' => $url]);

        $status = Request::processRequest('Transitions.getPreprocessedPageStatus', ['idSite' => 1, 'pageUrl' => $url]);

        self::assertTrue($status['selected']);
        self::assertSame($added['idpage'], $status['idPage']);
    }

    public function testAPageThatIsNotSelected(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(1, 'https://example.com/b');

        self::assertSame(
            ['selected' => false, 'idPage' => null, 'used' => 2, 'limit' => 20, 'rearchiveMonths' => RebuildWindow::getMonths()],
            $this->api->getPreprocessedPageStatus(1, 'https://example.com/a/')
        );
    }

    /**
     * @dataProvider getUrlsAddingRejects
     */
    public function testAUrlThatAddingRejectsIsNotSelected(string $url): void
    {
        $status = $this->api->getPreprocessedPageStatus(1, $url);

        self::assertFalse($status['selected']);
        self::assertNull($status['idPage']);
    }

    /**
     * @return array<string, array{string}>
     */
    public function getUrlsAddingRejects(): array
    {
        return [
            'no scheme' => ['example.com/a'],
            'other scheme' => ['javascript:alert(1)'],
            'empty' => [''],
            'too long' => ['https://example.com/' . str_repeat('a', 4100)],
        ];
    }

    public function testTheStatusIsPerWebsite(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(2, 'https://example.org/b');

        $status = $this->api->getPreprocessedPageStatus(2, 'https://example.com/a');

        self::assertFalse($status['selected']);
        self::assertSame(1, $status['used']);
    }

    public function testTheLimitIsTheWebsitesOneAlsoWhenLoweredBelowTheCount(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(1, 'https://example.com/b');
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 1];

        $status = $this->api->getPreprocessedPageStatus(1, 'https://example.com/c');

        self::assertSame(2, $status['used']);
        self::assertSame(1, $status['limit']);
        self::assertSame(20, $this->api->getPreprocessedPageStatus(2, 'https://example.org/c')['limit']);
    }

    /**
     * @dataProvider getRearchiveWindows
     *
     * @param int|string $configValue
     */
    public function testTheRebuildWindowIsReadAsCoreReadsIt($configValue, int $expectedMonths): void
    {
        $general = Config::getInstance()->General;
        $general['rearchive_reports_in_past_last_n_months'] = $configValue;
        Config::getInstance()->General = $general;

        self::assertSame($expectedMonths, $this->api->getPreprocessedPageStatus(1, 'https://example.com/a')['rearchiveMonths']);
    }

    /**
     * @return array<string, array{int|string, int}>
     */
    public function getRearchiveWindows(): array
    {
        return [
            'number' => ['3', 3],
            'int' => [12, 12],
            'lastN' => ['last2', 2],
            'zero' => ['0', 0],
            'empty' => ['', 0],
            'negative' => ['-1', 0],
            'invalid' => ['all', 0],
            'trailing characters' => ['6x', 6],
            'lastN with trailing characters' => ['last6x', 6],
            'fraction' => ['1.5', 2],
            'half' => ['0.5', 1],
        ];
    }

    public function testCronArchivingIsDetectedPerWebsiteWithBrowserArchivingEnabled(): void
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        $this->api->addPreprocessedPage(1, 'https://example.com/selected');
        // not gone through by core:archive yet
        self::assertSame(0, $this->getRearchiveMonths(1));

        // core:archive went through both websites; it is recorded only for website 1, which has selected pages
        Piwik::postEvent('CronArchive.archiveSingleSite.finish', [1, '4711']);
        Piwik::postEvent('CronArchive.archiveSingleSite.finish', [2, '4711']);
        self::assertSame(RebuildWindow::getMonths(), $this->getRearchiveMonths(1));
        $this->api->addPreprocessedPage(2, 'https://example.org/selected');
        self::assertSame(0, $this->getRearchiveMonths(2));

        $start = Date::now()->getTimestamp();
        try {
            // a run within the hour isn't recorded again, so the record expires from the first run
            Date::$now = $start + 1800;
            Piwik::postEvent('CronArchive.archiveSingleSite.finish', [1, '4712']);
            Date::$now = $start + ArchivingMode::CRON_RECENT_SECONDS + 60;
            self::assertSame(0, $this->getRearchiveMonths(1));

            // a later run is
            Piwik::postEvent('CronArchive.archiveSingleSite.finish', [1, '4713']);
            self::assertSame(RebuildWindow::getMonths(), $this->getRearchiveMonths(1));
        } finally {
            Date::$now = null;
        }
    }

    public function testWithoutCronArchivingOnlyNewDataIsProcessed(): void
    {
        $this->setRole('admin', 1);
        $_GET['idSite'] = '1';
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        self::assertGreaterThan(0, RebuildWindow::getMonths());

        self::assertSame(0, $this->api->getPreprocessedPageStatus(1, 'https://example.com/a')['rearchiveMonths']);
        self::assertStringContainsString('rearchive-months="0"', FrontController::getInstance()->fetchDispatch('Transitions', 'manage'));
    }

    /**
     * @dataProvider getMenuRoles
     */
    public function testTheMenuEntryIsShownToAdminsOfTheWebsiteOnly(string $role, bool $isShown): void
    {
        $this->setRole($role, 1);
        $_GET['idSite'] = '1';

        $expected = [['Transitions_PreprocessedTransitions', ['idSite' => 1, 'action' => 'manage', 'module' => 'Transitions']]];
        self::assertSame($isShown ? $expected : [], $this->getMenuItems());
    }

    public function testTheMenuEntryIsForTheDefaultWebsiteWithoutOne(): void
    {
        $this->setRole('admin', 1);
        unset($_GET['idSite']);

        // the admin page needs the website in its URL
        self::assertSame([['Transitions_PreprocessedTransitions', ['idSite' => 1, 'action' => 'manage', 'module' => 'Transitions']]], $this->getMenuItems());
    }

    public function testNoMenuEntryForAnInvalidWebsite(): void
    {
        $this->setRole('superuser', 1);
        $_GET['idSite'] = '1abc';

        self::assertSame([], $this->getMenuItems());
    }

    private function getRearchiveMonths(int $idSite): int
    {
        return $this->api->getPreprocessedPageStatus($idSite, 'https://example.com/a')['rearchiveMonths'];
    }

    /**
     * @return list<array{string, mixed}>
     */
    private function getMenuItems(): array
    {
        $menu = new class extends MenuAdmin {
            /** @var list<array{string, mixed}> */
            public array $items = [];

            public function __construct()
            {
                parent::__construct();
            }

            public function addMeasurableItem($menuName, $url, $order = 50, $tooltip = false, $icon = false)
            {
                $this->items[] = [$menuName, $url];
            }
        };
        (new Menu())->configureAdminMenu($menu);

        return $menu->items;
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function getMenuRoles(): array
    {
        return [
            'super user' => ['superuser', true],
            'admin' => ['admin', true],
            'write' => ['write', false],
            'view' => ['view', false],
            'admin of another site' => ['admin of site 2', false],
        ];
    }

    /**
     * @dataProvider getDeniedRoles
     */
    public function testTheAdminPageRequiresAdminAccess(string $role): void
    {
        $this->setRole($role, 1);
        $_GET['idSite'] = '1';

        $this->expectException(NoAccessException::class);
        StaticContainer::getContainer()->make(Controller::class)->manage();
    }

    public function testTheAdminPageGetsTheLimitAndTheRebuildWindow(): void
    {
        $this->setRole('admin', 1);
        $_GET['idSite'] = '1';
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 7];

        $html = FrontController::getInstance()->fetchDispatch('Transitions', 'manage');

        self::assertStringContainsString('vue-entry="Transitions.ManagePreprocessedPages"', $html);
        self::assertStringContainsString('max-pages="7"', $html);
        self::assertStringContainsString('rearchive-months="' . RebuildWindow::getMonths() . '"', $html);
    }

    private function setRole(string $role, int $idSite): void
    {
        switch ($role) {
            case 'superuser':
                FakeAccess::clearAccess(true);
                break;
            case 'admin':
                FakeAccess::clearAccess(false, [$idSite], [], 'siteAdmin');
                break;
            case 'write':
                FakeAccess::clearAccess(false, [], [], 'siteWriter', [$idSite]);
                break;
            case 'view':
                FakeAccess::clearAccess(false, [], [$idSite], 'siteViewer');
                break;
            case 'admin of site 2':
                FakeAccess::clearAccess(false, [2], [2], 'otherAdmin');
                break;
            default:
                FakeAccess::clearAccess(false, [], [], 'anonymous');
        }
    }
}
