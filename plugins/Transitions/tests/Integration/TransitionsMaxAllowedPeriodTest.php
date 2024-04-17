<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Transitions\tests\Integration;

use Piwik\Plugins\Transitions\Transitions;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\Transitions\API;

/**
 * Tests the transitions plugin max_period_allowed setting
 *
 * @group TransitionsMaxAllowedPeriodTest
 * @group Plugins
 */
class TransitionsMaxAllowedPeriodTest extends IntegrationTestCase
{
    public $api;

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2010-02-03 00:00:00');
        $this->api = API::getInstance();

        $t = Fixture::getTracker(1, '2012-08-09 01:02:03', $defaultInit = true, $useLocalTracker = false);

        $t->setUrl('http://example.org/page/one.html');
        $t->doTrackPageView('incredible title ');
    }

    public function test_ShouldThrowException_IfPeriodNotAllowed()
    {
        $invalidPeriods = [
            'day' => ['week', 'month', 'year'],
            'week' => ['month', 'year'],
            'month' => ['year'],
        ];
        foreach ($invalidPeriods as $period => $invalids) {
            Config::setSetting('Transitions_1', 'max_period_allowed', $period);
            foreach ($invalids as $ip) {
                try {
                    $this->api->getTransitionsForAction(
                        'http://example.org/page/one.html',
                        'url',
                        1,
                        $ip,
                        '2012-08-09'
                    );
                    $this->fail("Failed asserting that exception 'PeriodNotAllowed' was thrown");
                } catch (\Exception $e) {
                    $this->assertEquals('PeriodNotAllowed', $e->getMessage());
                }
            }
        }
    }

    public function test_ShouldReturnData_IfPeriodAllowed()
    {
        $validPeriods = [
            'day' => ['day'],
            'week' => ['day', 'week'],
            'month' => ['day', 'week', 'month'],
            'year' => ['day', 'week', 'month', 'year'],
            'all' => ['day', 'week', 'month', 'year'],
        ];
        foreach ($validPeriods as $period => $valids) {
            Config::setSetting('Transitions_1', 'max_period_allowed', $period);
            foreach ($valids as $vp) {
                $r = $this->api->getTransitionsForAction(
                    'http://example.org/page/one.html',
                    'url',
                    1,
                    $vp,
                    '2012-08-09'
                );
                self::assertEquals(1, $r['pageMetrics']['pageviews']);
            }
        }
    }

    public function test_ShouldThrowException_IfInvalidLimitBeforeGroup()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('limitBeforeGrouping has to be an integer.');
        $this->api->getTransitionsForAction(
            'http://example.org/page/one.html',
            'url',
            1,
            'range',
            '2012-08-09,2012-08-10',
            false,
            'all'
        );
    }

    public function test_ShouldPass_IfLimitBeforeGroupPassingIntAsString()
    {
        $report = $this->api->getTransitionsForAction(
            'http://example.org/page/one.html',
            'url',
            1,
            'range',
            '2012-08-09,2012-08-10',
            false,
            '100'
        );
        $this->assertIsArray($report);
    }

    public function test_ShouldThrowException_IfRangeDayCountIsLargerThanDayPeriod()
    {
        Config::setSetting('Transitions_1', 'max_period_allowed', 'day');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PeriodNotAllowed');
        $this->api->getTransitionsForAction(
            'http://example.org/page/one.html',
            'url',
            1,
            'range',
            '2012-08-09,2012-08-10'
        );
    }

    public function test_ShouldThrowException_IfRangeDayCountIsLargerThanWeekPeriod()
    {
        Config::setSetting('Transitions_1', 'max_period_allowed', 'day');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PeriodNotAllowed');
        $this->api->getTransitionsForAction(
            'http://example.org/page/one.html',
            'url',
            1,
            'range',
            '2012-08-09,2012-08-17'
        );
    }

    public function test_ShouldThrowException_IfRangeDayCountIsLargerThanMonthPeriod()
    {
        Config::setSetting('Transitions_1', 'max_period_allowed', 'day');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PeriodNotAllowed');
        $this->api->getTransitionsForAction(
            'http://example.org/page/one.html',
            'url',
            1,
            'range',
            '2012-08-09,2012-09-10'
        );
    }

    public function test_ShouldThrowException_IfRangeDayCountIsLargerThanYearPeriod()
    {
        Config::setSetting('Transitions_1', 'max_period_allowed', 'day');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PeriodNotAllowed');
        $this->api->getTransitionsForAction(
            'http://example.org/page/one.html',
            'url',
            1,
            'range',
            '2012-08-09,2013-08-10'
        );
    }

    public function test_ShouldUseSiteConfigInsteadOfGeneral_IfSiteConfigExists()
    {
        Config::setSetting('Transitions_1', 'max_period_allowed', null);
        Config::setSetting('Transitions', 'max_period_allowed', 'month');
        $maxAllowedPeriod = Transitions::getPeriodAllowedConfig(1);
        $this->assertEquals('month', $maxAllowedPeriod);

        Config::setSetting('Transitions_1', 'max_period_allowed', 'week');
        $maxAllowedPeriod = Transitions::getPeriodAllowedConfig(1);
        $this->assertEquals('week', $maxAllowedPeriod);
    }
}
