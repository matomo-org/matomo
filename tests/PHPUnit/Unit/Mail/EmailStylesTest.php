<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Mail;

use Piwik\Mail\EmailStyles;

class EmailStylesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getTestDataForRgbToHex
     */
    public function test_rgbToHex_convertsRgbCorrectly($values, $expected)
    {
        $this->assertEquals($expected, EmailStyles::rgbToHex($values));
    }

    public function getTestDataForRgbToHex()
    {
        return [
            ['255,127,80', '#ff7f50'],
            ['255,0,0', '#ff0000'],
            ['13,13,13', '#0d0d0d'],
        ];
    }
}
