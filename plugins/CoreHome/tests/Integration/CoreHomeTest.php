<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group Plugins
 */
class CoreHomeTest extends IntegrationTestCase
{
    public function test_CoreHomePlugin_ShouldListenToFilterJavaScriptEvent_AndOnlyChangeIfContainsSourceMap()
    {
        $content = 'var x = 5;';
        $expectedContent = $content;
        Piwik::postEvent('AssetManager.filterMergedJavaScripts', array(&$content));

        $this->assertEquals($expectedContent, $content);
    }

    public function test_CoreHomePlugin_ShouldListenToFilterJavaScriptEvent_AndRemoveSourceMapDefinition()
    {
        $content = '//# sourceMappingURL=55.map';
        Piwik::postEvent('AssetManager.filterMergedJavaScripts', array(&$content));

        $this->assertEquals('//# ', $content);
    }
}
