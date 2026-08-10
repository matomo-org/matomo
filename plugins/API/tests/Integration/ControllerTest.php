<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\Common;
use Piwik\Plugins\API\Controller;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Common::$headersSentInTests = [];
    }

    public function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }

    /**
     * @dataProvider getFormatSpellings
     */
    public function testIndexServesArrayOutputAsPlainText(string $format)
    {
        $_GET = [
            'module' => 'API',
            'method' => 'API.getBulkRequest',
            'format' => $format,
            'serialize' => '0',
            'urls' => [],
        ];

        $response = (new Controller())->index();

        self::assertSame(var_export([], true), $response);
        self::assertSame(
            'text/plain; charset=utf-8',
            trim(Common::$headersSentInTests['Content-Type'] ?? '')
        );
    }

    public function getFormatSpellings(): array
    {
        return [
            ['original'],
            ['ORIGINAL'],
            ['Original'],
        ];
    }
}
