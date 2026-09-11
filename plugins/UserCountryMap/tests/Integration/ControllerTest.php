<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\UserCountryMap\tests\Integration;

use Piwik\Access;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UserCountryMap
 * @group Plugins
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

    public function testMapCarriesThePageLanguageIntoTheRequestsItBuildsItself(): void
    {
        $reqParams = $this->renderedReqParams(['language' => 'de']);

        self::assertSame('de', $reqParams['language'] ?? null);
    }

    public function testMapSendsNoLanguageWhenThePageWasNotGivenOne(): void
    {
        self::assertArrayNotHasKey('language', $this->renderedReqParams());
    }

    /**
     * The parameters the map's own $.ajax calls are built from, read back out of the config the
     * template serialises for them.
     *
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    private function renderedReqParams(array $params = []): array
    {
        $_GET = array_merge([
            'module' => 'UserCountryMap',
            'action' => 'realtimeMap',
            'idSite' => (string) $this->idSite,
            'period' => 'day',
            'date'   => 'today',
        ], $params);

        $html = Access::doAsSuperUser(static function () {
            return FrontController::getInstance()->fetchDispatch('UserCountryMap', 'realtimeMap');
        });

        if (!preg_match('/data-config="([^"]*)"/', $html, $matches)) {
            self::fail('the real time map did not render the config its scripts read');
        }

        $config = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);

        self::assertIsArray($config['reqParams'] ?? null);

        return $config['reqParams'];
    }
}
