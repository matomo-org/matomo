<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\AssetManager;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAssetMinifier;

class UIAssetMinifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UIAssetMinifier
     */
    private $assetMinifier;

    public function setUp(): void
    {
        $this->assetMinifier = UIAssetMinifier::getInstance();
    }

    public function providerIsMinifiedJs()
    {
        return array(
            array('node_modules/jquery/dist/jquery.min.js', true),
            array('node_modules/jquery-ui-dist/jquery-ui.min.js', true),
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
     * @dataProvider providerIsMinifiedJs
     */
    public function testIsMinifiedJs($scriptFileName, $isMinified)
    {
        $scriptFile = new OnDiskUIAsset(PIWIK_DOCUMENT_ROOT, $scriptFileName);

        $this->assertEquals(
            $isMinified,
            $this->assetMinifier->isMinifiedJs($scriptFile->getContent())
        );
    }
}
