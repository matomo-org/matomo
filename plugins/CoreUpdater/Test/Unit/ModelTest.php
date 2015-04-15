<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Test\Unit;

use Piwik\Plugins\CoreUpdater\Model;

/**
 * @group CoreUpdater
 * @group ModelTest
 * @group Unit
 * @group Plugins
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Model
     */
    private $model;

    public function setUp()
    {
        parent::setUp();

        $this->model = new Model();
    }

    public function test_getPluginsFromDirectoy_shouldReturnEmptyArray_IfNoPluginsExist()
    {
        $plugins = $this->model->getPluginsFromDirectoy(PIWIK_INCLUDE_PATH . '/config');

        $this->assertEquals(array(), $plugins);
    }

    public function test_getPluginsFromDirectoy_shouldReturnAllDirectoriesWithinPlugins()
    {
        $plugins = $this->model->getPluginsFromDirectoy(PIWIK_INCLUDE_PATH);

        $this->assertGreaterThan(40, count($plugins));
        $this->assertContains('/plugins/API', $plugins);
        $this->assertContains('/plugins/Actions', $plugins);
        $this->assertContains('/plugins/Annotations', $plugins);

        $this->assertNotContains('/plugins/.', $plugins);
        $this->assertNotContains('/plugins/..', $plugins);
        $this->assertNotContains('/plugins', $plugins);
        $this->assertNotContains('/plugins/', $plugins);

        foreach ($plugins as $plugin) {
            $this->assertTrue(is_dir(PIWIK_INCLUDE_PATH . $plugin));
            $this->assertStringStartsWith('/plugins/', $plugin);
            $this->assertTrue(12 <= strlen($plugin)); // make sure it does not return something like '/plugins'.
        }
    }

}
