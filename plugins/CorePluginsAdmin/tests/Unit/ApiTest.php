<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Unit;

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    /**
     * @dataProvider getCheckLastNHoursTestData
     * @param int $maxMinutes
     * @param int $lastNMinutes
     * @param bool $isMaxInvalid
     * @param bool $areMinutesTooLow
     * @param bool $areMinutesTooHigh
     * @return void
     * @throws \Exception
     */
    public function testCheckLastNMinutes($maxMinutes, $lastNMinutes, $isMaxInvalid = false, $areMinutesTooLow = false, $areMinutesTooHigh = false)
    {
        $isExceptionExpected = false;
        if (!is_numeric($maxMinutes)) {
            $this->expectException(\TypeError::class);
            $isExceptionExpected = true;
        }

        if (!is_numeric($lastNMinutes)) {
            $this->expectException(\TypeError::class);
            $isExceptionExpected = true;
        }

        if ($isMaxInvalid || $areMinutesTooLow || $areMinutesTooHigh) {
            $this->expectException(\Exception::class);
            $isExceptionExpected = true;
        }
        if ($isMaxInvalid) {
            $this->expectExceptionMessage('Max minutes must be greater than 0');
        }
        if ($areMinutesTooLow) {
            $this->expectExceptionMessage('General_ValidatorErrorNumberTooLow');
        }
        if ($areMinutesTooHigh) {
            $this->expectExceptionMessage('General_ValidatorErrorNumberTooHigh');
        }

        if (!$isExceptionExpected) {
            $this->expectNotToPerformAssertions();
        }

        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->checkLastNMinutes($maxMinutes, $lastNMinutes);
    }

    public function getCheckLastNHoursTestData(): array
    {
        return [
            ['60', 60, false, false, false],
            ['60a', 60, false, false, false],
            [60, '60', false, false, false],
            [60, '60a', false, false, false],
            [-60, 60, true, false, false],
            [-1, 60, true, false, false],
            [0, 60, true, false, false],
            [1, 1, false, false, false],
            [60, 60, false, false, false],
            [120, 120, false, false, false],
            [360, 360, false, false, false],
            [720, 720, false, false, false],
            [60, -60, false, true, false],
            [60, -1, false, true, false],
            [60, 0, false, true, false],
            [1, 2, false, false, true],
            [1, 60, false, false, true],
            [60, 61, false, false, true],
            [60, 120, false, false, true],
            [120, 121, false, false, true],
            [120, 360, false, false, true],
            [120, 720, false, false, true],
            [360, 361, false, false, true],
            [360, 720, false, false, true],
            [720, 721, false, false, true],
        ];
    }
}
