<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
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
	 * @param string $imageData Base-64 encoded image data (via $_POST)
	 */
	function exportImage()
	{
		Piwik::checkUserHasSomeViewAccess();

		$view = Piwik_View::factory('exportImage');
		$view->imageData = 'data:image/png;base64,'. Piwik_Common::getRequestVar('imageData', self::TRANSPARENT_PNG_PIXEL, 'string', $_POST);
		echo $view->render();
	}
	
	/**
	 * Output image from base-64 encoded data.
	 *
	 * @param string $imageData Base-64 encoded image data (via $_POST)
	 */
	function outputImage()
	{
		Piwik::checkUserHasSomeViewAccess();

		header('Content-Type: image/png');
		$data = base64_decode(Piwik_Common::getRequestVar('imageData', self::TRANSPARENT_PNG_PIXEL, 'string', $_POST));

		if(function_exists('imagecreatefromstring'))
		{
			// validate image data
			$imgResource = imagecreatefromstring($data);
			if($imgResource !== false)
			{
				// output image and clean-up
				imagepng($imgResource);
				imagedestroy($imgResource);
			}
		}
		else
		{
			echo $data;
		}
		exit;
	}

	/**
	 * Output the merged CSS file.
	 * This method is called when the asset manager is enabled.
	 * 
	 * @see core/AssetManager.php
	 */
	public function getCss ()
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
	public function getJs ()
	{
		$jsMergedFile = Piwik_AssetManager::getMergedJsFileLocation();
		Piwik::serveStaticFile($jsMergedFile, "application/javascript; charset=UTF-8");
	}

	/**
	 * Output the CSS3PIE PIE.htc file
	 *
	 * @see /libs/CSS3PIE
	 */
	public function getPieHtc ()
	{
		Piwik::serveStaticFile(PIWIK_INCLUDE_PATH ."/libs/CSS3PIE/PIE.htc", "text/x-component");
	}	

	/**
	 * Output redirection page instead of linking directly to avoid
	 * exposing the referer on the Piwik demo.
	 *
	 * @param string $url (via $_GET)
	 */
	public function redirect()
	{
		// validate url against whitelist
		$url = Piwik_Common::getRequestVar('url', '', 'string', $_GET);
		if(!Piwik_Url::isAcceptableRemoteUrl($url))
		{
			exit;
		}

		// validate referer
		$referer = Piwik_Url::getReferer();
		if(!empty($referer) && (Piwik_Url::getLocalReferer() === false))
		{
			exit;
		}

		echo
'<html><head>
<meta http-equiv="refresh" content="0;url=' . $url . '" />
</head></html>';

	}
}
