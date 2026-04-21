<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Config;

use Piwik\Config;
use Piwik\Config\SectionConfig;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

class SectionConfigTest extends UnitTestCase
{
    public function testGetTypedConfigValueReturnsNullWhenConfigIsMissing(): void
    {
        $this->assertNull(TestSectionConfig::getIntegerConfigValue('missing'));
        $this->assertNull(TestSectionConfig::getFloatConfigValue('missing'));
        $this->assertNull(TestSectionConfig::getBoolConfigValue('missing'));
        $this->assertNull(TestSectionConfig::getStringConfigValue('missing'));
        $this->assertNull(TestSectionConfig::getArrayConfigValue('missing'));
    }

    public function testGetTypedConfigValueReturnsExpectedTypes(): void
    {
        Config::getInstance()->TestSection = [
            'integer' => '17',
            'float' => '17.25',
            'boolean' => 'true',
            'string' => "ran\0dom",
            'array' => ['key' => "val\0ue"],
        ];

        $this->assertSame(17, TestSectionConfig::getIntegerConfigValue('integer'));
        $this->assertSame(17.25, TestSectionConfig::getFloatConfigValue('float'));
        $this->assertTrue(TestSectionConfig::getBoolConfigValue('boolean'));
        $this->assertSame('random', TestSectionConfig::getStringConfigValue('string'));
        $this->assertSame(['key' => 'value'], TestSectionConfig::getArrayConfigValue('array'));
    }

    public function testGetTypedConfigValueReturnsDefaultWhenConfigIsMissing(): void
    {
        $this->assertSame(7, TestSectionConfig::getIntegerConfigValue('missing', 7));
        $this->assertSame(7.5, TestSectionConfig::getFloatConfigValue('missing', 7.5));
        $this->assertTrue(TestSectionConfig::getBoolConfigValue('missing', true));
        $this->assertSame('fallback', TestSectionConfig::getStringConfigValue('missing', 'fallback'));
        $this->assertSame(['fallback'], TestSectionConfig::getArrayConfigValue('missing', ['fallback']));
    }

    public function testGetConfigValueReturnsSiteSpecificOverride(): void
    {
        Config::getInstance()->TestSection = [
            'integer' => '17',
        ];
        Config::getInstance()->TestSection_4 = [
            'integer' => '23',
        ];

        $this->assertSame(17, TestSectionConfig::getIntegerConfigValue('integer', null, 3));
        $this->assertSame(23, TestSectionConfig::getIntegerConfigValue('integer', null, 4));
    }

    /**
     * @dataProvider getInvalidConfigValues
     */
    public function testGetTypedConfigValueLogsWarningWhenCastFails(string $method, $value, string $expectedType): void
    {
        Config::getInstance()->TestSection = [
            'setting' => $value,
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'Failed to cast config value {section}.{name} to {type}; actual type was {actualType}.',
                [
                    'section' => 'TestSection',
                    'name' => 'setting',
                    'type' => $expectedType,
                    'actualType' => is_object($value) ? get_class($value) : gettype($value),
                ]
            );

        StaticContainer::getContainer()->set(LoggerInterface::class, $loggerMock);

        $this->assertNull(TestSectionConfig::$method('setting'));
    }

    /**
     * @dataProvider getInvalidConfigValuesWithDefaults
     */
    public function testGetTypedConfigValueReturnsDefaultAndLogsWarningWhenCastFails(
        string $method,
        $value,
        $default,
        string $expectedType
    ): void {
        Config::getInstance()->TestSection = [
            'setting' => $value,
        ];

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'Failed to cast config value {section}.{name} to {type}; actual type was {actualType}.',
                [
                    'section' => 'TestSection',
                    'name' => 'setting',
                    'type' => $expectedType,
                    'actualType' => is_object($value) ? get_class($value) : gettype($value),
                ]
            );

        StaticContainer::getContainer()->set(LoggerInterface::class, $loggerMock);

        $this->assertSame($default, TestSectionConfig::$method('setting', $default));
    }

    /**
     * @return iterable<string, array{string, mixed, string}>
     */
    public function getInvalidConfigValues(): iterable
    {
        yield 'int from invalid string' => ['getIntegerConfigValue', 'abc', 'int'];
        yield 'float from invalid string' => ['getFloatConfigValue', 'abc', 'float'];
        yield 'bool from invalid integer' => ['getBoolConfigValue', 2, 'bool'];
        yield 'string from bool' => ['getStringConfigValue', true, 'string'];
        yield 'array from csv string' => ['getArrayConfigValue', 'a,b', 'array'];
        yield 'int from object' => ['getIntegerConfigValue', new \stdClass(), 'int'];
    }

    /**
     * @return iterable<string, array{string, mixed, mixed, string}>
     */
    public function getInvalidConfigValuesWithDefaults(): iterable
    {
        yield 'int from invalid string with default' => ['getIntegerConfigValue', 'abc', 9, 'int'];
        yield 'float from invalid string with default' => ['getFloatConfigValue', 'abc', 9.5, 'float'];
        yield 'bool from invalid integer with default' => ['getBoolConfigValue', 2, true, 'bool'];
        yield 'string from bool with default' => ['getStringConfigValue', true, 'fallback', 'string'];
        yield 'array from csv string with default' => ['getArrayConfigValue', 'a,b', ['fallback'], 'array'];
    }
}

class TestSectionConfig extends SectionConfig
{
    public static function getSectionName(): string
    {
        return 'TestSection';
    }
}
