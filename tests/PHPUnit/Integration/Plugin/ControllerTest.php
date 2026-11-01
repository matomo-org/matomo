<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Access;
use Piwik\FrontController;
use Piwik\Http\BadRequestException;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugin
 * @group ControllerTest
 */
class ControllerTest extends IntegrationTestCase
{
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = (int) Fixture::createWebsite('2012-01-01 00:00:00');
    }

    public function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }

    public function testThePageReportsTheLanguageItWasRenderedIn(): void
    {
        $html = $this->dispatchPage(['language' => 'de']);

        // read by the report export popover, which builds its own API URLs
        self::assertStringContainsString('piwik.language = "de";', $html);
        self::assertStringContainsString('lang="de"', $html);
    }

    public function testThePageFallsBackToTheLanguageOfTheUserWhenTheUrlAsksForNone(): void
    {
        $html = $this->dispatchPage();

        self::assertStringContainsString('piwik.language = "en";', $html);
        self::assertStringContainsString('lang="en"', $html);
    }

    /**
     * @dataProvider getInvalidSiteIds
     */
    public function testAPageForAWebsiteAnswersWithBadRequestWithoutAValidSiteId(?string $idSite): void
    {
        $_GET = [
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'containerId' => 'GoalsOverview',
            'period' => 'day',
            'date'   => 'today',
        ];
        if (null !== $idSite) {
            $_GET['idSite'] = $idSite;
        }

        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('The requested website idSite is not found in the request, or is invalid.');

        Access::doAsSuperUser(static function () {
            return FrontController::getInstance()->dispatch('CoreHome', 'renderWidgetContainer');
        });
    }

    public static function getInvalidSiteIds(): iterable
    {
        yield 'missing' => [null];
        yield 'empty' => [''];
        yield 'zero' => ['0'];
        yield 'not a number' => ['abc'];
    }

    /**
     * @param array<string, string> $params
     */
    private function dispatchPage(array $params = []): string
    {
        $_GET = array_merge([
            'module' => 'CoreHome',
            'action' => 'index',
            'idSite' => (string) $this->idSite,
            'period' => 'day',
            'date'   => 'today',
        ], $params);

        return Access::doAsSuperUser(static function () {
            return FrontController::getInstance()->fetchDispatch('CoreHome', 'index');
        });
    }
}
