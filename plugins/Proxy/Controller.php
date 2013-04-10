<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Proxy
 */

/**
 * Controller for proxy services
 *
 * @package Piwik_Proxy
 */
class Piwik_Proxy_Controller extends Piwik_Controller
{
    const TRANSPARENT_PNG_PIXEL = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=';

    /**
     * Display the "Export Image" window.
     *
     * @deprecated 1.5.1
     *
     * @param string $imageData Base-64 encoded image data (via $_POST)
     */
    static public function exportImageWindow()
    {
        Piwik::checkUserHasSomeViewAccess();

        $view = Piwik_View::factory('exportImage');
        $view->imageData = 'data:image/png;base64,' . Piwik_Common::getRequestVar('imageData', self::TRANSPARENT_PNG_PIXEL, 'string', $_POST);
        echo $view->render();
    }

    function exportImage()
    {
        self::exportImageWindow();
    }

    /**
     * Output binary image from base-64 encoded data.
     *
     * @deprecated 1.5.1
     *
     * @param string $imageData Base-64 encoded image data (via $_POST)
     */
    static public function outputBinaryImage()
    {
        Piwik::checkUserHasSomeViewAccess();

        $rawData = Piwik_Common::getRequestVar('imageData', '', 'string', $_POST);

        // returns false if any illegal characters in input
        $data = base64_decode($rawData);
        if ($data !== false) {
            // check for PNG header
            if (Piwik_Common::substr($data, 0, 8) === "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a") {
                header('Content-Type: image/png');

                // more robust validation (if available)
                if (function_exists('imagecreatefromstring')) {
                    // validate image data
                    $imgResource = @imagecreatefromstring($data);
                    if ($imgResource !== false) {
                        // output image and clean-up
                        imagepng($imgResource);
                        imagedestroy($imgResource);
                        exit;
                    }
                } else {
                    echo $data;
                    exit;
                }
            }
        }

        Piwik::setHttpStatus('400 Bad Request');
        exit;
    }

    function outputImage()
    {
        self::outputBinaryImage();
    }

    /**
     * Output the merged CSS file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getCss()
    {
        $cssMergedFile = Piwik_AssetManager::getMergedCssFileLocation();
        Piwik::serveStaticFile($cssMergedFile, "text/css");
    }

    /**
     * Output the merged JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getJs()
    {
        $jsMergedFile = Piwik_AssetManager::getMergedJsFileLocation();
        Piwik::serveStaticFile($jsMergedFile, "application/javascript; charset=UTF-8");
    }

    /**
     * Output redirection page instead of linking directly to avoid
     * exposing the referrer on the Piwik demo.
     *
     * @param string $url (via $_GET)
     */
    public function redirect()
    {
        $url = Piwik_Common::getRequestVar('url', '', 'string', $_GET);

        // validate referrer
        $referrer = Piwik_Url::getReferer();
        if (empty($referrer) || !Piwik_Url::isLocalUrl($referrer)) {
            die('Invalid Referer detected - This means that your web browser is not sending the "Referer URL" which is
				required to proceed with the redirect. Verify your browser settings and add-ons, to check why your browser
				 is not sending this referer.

				<br/><br/>You can access the page at: ' . $url);
        }

        // mask visits to *.piwik.org
        if (!self::isPiwikUrl($url)) {
            Piwik::checkUserHasSomeViewAccess();
        }
        if (!Piwik_Common::isLookLikeUrl($url)) {
            die('Please check the &url= parameter: it should to be a valid URL');
        }
        @header('Content-Type: text/html; charset=utf-8');
        echo '<html><head><meta http-equiv="refresh" content="0;url=' . $url . '" /></head></html>';

        exit;
    }

    /**
     * Validate URL against *.piwik.org domains
     *
     * @param string $url
     * @return bool True if valid; false otherwise
     */
    static public function isPiwikUrl($url)
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
