<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Transitions\tests\System;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Fixtures\SomeVisitsManyPageviewsWithTransitions;
use Piwik\Config;
use Piwik\Plugins\Transitions\API;

/**
 * Tests the transitions plugin max_period_allowed setting
 *
 * @group TransitionsMaxAllowedPeriodTest
 * @group Plugins
 */
class TransitionsMaxAllowedPeriodTest extends IntegrationTestCase
{
    /**
     * @var SomeVisitsManyPageviewsWithTransitions
     */
    public static $fixture = null;
    public $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();
    }

    public function test_ShouldThrowException_IfPeriodNotAllowed()
    {
        // Attempt to get transition data for a week when the max period is set to a day
        Config::setSetting('Transitions', 'max_period_allowed', 'day');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PeriodNotAllowed');
        $this->api->getTransitionsForAction('http://example.org/page/one.html', 'url', self::$fixture->idSite, 'week', self::$fixture->dateTime);
    }
}

TransitionsMaxAllowedPeriodTest::$fixture = new SomeVisitsManyPageviewsWithTransitions();