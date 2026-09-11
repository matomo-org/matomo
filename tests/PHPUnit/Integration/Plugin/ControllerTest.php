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
