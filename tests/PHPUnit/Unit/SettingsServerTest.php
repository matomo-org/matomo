<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\SettingsServer;

/**
 * @group Core
 * @group SettingsServer
 */
class SettingsServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Dataprovider for testGetMegaBytesFromShorthandByte
     */
    public function getShorthandByteTestData()
    {
        return [
            ['8M', 8],
            ['10 m', 10],
            ['2g', 2048],
            ['1K', 1 / 1024],
            ['1048576', 1],
            ['garbl', false],
            ['17sdfsdf', false],
        ];
    }

    /**
     * @dataProvider getShorthandByteTestData
     */
    public function testGetMegaBytesFromShorthandByte($data, $expected)
    {
        $class = new \ReflectionClass(SettingsServer::class);
        $method = $class->getMethod('getMegaBytesFromShorthandByte');
        $method->setAccessible(true);
        $output = $method->invoke($class, $data);

        $this->assertEquals($expected, $output);
    }
}
