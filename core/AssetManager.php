<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: AssetManager.php
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik_AssetManager is the class used to manage the inclusion of UI assets: Javascript and CSS files.
 * 
 * It performs the following actions :
 * 	- Identifies required assets
 *  - Includes assets in the rendered HTML page
 *  - Manages asset merging and minifying
 *  - Manages both server-side and client-side cache
 *  
 * Whether assets are included individually or as merged files is defined by the 
 * global option 'disable_merged_assets'. When set to 1, files will be included individually.
 * When set to 0, files will be included within a pair of files: 1 javascript and 1 css file.
 *
 * @package Piwik
 */

require_once ("libs/cssmin/cssmin.php");
require_once ("libs/jsmin/jsmin.php");

class Piwik_AssetManager
{		
	const CSS_IMPORT_EVENT = "AssetManager.getCssFiles";
	const JS_IMPORT_EVENT = "AssetManager.getJsFiles";	
	const MERGED_FILE_DIR = "tmp/assets/";
	const CSS_IMPORT_DIRECTIVE = "<link rel='stylesheet' type='text/css' href='%s' /> \n";
	const JS_IMPORT_DIRECTIVE = "<script type='text/javascript' src='%s'> </script> \n";
	
	/**
	 * Returns CSS file inclusion directive(s) using the markup <link>
	 *
	 * @return string
	 */
	public static function getCssAssets()
	{
		if ( self::getDisableMergedAssets() ) {
			
			// Individual includes mode
			self::removeMergedAsset("css");
			return self::getIndividualCssIncludes();
						
		} else {
			
			return self::getMergedCssInclude();			
		}	
	}
	
	/**
	 * Returns JS file inclusion directive(s) using the markup <script>
	 *
	 * @return string
	 */
	public static function getJsAssets()
	{
		if ( self::getDisableMergedAssets() ) {
			
			// Individual includes mode
			self::removeMergedAsset("js");
			return self::getIndividualJsIncludes();
		
		} else {
			
			return self::getMergedJsInclude();
		}
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
	 * @throws Exception if a file can not be opened in write mode
	 * @return string Hashcode of the merged file.
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
		Piwik_PostEvent(self::CSS_IMPORT_EVENT, $cssFiles);
		return array_unique ( $cssFiles ); 		
	}
	
	/**
	 * Check the validity of the css file
	 *
	 * @throws Exception if css file is not valid
	 * @return boolean
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
	 * @throws Exception if a file can not be opened in write mode
	 * @return string Hashcode of the merged file.
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
			
			$mergedContent = $mergedContent . $content;
		}
		
		$mergedContent = JSMin::minify($mergedContent);
		
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
	
		return $hashcode;
	}
	
	/**
	 * Returns individual JS file inclusion directive(s) using the markup <script>
	 *
	 * @return string
	 */
	private static function getIndividualJsIncludes()   
	{
		$jsIncludeString = '';
		
		$jsFiles = self::getJsFiles();
		
		foreach ($jsFiles as $jsFile) {

			self::validateJsFile ( $jsFile );
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
		Piwik_PostEvent(self::JS_IMPORT_EVENT, $jsFiles);
		return array_unique($jsFiles); 		
	}
	
	/**
	 * Check the validity of the js file
	 *
	 * @throws Exception if js file is not valid
	 * @return boolean
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
	 * @throws Exception if there is more than one file of the same type.
	 * @return string The hashcode of the merged file, false if not present.
	 */
	private static function getMergedAssetHash ($type)
	{	
		$mergedFileDirectory = self::getMergedFileDirectory();
		
		$matchingFiles = glob( $mergedFileDirectory . "*." . $type );
		
		switch ( count($matchingFiles) )
		{
			case 0:				
				return false;
				
			case 1:
				
				$mergedFile = $matchingFiles[0];
				$hashcode = basename($mergedFile, ".".$type);
				
				if ( empty($hashcode) ) {
					throw new Exception ("The merged asset : " . $mergedFile . " couldn't be parsed for getting the hashcode.");
				}
				
				return $hashcode;
				
			default:
				throw Exception ("There are more than 1 merged file of the same type in the merged file directory. This should never happen. Please delete all files in piwik/tmp/assets/ and refresh the page.");	
		}		
	}
	
	/**
	 * Check if the merged file directory exists and is writable.
	 *
	 * @throws Exception if directory is not writable.
	 * @return string The directory location
	 */
	private static function getMergedFileDirectory ()
	{
 		$mergedFileDirectory = self::getAbsoluteLocation(self::MERGED_FILE_DIR);
			
		if (!is_dir($mergedFileDirectory))
		{
			Piwik_Common::mkdir($mergedFileDirectory, 0755, false);
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
	 * @throws Boolean
	 */  
	private static function assetIsReadable ($relativePath)
	{
		return is_readable(self::getAbsoluteLocation($relativePath));
	}

	/**
	 * Returns the full path of an asset file
	 *
	 * @throws string
	 */  
	private static function getAbsoluteLocation ($relativePath)
	{
		return PIWIK_USER_PATH . "/" . $relativePath;
	}	
	
	/**
	 * Returns the full path of the merged file based on its hash.
	 *
	 * @throws string
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
	
}