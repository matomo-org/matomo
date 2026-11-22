<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\DebugView\DebugView;

/**
 * @group DebugView
 * @group DebugViewPluginUnitTest
 * @group Plugins
 */
class DebugViewTest extends TestCase
{
    /**
     * @var DebugView
     */
    private $plugin;

    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = new DebugView('DebugView');
    }

    public function testRegisterEventsSubscribesToTheExpectedEvents()
    {
        $events = $this->plugin->registerEvents();

        $this->assertSame(
            [
                'AssetManager.getStylesheetFiles',
                'Translate.getClientSideTranslationKeys',
                'Db.getTablesInstalled',
                'SitesManager.deleteSite.end',
            ],
            array_keys($events)
        );
    }

    public function testIsTrackerPluginSoTheRequestProcessorIsLoadedDuringTracking()
    {
        $this->assertTrue($this->plugin->isTrackerPlugin());
    }

    public function testGetStylesheetFilesAddsThePluginStylesheet()
    {
        $stylesheets = [];
        $this->plugin->getStylesheetFiles($stylesheets);

        $this->assertSame([
            'plugins/DebugView/stylesheets/debugview.less',
            'plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.less',
            'plugins/DebugView/vue/src/MinutesRail/MinutesRail.less',
            'plugins/DebugView/vue/src/HitsStream/HitsStream.less',
            'plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.less',
            'plugins/DebugView/vue/src/HitDetailsPane/DetailRows.less',
        ], $stylesheets);
    }

    public function testGetClientSideTranslationKeysOnlyAddsDebugViewKeys()
    {
        $keys = [];
        $this->plugin->getClientSideTranslationKeys($keys);

        $this->assertNotEmpty($keys);
        foreach ($keys as $key) {
            $this->assertStringStartsWith('DebugView_', $key);
        }
    }

    public function testGetClientSideTranslationKeysContainsTheKeysTheComponentsUse()
    {
        $keys = [];
        $this->plugin->getClientSideTranslationKeys($keys);

        foreach (
            [
                'DebugView_DebugView',
                'DebugView_WaitingForRequests',
                'DebugView_ParametersTab',
                'DebugView_ProcessedTab',
                'DebugView_TrackingParameters',
                'DebugView_DefaultParameters',
                'DebugView_OtherParameters',
                'DebugView_ProcessedCannotBeShown',
                'DebugView_ProcessedAggregatedHint',
                'DebugView_TypeMedia',
                'DebugView_TypeCrash',
            ] as $expected
        ) {
            $this->assertContains($expected, $keys);
        }
    }
}
