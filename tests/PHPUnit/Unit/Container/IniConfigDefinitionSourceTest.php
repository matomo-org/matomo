<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Container;

use DI\Definition\ValueDefinition;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Container\IniConfigDefinitionSource;

class IniConfigDefinitionSourceTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDefinitionWhenNotMatchingPrefixShouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource($this->createConfig(), 'prefix.');

        $this->assertNull($definitionSource->getDefinition('foo'));
    }

    public function testGetDefinitionWithUnknownConfigSectionShouldReturnEmptyArray()
    {
        $definitionSource = new IniConfigDefinitionSource(new GlobalSettingsProvider());

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('ini.foo');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('ini.foo', $definition->getName());
        $this->assertSame(array(), $definition->getValue());
    }

    public function testGetDefinitionWithUnknownConfigSectionAndKeyShouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource(new GlobalSettingsProvider());

        $this->assertNull($definitionSource->getDefinition('ini.foo.bar'));
    }

    public function testGetDefinitionWithUnknownConfigKeyShouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource(new GlobalSettingsProvider());

        $this->assertNull($definitionSource->getDefinition('ini.General.foo'));
    }

    public function testGetDefinitionWithExistingConfigSectionShouldReturnValueDefinition()
    {
        $config = $this->createConfig();
        $config->expects($this->once())
            ->method('getSection')
            ->with('General')
            ->willReturn(array('foo' => 'bar'));

        $definitionSource = new IniConfigDefinitionSource($config);

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('ini.General');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('ini.General', $definition->getName());
        self::assertIsArray($definition->getValue());
        $this->assertEquals(array('foo' => 'bar'), $definition->getValue());
    }

    public function testGetDefinitionWithExistingConfigKeyShouldReturnValueDefinition()
    {
        $config = $this->createConfig();
        $config->expects($this->once())
            ->method('getSection')
            ->with('General')
            ->willReturn(array('foo' => 'bar'));

        $definitionSource = new IniConfigDefinitionSource($config);

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('ini.General.foo');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('ini.General.foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GlobalSettingsProvider
     */
    private function createConfig()
    {
        return $this->getMockBuilder('Piwik\Application\Kernel\GlobalSettingsProvider')
                ->disableOriginalConstructor()->getMock();
    }
}
