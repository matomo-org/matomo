<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Overlay;

use Piwik\Url;
use Piwik\UrlHelper;

class Overlay extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    /**
     * Returns required Js Files
     * @param $jsFiles
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Overlay/javascripts/rowaction.js';
        $jsFiles[] = 'plugins/Overlay/javascripts/Overlay_Helper.js';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_OverlayRowActionTooltipTitle';
        $translationKeys[] = 'General_OverlayRowActionTooltip';
    }

    /**
     * Returns if a request belongs to the Overlay page
     *
     * Whenever we change the Overlay, or any feature that is available on that page, this list needs to be adjusted
     * Otherwise it can happen, that the session cookie is sent with samesite=lax, which might break the session in Overlay
     * See https://github.com/matomo-org/matomo/pull/18648
     */
    public static function isOverlayRequest($module, $action, $method, $referer)
    {
        $isOverlay = $module == 'Overlay';
        $referrerUrlQuery = parse_url($referer ?? '', PHP_URL_QUERY);
        $referrerUrlQueryParams = UrlHelper::getArrayFromQueryString($referrerUrlQuery);
        $referrerUrlHost = parse_url($referer ?? '', PHP_URL_HOST);
        $comingFromOverlay = Url::isValidHost($referrerUrlHost) && !empty($referrerUrlQueryParams['module']) && $referrerUrlQueryParams['module'] === 'Overlay';
        $isPossibleOverlayRequest = (
            $module === 'Proxy' // JS & CSS requests
            || ($module === 'API' && 0 === strpos($method, 'Overlay.')) // Overlay API data
            || ($module === 'CoreHome' && $action === 'getRowEvolutionPopover') // Row evolution
            || ($module === 'CoreHome' && $action === 'getRowEvolutionGraph') // Row evolution (graph)
            || ($module === 'CoreHome' && $action === 'saveViewDataTableParameters') // store chart changes (within row evolution & transitions)
            || $module === 'Annotations' // required to interact with annotations in evolution charts (within row evolution)
            || ($module === 'Transitions' && $action === 'renderPopover') // Transitions
            || ($module === 'API' && 0 === strpos($method, 'Transitions.')) // Transitions API data
            || ($module === 'Live' && $action === 'indexVisitorLog') // Visits Log
            || ($module === 'Live' && $action === 'getLastVisitsDetails') // Visits Log (pagination)
            || ($module === 'Live' && $action === 'getVisitorProfilePopup') // Visitor Profile
            || ($module === 'Live' && $action === 'getVisitList') // Visitor Profile (load more visits)
            || ($module === 'UserCountryMap' && $action === 'realtimeMap') // Visitor Profile (map)
        );

        return $isOverlay || ($comingFromOverlay && $isPossibleOverlayRequest);
    }
}
