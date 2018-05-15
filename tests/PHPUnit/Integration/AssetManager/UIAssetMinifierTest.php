<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\AssetManager;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAssetMinifier;

class UIAssetMinifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UIAssetMinifier
     */
    private $assetMinifier;

    public function setUp()
    {
        $this->assetMinifier = UIAssetMinifier::getInstance();
    }

    public function provider_isMinifiedJs()
    {
        return array(
            array('libs/bower_components/jquery/dist/jquery.min.js', true),
            array('libs/bower_components/jquery-ui/ui/minified/jquery-ui.min.js', true),
            array('libs/jquery/jquery.browser.js', true),
            array('libs/jqplot/jqplot-custom.min.js', true),
            array('plugins/TreemapVisualization/libs/Jit/jit-2.0.1-yc.js', true),
            array('plugins/TreemapVisualization/javascripts/treemapViz.js', false),
            array('plugins/UserCountryMap/javascripts/vendor/raphael.min.js', true),
            array('plugins/UserCountryMap/javascripts/vendor/jquery.qtip.min.js', true),
            array('plugins/UserCountryMap/javascripts/vendor/kartograph.min.js', true),
            array('plugins/UserCountryMap/javascripts/vendor/jquery.qtip.min.js', true),
        );
    }

    /**
     * @group Core
     * @dataProvider provider_isMinifiedJs
     */
    public function test_isMinifiedJs($scriptFileName, $isMinified)
    {
        $scriptFile = new OnDiskUIAsset(PIWIK_USER_PATH, $scriptFileName);

        $this->assertEquals(
            $isMinified,
            $this->assetMinifier->isMinifiedJs($scriptFile->getContent())
        );
    }
}
