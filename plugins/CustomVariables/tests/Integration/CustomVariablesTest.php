<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\tests;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\CustomVariables\Model;
use Piwik\Tracker\Cache;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomVariables
 * @group CustomVariablesTest
 * @group Plugins
 */
class CustomVariablesTest extends IntegrationTestCase
{
    public function test_getNumUsableCustomVariables_ShouldDetectCorrectNumberOfVariables()
    {
        Cache::clearCacheGeneral();
        $this->assertSame(5, CustomVariables::getNumUsableCustomVariables());
    }

    public function test_getNumUsableCustomVariables_ShouldCacheTheResult()
    {
        CustomVariables::getNumUsableCustomVariables();
        $cache = Cache::getCacheGeneral();

        $this->assertSame(5, $cache['CustomVariables.NumUsableCustomVariables']);
    }

    public function test_getNumUsableCustomVariables_ShouldReadFromCacheIfPossible()
    {
        $cache = Cache::getCacheGeneral();
        $cache['CustomVariables.NumUsableCustomVariables'] = 10;
        Cache::setCacheGeneral($cache);

        $this->assertSame(10, CustomVariables::getNumUsableCustomVariables());
    }

    public function test_getNumUsableCustomVariables_ShouldReturnMinVariables_IfOneTableHasLessEntriesThanOthers()
    {
        $this->assertEquals(5, CustomVariables::getNumUsableCustomVariables());

        $scopes = Model::getScopes();

        // removing custom vars step by step... as soon as one custom var is removed,
        // it should return the min count of available variables
        for ($i = 4; $i != -1; $i--) {
            foreach ($scopes as $scope) {
                $this->dropCustomVar($scope);
                $this->assertSame($i, CustomVariables::getNumUsableCustomVariables());
            }
        }

        $this->assertEquals(0, CustomVariables::getNumUsableCustomVariables());

        // add custom var, only once all custom vars are written it should write return a higher custom var number
        for ($i = 1; $i != 7; $i++) {
            foreach ($scopes as $index => $scope) {
                $isLastIndex = $index === (count($scopes) - 1);

                $this->addCustomVar($scope);

                if ($isLastIndex) {
                    $this->assertSame($i, CustomVariables::getNumUsableCustomVariables());
                    // all scopes have been added, it should consider all custom var counts
                } else {
                    $this->assertSame($i - 1, CustomVariables::getNumUsableCustomVariables());
                    // at least one scope is not added and should therefore return the old custom var count until all
                    // tables have been updated
                }
            }
        }

        $this->assertEquals(6, CustomVariables::getNumUsableCustomVariables());
    }

    private function dropCustomVar($scope)
    {
        $this->clearCache();
        $model = new Model($scope);
        $model->removeCustomVariable();
    }

    private function addCustomVar($scope)
    {
        $this->clearCache();
        $model = new Model($scope);
        $model->addCustomVariable();
    }

    private function clearCache()
    {
        Cache::clearCacheGeneral();
    }


}
