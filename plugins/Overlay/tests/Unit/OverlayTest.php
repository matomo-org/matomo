<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Overlay\tests\Unit;

use Piwik\Plugins\Overlay\Overlay;

class OverlayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getOverlayRequestTestData
     */
    public function testIsOverlayRequestWithValidReferredRequests($module, $action, $method)
    {
        $this->assertSame(true, Overlay::isOverlayRequest($module, $action, $method, 'https://demo.matomo.cloud/index.php?module=Overlay&period=month&date=today&idSite=1'));
        $this->assertSame(false, Overlay::isOverlayRequest($module, $action, $method, 'https://demo.matomo.org'));
    }

    public function getOverlayRequestTestData()
    {
        return [
            [ // CSS
                'Proxy',
                'getCss',
                '',
            ],
            [ // JS
                'Proxy',
                'getCoreJs',
                '',
            ],
            [ // API request
                'API',
                'index',
                'Overlay.getTranslations',
            ],
            [ // API request
                'API',
                'index',
                'Transitions.get',
            ],
            [ // Row evolution
                'CoreHome',
                'getRowEvolutionPopover',
                '',
            ],
            [ // Row evolution
                'CoreHome',
                'getRowEvolutionGraph',
                '',
            ],
            [
                'CoreHome',
                'saveViewDataTableParameters',
                '',
            ],
            [
                'Transitions',
                'renderPopover',
                '',
            ],
            [
                'Live',
                'indexVisitorLog',
                '',
            ],
            [
                'Live',
                'getLastVisitsDetails',
                '',
            ],
            [
                'Live',
                'getVisitorProfilePopup',
                '',
            ],
            [
                'Live',
                'getVisitList',
                '',
            ],
            [
                'UserCountryMap',
                'realtimeMap',
                '',
            ],
        ];
    }

    /**
     * @dataProvider getInvalidOverlayRequestTestData
     */
    public function testIsOverlayRequestWithiNValidReferredRequests($module, $action, $method, $referer)
    {
        $this->assertSame(false, Overlay::isOverlayRequest($module, $action, $method, $referer));
    }

    public function getInvalidOverlayRequestTestData()
    {
        return [
            [ // invalid module / action
              'Referer',
              'get',
              '',
              'https://demo.matomo.cloud/index.php?module=Overlay&period=month&date=today&idSite=1'
            ],
            [ // invalid api method
              'API',
              'index',
              'VisitsSummary.get',
              'https://demo.matomo.cloud/index.php?module=Overlay&period=month&date=today&idSite=1'
            ],
            [ // invalid referer
              'API',
              'index',
              'Transitions.get',
              'https://demo.matomo.cloud/index.php?module=Overlay&module=CoreHome&action=index&period=month&date=today&idSite=1'
            ],
        ];
    }
}
