<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker\Visit;

use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Visit\Factory;

/**
 * @group Tracker
 * @group Handler
 * @group Visit
 * @group Factory
 * @group FactoryTest
 */
class FactoryTest extends IntegrationTestCase
{
    public function testMakeShouldCreateDefaultInstance()
    {
        $visit = Factory::make();
        $this->assertInstanceOf('Piwik\\Tracker\\Visit', $visit);
    }

    public function testMakeShouldTriggerEventOnce()
    {
        $called = 0;
        $self   = $this;
        Piwik::addAction('Tracker.makeNewVisitObject', function ($visit) use (&$called, $self) {
            $called++;
            $self->assertNull($visit);
        });

        Factory::make();
        $this->assertSame(1, $called);
    }

    public function testMakeShouldPreferManuallyCreatedHandlerInstanceInEventOverDefaultHandler()
    {
        $visitToUse = new Visit();
        Piwik::addAction('Tracker.makeNewVisitObject', function (&$visit) use ($visitToUse) {
            $visit = $visitToUse;
        });

        $visit = Factory::make();
        $this->assertSame($visitToUse, $visit);
    }

    public function testMakeShouldTriggerExceptionInCaseWrongInstanceCreatedInHandler()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The Visit object set in the plugin');

        Piwik::addAction('Tracker.makeNewVisitObject', function (&$visit) {
            $visit = new Tracker();
        });

        Factory::make();
    }
}
