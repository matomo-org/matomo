<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration\Plugin;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration\Plugin\Activate;
use Piwik\Updater\Migration\Plugin\Factory;

/**
 * @group Core
 * @group Updater
 * @group Migration
 */
class FactoryTest extends IntegrationTestCase
{
    /**
     * @var Factory
     */
    private $factory;

    private $pluginName = 'MyTestPluginName';

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new Factory();
    }

    public function test_activate_returnsActivateInstance()
    {
        $migration = $this->factory->activate($this->pluginName);

        $this->assertTrue($migration instanceof Activate);
    }

    public function test_sql_forwardsQueryAndErrorCode()
    {
        $migration = $this->factory->activate($this->pluginName);

        $this->assertSame('./console plugin:activate "MyTestPluginName"', '' . $migration);
    }
}
