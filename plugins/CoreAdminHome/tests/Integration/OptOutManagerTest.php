<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Http\BadRequestException;
use Piwik\Http\HttpCodeException;
use Piwik\Plugins\CoreAdminHome\OptOutManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreAdminHome
 * @group OptOutManagerTest
 * @group Plugins
 */
class OptOutManagerTest extends IntegrationTestCase
{
    private array $get = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->get = $_GET;
    }

    public function tearDown(): void
    {
        $_GET = $this->get;

        parent::tearDown();
    }

    /**
     * @dataProvider getInvalidStylingParameters
     */
    public function testGetOptOutJSRejectsInvalidStylingAsBadRequest(string $name, string $value): void
    {
        $_GET[$name] = $value;

        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("The URL parameter $name value of '$value' is not valid.");

        (new OptOutManager())->getOptOutJS();
    }

    /**
     * @dataProvider getInvalidStylingParameters
     */
    public function testGetOptOutViewIFrameRejectsInvalidStylingAsBadRequest(string $name, string $value): void
    {
        $_GET[$name] = $value;

        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode(400);

        (new OptOutManager())->getOptOutViewIFrame();
    }

    public function testGetOptOutSelfContainedEmbedCodeKeepsPlainValidationError(): void
    {
        $exception = null;
        try {
            (new OptOutManager())->getOptOutSelfContainedEmbedCode('zzz', '000000', '12px', 'Arial', true, true);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertNotInstanceOf(HttpCodeException::class, $exception);
        $this->assertStringContainsString("The URL parameter backgroundColor value of 'zzz' is not valid.", $exception->getMessage());
    }

    /**
     * @return array<array{string, string}>
     */
    public function getInvalidStylingParameters(): array
    {
        return [
            ['fontColor', 'zzz'],
            ['backgroundColor', 'ffff'],
            ['fontSize', '12; color: red'],
            ['fontFamily', 'Arial; color: red'],
        ];
    }
}
