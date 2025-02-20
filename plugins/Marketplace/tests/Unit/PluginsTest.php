<?php

namespace Piwik\Plugins\Marketplace\tests\Unit;

use Piwik\Plugins\Marketplace\Plugins;
use Piwik\Tests\Framework\Fixture;
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
    public function testPrettifyNumberOfDownloads($numDownloads, $expectedPrettyDownloads)
    {
        Fixture::loadAllTranslations();

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
            [1000, '1K'],
            [1050, '1.1K'],
            [1051, '1.1K'],
            [1550, '1.6K'],
            [1551, '1.6K'],
            [9950, '10K'],
            [9951, '10K'],
            [9999, '10K'],
            [10000, '10K'],
            [10100, '10K'],
            [99950, '100K'],
            [99951, '100K'],
            [100000, '100K'],
            [999999, '1M'],
            [1000000, '1M'],
            [1100000, '1.1M'],
            [9999999, '10M'],
            [10000000, '10M'],
            [10000001, '10M'],
            [99999999, '100M'],
            [100000000, '100M'],
            [100000001, '100M'],
            [999999999, '1B'],
            [1000000000, '1B'],
        ];
    }
}
