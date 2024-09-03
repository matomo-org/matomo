<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin\tests\Unit;
use Piwik\Plugins\CustomDirPlugin\CustomClass;

/**
 * @group CustomDirPlugin
 * @group CustomClassTest
 * @group Plugins
 */
class CustomClassTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        // set up here if needed
    }
    
    public function tearDown(): void
    {
        // tear down here if needed
    }

    public function testAutoloadingCustompluginWorks()
    {
        $customClass = new CustomClass();
        $this->assertTrue($customClass instanceof CustomClass);
    }

}
