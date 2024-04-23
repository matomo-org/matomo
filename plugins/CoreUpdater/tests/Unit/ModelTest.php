<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Unit;

use Piwik\Plugins\CoreUpdater\Model;

/**
 * @group CoreUpdater
 * @group ModelTest
 * @group Unit
 * @group Plugins
 */
class ModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Model
     */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Model();
    }

    public function testGetPluginsFromDirectoyShouldReturnEmptyArrayIfNoPluginsExist()
    {
        $plugins = $this->model->getPluginsFromDirectoy(PIWIK_INCLUDE_PATH . '/config');

        $this->assertEquals(array(), $plugins);
    }

    public function testGetPluginsFromDirectoyShouldReturnAllDirectoriesWithinPlugins()
    {
        $plugins = $this->model->getPluginsFromDirectoy(PIWIK_INCLUDE_PATH);

        $this->assertGreaterThan(40, count($plugins));
        self::assertTrue(in_array('/plugins/API', $plugins));
        self::assertTrue(in_array('/plugins/Actions', $plugins));
        self::assertTrue(in_array('/plugins/Annotations', $plugins));

        self::assertTrue(!in_array('/plugins/.', $plugins));
        self::assertTrue(!in_array('/plugins/..', $plugins));
        self::assertTrue(!in_array('/plugins', $plugins));
        self::assertTrue(!in_array('/plugins/', $plugins));

        foreach ($plugins as $plugin) {
            $this->assertTrue(is_dir(PIWIK_INCLUDE_PATH . $plugin));
            $this->assertStringStartsWith('/plugins/', $plugin);
            $this->assertTrue(12 <= strlen($plugin)); // make sure it does not return something like '/plugins'.
        }
    }
}
