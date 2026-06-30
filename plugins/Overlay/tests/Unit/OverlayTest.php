<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        $this->assertSame(false, Overlay::isOverlayRequest($module, $action, $method, 'https://demo.matomo.cloud'));
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
              'https://demo.matomo.cloud/index.php?module=Overlay&period=month&date=today&idSite=1',
            ],
            [ // invalid api method
              'API',
              'index',
              'VisitsSummary.get',
              'https://demo.matomo.cloud/index.php?module=Overlay&period=month&date=today&idSite=1',
            ],
            [ // invalid referer
              'API',
              'index',
              'Transitions.get',
              'https://demo.matomo.cloud/index.php?module=Overlay&module=CoreHome&action=index&period=month&date=today&idSite=1',
            ],
        ];
    }

    /**
     * The Overlay parent templates build the startOverlaySession navigation URL. Keep that URL
     * limited to the canonical handshake parameters — no `token_auth` or `force_api_session`
     * may appear as a query-string parameter (either prefixed with `?` or `&`).
     *
     * The matching pattern intentionally requires the `?`/`&` prefix and the `=` suffix so it
     * only fires on URL query-string appends, not on incidental identifier references in
     * comments or in the JS object literal that builds the POST-handoff body. The optional
     * `amp;` group also covers the case where a future refactor moves the URL into an HTML
     * attribute and the Twig auto-escape rewrites `&` to `&amp;`.
     *
     * @dataProvider getOverlayNavigationTemplates
     */
    public function testOverlayNavigationTemplateKeepsCanonicalHandshakeUrl(string $relativeTemplatePath)
    {
        $contents = file_get_contents(__DIR__ . '/../../templates/' . $relativeTemplatePath);
        self::assertNotFalse($contents, 'Could not read template ' . $relativeTemplatePath);

        self::assertNotRegExp(
            '/[?&](?:amp;)?(token_auth|force_api_session)=/',
            $contents,
            $relativeTemplatePath . ' must not append token_auth or force_api_session to the startOverlaySession URL'
                . ' as a query-string parameter.'
        );
    }

    /**
     * The credential propagation that previously rode on the URL must now travel via the POST
     * handoff. These assertions intentionally couple to the specific helper/identifier names in
     * the JS: a future maintainer renaming `submitPostNavigation`, `submitIframePost`, or
     * `iframePostParams` should re-pin these assertions to the new names rather than delete
     * them. The pinning ensures a refactor that accidentally drops the POST hand-off path
     * (and falls back to URL propagation) will fail this test.
     */
    public function testOverlayNavigationUsesPostHandoffWhenRequestAuthPropagationIsEnabled()
    {
        $indexContents = file_get_contents(__DIR__ . '/../../templates/index.twig');
        $noFrameContents = file_get_contents(__DIR__ . '/../../templates/index_noframe.twig');
        $overlayJsContents = file_get_contents(__DIR__ . '/../../javascripts/Piwik_Overlay.js');

        self::assertNotFalse($indexContents);
        self::assertNotFalse($noFrameContents);
        self::assertNotFalse($overlayJsContents);

        self::assertStringContainsString('iframePostParams = {', $indexContents);
        self::assertStringContainsString('Piwik_Overlay.init(iframeSrc', $indexContents);
        self::assertStringContainsString('iframePostParams);', $indexContents);

        self::assertStringContainsString('submitPostNavigation(newLocation, postParams);', $noFrameContents);

        self::assertStringContainsString('submitIframePost(iframeUrl);', $overlayJsContents);
        self::assertStringContainsString('function submitIframePost(iframeUrl)', $overlayJsContents);
    }

    public function getOverlayNavigationTemplates(): array
    {
        return [
            ['index.twig'],
            ['index_noframe.twig'],
        ];
    }

    /**
     * piwik.js::isOverlaySession() matches the canonical handshake fields. The
     * startOverlaySession template canonicalizes its URL before continuing, and the resulting
     * shape must still match.
     *
     * The regex below is the one in js/piwik.js. If it changes there, this test must change too.
     *
     * @dataProvider getCanonicalOverlayReferrers
     */
    public function testCanonicalOverlayReferrerMatchesPiwikJsRegex(string $referrer, bool $expectMatch)
    {
        $piwikJsRegex = '#index\.php\?module=Overlay&action=startOverlaySession&idSite=([0-9]+)&period=([^&]+)&date=([^&]+)(&segment=[^&]*)?#';
        self::assertSame($expectMatch, (bool) preg_match($piwikJsRegex, $referrer));
    }

    public function getCanonicalOverlayReferrers(): array
    {
        return [
            'canonical URL without segment matches' => [
                'https://matomo.example/index.php?module=Overlay&action=startOverlaySession&idSite=1&period=day&date=today',
                true,
            ],
            'canonical URL with segment matches' => [
                'https://matomo.example/index.php?module=Overlay&action=startOverlaySession&idSite=2&period=range&date=2020-01-01,2020-12-31&segment=visitIp%3D%3D1.2.3.4',
                true,
            ],
            'canonical URL with empty segment still matches' => [
                'https://matomo.example/index.php?module=Overlay&action=startOverlaySession&idSite=3&period=day&date=today&segment=',
                true,
            ],
            'reordered parameters do not match' => [
                'https://matomo.example/index.php?action=startOverlaySession&module=Overlay&idSite=1&period=day&date=today',
                false,
            ],
        ];
    }
}
