<?php

namespace Piwik\Plugins\Marketplace\tests\Unit;

use Piwik\Plugins\Marketplace\Plugins;
use ReflectionClass;

/**
 * @group Marketplace
 * @group PluginsTest
 * @group Plugins
 */
class PluginsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getNumberOfDownloadsData
     */
    public function test_prettifyNumberOfDownloads($numDownloads, $expectedPrettyDownloads)
    {
        $pluginsClass = new Plugins(
            $this->createMock('Piwik\Plugins\Marketplace\Api\Client'),
            $this->createMock('Piwik\Plugins\Marketplace\Consumer'),
            $this->createMock('Piwik\ProfessionalServices\Advertising')
        );

        $pluginsReflection = new ReflectionClass($pluginsClass);
        $method = $pluginsReflection->getMethod('prettifyNumberOfDownloads');
        $method->setAccessible(true);

        $plugin = ['numDownloads' => $numDownloads];
        $method->invokeArgs($pluginsClass, [&$plugin]);

        $this->assertEquals($expectedPrettyDownloads, $plugin['numDownloadsPretty']);
    }

    public function getNumberOfDownloadsData(): array
    {
        return [
            [-1, -1],
            [0, 0],
            [999, 999],
            [1000, '1k'],
            [1050, '1k'],
            [1051, '1.1k'],
            [1550, '1.5k'],
            [1551, '1.6k'],
            [9950, '9.9k'],
            [9951, '10k'],
            [9999, '10k'],
            [10000, '10k'],
            [10100, '10.1k'],
            [99950, '99.9k'],
            [99951, '100k'],
            [100000, '100k'],
            [999999, '999k'],
            [1000000, '1m'],
            [1100000, '1m'],
            [9999999, '9m'],
            [10000000, '10m'],
            [10000001, '10m'],
            [99999999, '99m'],
            [100000000, '100m'],
            [100000001, '100m'],
            [999999999, '999m'],
            [1000000000, '1000m'],
        ];
    }
}
