<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Proxy;

use Piwik\AssetManager;
use Piwik\AssetManager\UIAsset;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\ProxyHttp;
use Piwik\Url;
use Piwik\UrlHelper;

/**
 * Controller for proxy services
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    const TRANSPARENT_PNG_PIXEL = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=';
    const JS_MIME_TYPE = "application/javascript; charset=UTF-8";

    /**
     * Output the merged CSS file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getCss()
    {
        $cssMergedFile = AssetManager::getInstance()->getMergedStylesheet();
        ProxyHttp::serverStaticFile($cssMergedFile->getAbsoluteLocation(), "text/css");
    }

    /**
     * Output the merged core JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getCoreJs()
    {
        $jsMergedFile = AssetManager::getInstance()->getMergedCoreJavaScript();
        $this->serveJsFile($jsMergedFile);
    }

    /**
     * Output the merged non core JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getNonCoreJs()
    {
        $jsMergedFile = AssetManager::getInstance()->getMergedNonCoreJavaScript();
        $this->serveJsFile($jsMergedFile);
    }

    /**
     * @param UIAsset $uiAsset
     */
    private function serveJsFile($uiAsset)
    {
        ProxyHttp::serverStaticFile($uiAsset->getAbsoluteLocation(), self::JS_MIME_TYPE);
    }

    /**
     * Output redirection page instead of linking directly to avoid
     * exposing the referrer on the Piwik demo.
     *
     * @internal param string $url (via $_GET)
     */
    public function redirect()
    {
        $url = Common::getRequestVar('url', '', 'string', $_GET);

        // validate referrer
        $referrer = Url::getReferrer();
        if (empty($referrer) || !Url::isLocalUrl($referrer)) {
            die('Invalid Referrer detected - This means that your web browser is not sending the "Referrer URL" which is
				required to proceed with the redirect. Verify your browser settings and add-ons, to check why your browser
				 is not sending this referrer.

				<br/><br/>You can access the page at: ' . $url);
        }

        // mask visits to *.piwik.org
        if (!self::isPiwikUrl($url)) {
            Piwik::checkUserHasSomeViewAccess();
        }
        if (!UrlHelper::isLookLikeUrl($url)) {
            die('Please check the &url= parameter: it should to be a valid URL');
        }
        Common::sendHeader('Content-Type: text/html; charset=utf-8');
        echo '<html><head><meta http-equiv="refresh" content="0;url=' . $url . '" /></head></html>';

        exit;
    }

    /**
     * Validate URL against *.piwik.org domains
     *
     * @param string $url
     * @return bool True if valid; false otherwise
     */
    public static function isPiwikUrl($url)
    {
        // guard for IE6 meta refresh parsing weakness (OSVDB 19029)
        if (strpos($url, ';') !== false
            || strpos($url, '&#59') !== false
        ) {
            return false;
        }
        if (preg_match('~^http://(qa\.|demo\.|dev\.|forum\.)?piwik.org([#?/]|$)~', $url)) {
            return true;
        }

        // Allow clockworksms domain
        if (strpos($url, 'http://www.clockworksms.com/') === 0) {
            return true;
        }

        return false;
    }
}
