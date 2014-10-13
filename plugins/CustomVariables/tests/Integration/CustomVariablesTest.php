<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\tests;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker\Cache;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomVariables
 * @group CustomVariablesTest
 * @group Plugins
 */
class CustomVariablesTest extends IntegrationTestCase
{
    public function testGetMaxCustomVariables_ShouldDetectCorrectNumberOfVariables()
    {
        Cache::clearCacheGeneral();
        $this->assertSame(5, CustomVariables::getMaxCustomVariables());
    }

    public function testGetMaxCustomVariables_ShouldCacheTheResult()
    {
        CustomVariables::getMaxCustomVariables();
        $cache = Cache::getCacheGeneral();

        $this->assertSame(5, $cache['CustomVariables.MaxNumCustomVariables']);
    }

    public function testGetMaxCustomVariables_ShouldReadFromCacheIfPossible()
    {
        $cache = Cache::getCacheGeneral();
        $cache['CustomVariables.MaxNumCustomVariables'] = 10;
        Cache::setCacheGeneral($cache);

        $this->assertSame(10, CustomVariables::getMaxCustomVariables());
    }

}
