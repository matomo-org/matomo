<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker\Handler;

use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;
use Piwik\Tracker\Handler;
use Piwik\Tracker\Handler\Factory;

/**
 * @group Tracker
 * @group Handler
 * @group Factory
 * @group FactoryTest
 */
class FactoryTest extends IntegrationTestCase
{
    public function testMakeShouldCreateDefaultInstance()
    {
        $handler = Factory::make();
        $this->assertInstanceOf('Piwik\\Tracker\\Handler', $handler);
    }

    public function testMakeShouldTriggerEventOnce()
    {
        $called = 0;
        $self   = $this;
        Piwik::addAction('Tracker.newHandler', function ($handler) use (&$called, $self) {
            $called++;
            $self->assertNull($handler);
        });

        Factory::make();
        $this->assertSame(1, $called);
    }

    public function testMakeShouldPreferManuallyCreatedHandlerInstanceInEventOverDefaultHandler()
    {
        $handlerToUse = new Handler();
        Piwik::addAction('Tracker.newHandler', function (&$handler) use ($handlerToUse) {
            $handler = $handlerToUse;
        });

        $handler = Factory::make();
        $this->assertSame($handlerToUse, $handler);
    }

    public function testMakeShouldTriggerExceptionInCaseWrongInstanceCreatedInHandler()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The Handler object set in the plugin');

        Piwik::addAction('Tracker.newHandler', function (&$handler) {
            $handler = new Tracker();
        });

        Factory::make();
    }
}
