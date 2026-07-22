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

    public function testInheritsAttributeFromOverriddenParentAction(): void
    {
        // A subclass overrides an attributed parent action without re-declaring the attribute (PHP
        // does not inherit method attributes) and delegates to the parent. The header must still win.
        Common::$headersSentInTests = [];
        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $this->invokeApplyResponseHeaders([new JsonResponseInheritanceChild(), 'act']);

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
        $method->setAccessible(true);
        $method->invoke(FrontController::getInstance(), $controller);
    }
}

class JsonResponseInheritanceParent
{
    #[JsonResponse]
    public function act(): string
    {
        return '{}';
    }
}

class JsonResponseInheritanceChild extends JsonResponseInheritanceParent
{
    public function act(): string
    {
        return parent::act();
    }
}
