<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Columns;

use Piwik\Columns\DimensionsProvider;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DimensionsProviderTest extends IntegrationTestCase
{
    /**
     * @var DimensionsProvider
     */
    private $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new DimensionsProvider();
    }

    public function test_factory()
    {
        Manager::getInstance()->loadPlugins(array('ExampleTracker'));
        $dimension = $this->provider->factory("ExampleTracker.ExampleDimension");
        $this->assertInstanceOf('Piwik\Plugins\ExampleTracker\Columns\ExampleDimension', $dimension);
    }
}
