<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Common;
use Piwik\FrontController;
use Piwik\Http\JsonResponse;

/**
 * @group Core
 */
class FrontControllerJsonResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        Common::$headersSentInTests = [];
        parent::tearDown();
    }

    public function testReappliesJsonHeaderForAttributedActionEvenAfterItWasOverwritten(): void
    {
        $controller = new class {
            #[JsonResponse]
            public function jsonAction(): string
            {
                return '{}';
            }
        };

        Common::$headersSentInTests = [];
        // Simulate later output (e.g. a rendered View) resetting the Content-Type back to HTML.
        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $this->invokeApplyResponseHeaders([$controller, 'jsonAction']);

        $this->assertSame(
            'application/json; charset=utf-8',
            trim(Common::$headersSentInTests['Content-Type'])
        );
    }

    public function testLeavesContentTypeUntouchedForActionWithoutAttribute(): void
    {
        $controller = new class {
            public function htmlAction(): string
            {
                return '';
            }
        };

        Common::$headersSentInTests = [];
        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $this->invokeApplyResponseHeaders([$controller, 'htmlAction']);

        $this->assertSame(
            'text/html; charset=utf-8',
            trim(Common::$headersSentInTests['Content-Type'])
        );
    }

    /**
     * @param callable $controller
     */
    private function invokeApplyResponseHeaders($controller): void
    {
        $method = new \ReflectionMethod(FrontController::class, 'applyResponseHeadersFromAttributes');
        $method->invoke(FrontController::getInstance(), $controller);
    }
}
