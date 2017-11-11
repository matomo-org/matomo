<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\BaseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group Core
 */
class FactoryTest extends TestCase
{
    public function testCreatingExistingClassSucceeds()
    {
        $instance = BaseFactory::factory('Piwik\Timer');

        $this->assertNotNull($instance);
        $this->assertInstanceOf('Piwik\Timer', $instance);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid class ID
     */
    public function testCreatingInvalidClassThrows()
    {
        BaseFactory::factory("This\\Class\\Does\\Not\\Exist");
    }
}
