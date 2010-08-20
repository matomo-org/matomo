<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: AssetManager.php
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * @see libs/cssmin/cssmin.php
 */
require_once PIWIK_INCLUDE_PATH . '/libs/cssmin/cssmin.php';

/**
 * @see libs/jsmin/jsmin.php
 */
require_once PIWIK_INCLUDE_PATH . '/libs/jsmin/jsmin.php';

/**
 * Piwik_AssetManager is the class used to manage the inclusion of UI assets:
 * JavaScript and CSS files.
 * 
 * It performs the following actions:
 * 	- Identifies required assets
 *  - Includes assets in the rendered HTML page
 *  - Manages asset merging and minifying
 *  - Manages both server-side and client-side cache
 *
 * Whether assets are included individually or as merged files is defined by
 * the global option 'disable_merged_assets'. When set to 1, files will be
 * included individually.
 * When set to 0, files will be included within a pair of files: 1 JavaScript
 * and 1 css file.
 *
 * @package Piwik
 */
class Piwik_AssetManager
{		
	const CSS_IMPORT_EVENT = "AssetManager.getCssFiles";
	const JS_IMPORT_EVENT = "AssetManager.getJsFiles";	
	const MERGED_FILE_DIR = "tmp/assets/";
	const CSS_IMPORT_DIRECTIVE = "<link rel='stylesheet' type='text/css' href='%s' /> \n";
	const JS_IMPORT_DIRECTIVE = "<script type='text/javascript' src='%s'> </script> \n";
	const MINIFIED_JS_RATIO = 100;
	
	/**
	 * Returns CSS file inclusion directive(s) using the markup <link>
	 *
	 * @return string
	 */
	public static function getCssAssets()
	{
		if ( self::getDisableMergedAssets() ) 
		{
			// Individual includes mode
			self::removeMergedAsset("css");
			return self::getIndividualCssIncludes();
		} 
		return self::getMergedCssInclude();			
	}
	
	/**
	 * Returns JS file inclusion directive(s) using the markup <script>
	 *
	 * @return string
	 */
	public static function getJsAssets()
	{
		if ( self::getDisableMergedAssets() ) 
		{
			// Individual includes mode
			self::removeMergedAsset("js");
			return self::getIndividualJsIncludes();
		} 
		return self::getMergedJsInclude();
	}
	
	/**
	 * Returns the merged CSS file inclusion directive(s) using the getAsset.php file.
	 *
	 * @return string
	 */
	private static function getMergedCssInclude()   
	{
		// Check existing merged asset
		$mergedCssFileHash = self::getMergedAssetHash("css");	
		
		// Generate asset when none exists
		if ( !$mergedCssFileHash )
		{
			$mergedCssFileHash = self::generateMergedCssFile();
		}

		return sprintf ( self::CSS_IMPORT_DIRECTIVE, self::MERGED_FILE_DIR . $mergedCssFileHash . ".css" );
	}

	/**
	 * Generate the merged css file.
	 *
	 * @return string Hashcode of the merged file.
	 * @throws Exception if a file can not be opened in write mode
	 */
	private static function generateMergedCssFile()
	{
		$mergedContent = "";
		
		// Loop through each css file
		$files = self::getCssFiles();
		foreach ($files as $file) {
			
			self::validateCssFile ( $file );
			
			$fileLocation = self::getAbsoluteLocation($file);
			$content = file_get_contents ($fileLocation);
			
			// Rewrite css url directives
			$baseDirectory = "../../" . dirname($file) . "/";
			$content = preg_replace ("/(url\(['\"]?)([^'\")]*)/", "$1" . $baseDirectory . "$2", $content);
			
			$mergedContent = $mergedContent . $content;
		}

		$mergedContent = cssmin::minify($mergedContent);
		
		// Compute HASH
		$hashcode = md5($mergedContent);
		
		// Remove the previous file
		self::removeMergedAsset("css");
		
		// Tries to open the new file
		$newFilePath = self::getLocationFromHash($hashcode, "css");
		$newFile = fopen($newFilePath, "w");	

		if (!$newFile) {
			throw new Exception ("The file : " . $newFile . " can not be opened in write mode.");
		}
	
		// Write the content in the new file
		fwrite($newFile, $mergedContent);
		fclose($newFile);
		@chmod($newFilePath, 0755);

		return $hashcode;
	}
	
	/**
	 * Returns individual CSS file inclusion directive(s) using the markup <link>
	 *
	 * @return string
	 */
	private static function getIndividualCssIncludes()   
	{
		$cssIncludeString = '';
		
		$cssFiles = self::getCssFiles();
		
		foreach ($cssFiles as $cssFile) {
			
			self::validateCssFile ( $cssFile );	
			$cssIncludeString = $cssIncludeString . sprintf ( self::CSS_IMPORT_DIRECTIVE, $cssFile ); 
		}
		
		return $cssIncludeString;
	}
	
	/**
	 * Returns required CSS files
	 *
	 * @return Array
	 */
	private static function getCssFiles()   
	{
		$cssFiles = array();
		Piwik_PostEvent(self::CSS_IMPORT_EVENT, $cssFiles);
		$cssFiles = self::sortCssFiles($cssFiles);
		return $cssFiles;
	}

	/**
	 * Ensure CSS stylesheets are loaded in a particular order regardless of the order that plugins are loaded.
	 *
	 * @param array $cssFiles Array of CSS stylesheet files
	 * @return array
	 */ 
	private static function sortCssFiles($cssFiles)
	{
		$priorityCssOrdered = array(
			'themes/default/common.css',
			'themes/default/',
			'libs/',
			'plugins/',
		);

		return self::prioritySort($priorityCssOrdered, $cssFiles);
	}

	/**
	 * Check the validity of the css file
	 *
	 * @param string $cssFile CSS file name
	 * @return boolean
	 * @throws Exception if a file can not be opened in write mode
	 */
	private static function validateCssFile ( $cssFile )   
	{
		if(!self::assetIsReadable($cssFile))
		{
			throw new Exception("The css asset with 'href' = " . $cssFile . " is not readable");
		}
	}
	
	/**
	 * Returns the merged JS file inclusion directive(s) using the getAsset.php file.
	 *
	 * @return string
	 */
	private static function getMergedJsInclude()   
	{	
		// Check existing merged asset
		$mergedJsFileHash = self::getMergedAssetHash("js");
		
		// Generate asset when none exists
		if ( !$mergedJsFileHash )
		{
			$mergedJsFileHash = self::generateMergedJsFile();
		}
		
		return sprintf ( self::JS_IMPORT_DIRECTIVE, self::MERGED_FILE_DIR . $mergedJsFileHash . ".js" ); 
	}

	/**
	 * Generate the merged js file.
	 *
	 * @return string Hashcode of the merged file.
	 * @throws Exception if a file can not be opened in write mode
	 */
	private static function generateMergedJsFile()
	{
		$mergedContent = "";
		
		// Loop through each js file
		$files = self::getJsFiles();
		foreach ($files as $file) {
			
			self::validateJsFile ( $file );
			
			$fileLocation = self::getAbsoluteLocation($file);
			$content = file_get_contents ($fileLocation);
			
			if ( !self::isMinifiedJs($content) )
			{
				$content = JSMin::minify($content);
			}
			
			$mergedContent = $mergedContent . PHP_EOL . $content;
		}
		
		// Compute HASH
		$hashcode = md5($mergedContent);
		
		// Remove the previous file
		self::removeMergedAsset("js");
		
		// Tries to open the new file
		$newFilePath = self::getLocationFromHash($hashcode, "js");
		$newFile = fopen($newFilePath, "w");	

		if (!$newFile) {
			throw new Exception ("The file : " . $newFile . " can not be opened in write mode.");
		}
		
		// Write the content in the new file
		fwrite($newFile, $mergedContent);
		fclose($newFile);
		@chmod($newFilePath, 0755);
	
		return $hashcode;
	}
	
	/**
	 * Returns individual JS file inclusion directive(s) using the markup <script>
	 *
	 * @return string
	 */
	private static function getIndividualJsIncludes()   
	{
		$jsFiles = self::getJsFiles();
		$jsIncludeString = '';
		foreach ($jsFiles as $jsFile) 
		{
			self::validateJsFile( $jsFile );
			$jsIncludeString = $jsIncludeString . sprintf ( self::JS_IMPORT_DIRECTIVE, $jsFile );
		}
		return $jsIncludeString;	
	}
	
	/**
	 * Returns required JS files
	 *
	 * @return Array
	 */
	private static function getJsFiles()   
	{
		$jsFiles = array();
		Piwik_PostEvent(self::JS_IMPORT_EVENT, $jsFiles);
		$jsFiles = self::sortJsFiles($jsFiles);
		return $jsFiles; 		
	}
	
	/**
	 * Ensure core JS (jQuery etc.) are loaded in a particular order regardless of the order that plugins are loaded.
	 *
	 * @param array $jsFiles Arry of JavaScript files
	 * @return array
	 */ 
	private static function sortJsFiles($jsFiles)
	{
		$priorityJsOrdered = array(
			'libs/jquery/jquery.js',
			'libs/jquery/jquery-ui.js',
			'libs/',
			'themes/default/common.js',
			'themes/default/',
			'plugins/',
		);

		return self::prioritySort($priorityJsOrdered, $jsFiles);
	}
	
	/**
	 * Check the validity of the js file
	 *
	 * @param string $jsFile JavaScript file name
	 * @return boolean
	 * @throws Exception if js file is not valid
	 */
	private static function validateJsFile ( $jsFile )   
	{
		if(!self::assetIsReadable($jsFile))
		{
			throw new Exception("The js asset with 'src' = " . $jsFile . " is not readable");
		}
	}

	/**
	 * Returns the global option disable_merged_assets
	 *
	 * @return string
	 */
	private static function getDisableMergedAssets()
	{
		return Zend_Registry::get('config')->Debug->disable_merged_assets;
	}

	/**
	 * Gets the hashcode of the merged file according to its type
	 *
	 * @param string $type js|css
	 * @return string The hashcode of the merged file, false if not present.
	 * @throws Exception if there is more than one file of the same type.
	 */
	private static function getMergedAssetHash ($type)
	{	
		$mergedFileDirectory = self::getMergedFileDirectory();
		
		$matchingFiles = _glob( $mergedFileDirectory . "*." . $type );
		
		if($matchingFiles == false)
		{
			return false;
		}
		switch ( count($matchingFiles) )
		{
			case 0:				
				return false;
				
			case 1:
				$mergedFile = $matchingFiles[0];
				$hashcode = basename($mergedFile, ".".$type);
				
				if ( empty($hashcode) ) {
					throw new Exception("The merged asset : " . $mergedFile . " couldn't be parsed for getting the hashcode.");
				}
				return $hashcode;
			default:
				throw new Exception("There are more than 1 merged file of the same type in the merged file directory. 
				This should not happen. Please delete all files in piwik/tmp/assets/ and refresh the page.");
		}		
	}
	
	/**
	 * Check if the merged file directory exists and is writable.
	 *
	 * @return string The directory location
	 * @throws Exception if directory is not writable.
	 */
	private static function getMergedFileDirectory ()
	{
 		$mergedFileDirectory = self::getAbsoluteLocation(self::MERGED_FILE_DIR);
			
		if (!is_dir($mergedFileDirectory))
		{
			Piwik_Common::mkdir($mergedFileDirectory, 0755, false);
			@chmod($mergedFileDirectory, 0755);
		}
		
		if (!is_writable($mergedFileDirectory))
		{
			throw new Exception("Directory " . $mergedFileDirectory . " has to be writable.");
		}

		return $mergedFileDirectory;
	}

	/**
	 * Remove the previous merged file if it exists
	 *
	 * @param string $type js|css
	 * @throws Exception if the file couldn't be deleted
	 */	
	private static function removeMergedAsset($type)
	{
		$mergedAssetHash = self::getMergedAssetHash($type);
		
		if ( $mergedAssetHash != false )
		{
			$previousFileLocation = self::getMergedFileDirectory() . $mergedAssetHash . "." . $type;
			
			if ( !unlink ( $previousFileLocation ) ) {
				throw Exception ("Unable to delete merged file : " . $previousFileLocation . ". Please delete the file and refresh");
			}
		}
	}

	/**
	 * Check if asset is readable
	 *
	 * @param string $relativePath Relative path to file
	 * @return boolean
	 */  
	private static function assetIsReadable ($relativePath)
	{
		return is_readable(self::getAbsoluteLocation($relativePath));
	}

	/**
	 * Returns the full path of an asset file
	 *
	 * @param string $relativePath Relative path to file
	 * @return string
	 */  
	private static function getAbsoluteLocation ($relativePath)
	{
		// served by web server directly, so must be a public path
		return PIWIK_DOCUMENT_ROOT . "/" . $relativePath;
	}	
	
	/**
	 * Returns the full path of the merged file based on its hash.
	 *
	 * @param string $hash Computed hash
	 * @param string $type js|css
	 * @return string
	 */
	private static function getLocationFromHash ( $hash, $type )
	{
		return self::getMergedFileDirectory() . $hash . "." . $type; 
	}
	
	/**
	 * Remove previous merged assets
	 */
	public static function removeMergedAssets()
	{
		self::removeMergedAsset("css");
		self::removeMergedAsset("js");
	}
	
	/**
	 * Indicates if the provided JavaScript content has already been minified or not.
	 * The heuristic is based on a custom ratio : (size of file) / (number of lines).
	 * The threshold (100) has been found empirically on existing files : 
	 * - the ratio never exceeds 50 for non-minified content and
	 * - it never goes under 150 for minified content.
	 *
	 * @param string $content Contents of the JavaScript file
	 * @return boolean
	 */
	private static function isMinifiedJs ( $content )
	{
		$lineCount = substr_count($content, "\n");
		if ( $lineCount == 0 )
		{
			return true;
		}
		
		$contentSize = strlen($content);
		
		$ratio = $contentSize / $lineCount;
		
		return $ratio > self::MINIFIED_JS_RATIO;
	}

	/**
	 * Sort files according to priority order. Duplicates are also removed.
	 *
	 * @param array $priorityOrder Ordered array of paths (first to last) serving as buckets
	 * @param array $files Unsorted array of files
	 * @return array
	 */
	public static function prioritySort($priorityOrder, $files)
	{
		$newFiles = array();
		foreach($priorityOrder as $filePattern)
		{
			$newFiles = array_merge($newFiles, preg_grep('~^' . $filePattern . '~', $files));
		}
		return array_keys(array_flip($newFiles));
	}
}
