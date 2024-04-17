<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit;

use Piwik\Plugins\CoreHome\CoreHome;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group Plugins
 */
class CoreHomeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CoreHome
     */
    private $coreHome;

    public function setUp(): void
    {
        parent::setUp();

        $this->coreHome = new CoreHome();
    }

    /**
     * @dataProvider getJavaScriptsContainingNoSourceMapDefinition
     */
    public function testFilterMergedJavaScripts_shouldNotChangeAnything_IfJsDoesNotContainAnySourceMap($content)
    {
        $expectedContent = $content;

        $this->coreHome->filterMergedJavaScripts($content);

        $this->assertSame($expectedContent, $content);
    }

    /**
     * @dataProvider getJavaScriptsContainingSourceMapDefinition
     */
    public function testFilterMergedJavaScripts_shouldRemoveSourceMap_IfDefinedInJs($content, $expectedContent)
    {
        $this->coreHome->filterMergedJavaScripts($content);

        $this->assertSame($expectedContent, $content);
    }

    public function testFilterMergedJavaScripts_shouldRemoveMultipleSourceMapDefinitionsInOneContent()
    {
        $content = 'var x = 5;
//# sourceMappingURL=whatever.js.map .map
init();
x = 6;
//# sourceMappingURL=js.map
foo("bar");
';
        $expected = 'var x = 5;
//#  .map
init();
x = 6;
//# 
foo("bar");
';
        $this->coreHome->filterMergedJavaScripts($content);

        $this->assertSame($expected, $content);
    }

    public function getJavaScriptsContainingNoSourceMapDefinition()
    {
        $js = array();

        $js[] = array('');
        $js[] = array('var x = 5; init();');
        $js[] = array('//# sourceMappingURL');
        $js[] = array('//# sourceMappingURL=');
        $js[] = array('//# sourceMappingURL.map');
        $js[] = array('sourceMappingURL=
cc.map');

        return $js;
    }

    public function getJavaScriptsContainingSourceMapDefinition()
    {
        $js = array();

        $js[] = array('//# sourceMappingURL=55.map', '//# ');
        $js[] = array('//# sourceMappingURL=.map', '//# ');
        $js[] = array('sourceMappingURL=whatever.js.map', '');
        $js[] = array('sourceMappingURL=whatever.js.map .map', ' .map');
        $js[] = array('var x = 5;
//# sourceMappingURL=whatever.js.map .map', 'var x = 5;
//#  .map');

        return $js;
    }
}
