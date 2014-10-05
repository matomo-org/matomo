<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\API\ApiRenderer;

/**
 * @group Core
 * @group Only2
 */
class ApiRendererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array('API'));
    }

    public function test_factory_shouldCreateAnInstance_IfValidFormatGiven()
    {
        $renderer = ApiRenderer::factory('php', array());
        $this->assertInstanceOf('\\Piwik\\Plugins\\API\\Renderer\\Php', $renderer);

        $renderer = ApiRenderer::factory('PHP', array());
        $this->assertInstanceOf('\\Piwik\\Plugins\\API\\Renderer\\Php', $renderer);

        $renderer = ApiRenderer::factory('pHp', array());
        $this->assertInstanceOf('\\Piwik\\Plugins\\API\\Renderer\\Php', $renderer);

        $renderer = ApiRenderer::factory('xmL', array());
        $this->assertInstanceOf('\\Piwik\\Plugins\\API\\Renderer\\Xml', $renderer);

        $renderer = ApiRenderer::factory('OriginAl', array());
        $this->assertInstanceOf('\\Piwik\\Plugins\\API\\Renderer\\Original', $renderer);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage General_ExceptionInvalidRendererFormat
     */
    public function test_factory_shouldThrowAnException_IfInvalidFormatGiven()
    {
        ApiRenderer::factory('phpi', array());
    }
}
