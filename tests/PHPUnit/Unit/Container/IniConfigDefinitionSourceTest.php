<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Container;

use DI\Definition\ValueDefinition;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Container\IniConfigDefinitionSource;

class IniConfigDefinitionSourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getDefinition_whenNotMatchingPrefix_shouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource($this->createConfig(), 'prefix.');

        $this->assertNull($definitionSource->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function getDefinition_withUnknownConfigSection_shouldReturnEmptyArray()
    {
        $definitionSource = new IniConfigDefinitionSource(new GlobalSettingsProvider());

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('ini.foo');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('ini.foo', $definition->getName());
        $this->assertSame(array(), $definition->getValue());
    }

    /**
     * @test
     */
    public function getDefinition_withUnknownConfigSectionAndKey_shouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource(new GlobalSettingsProvider());

        $this->assertNull($definitionSource->getDefinition('ini.foo.bar'));
    }

    /**
     * @test
     */
    public function getDefinition_withUnknownConfigKey_shouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource(new GlobalSettingsProvider());

        $this->assertNull($definitionSource->getDefinition('ini.General.foo'));
    }

    /**
     * @test
     */
    public function getDefinition_withExistingConfigSection_shouldReturnValueDefinition()
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

    /**
     * @test
     */
    public function getDefinition_withExistingConfigKey_shouldReturnValueDefinition()
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
