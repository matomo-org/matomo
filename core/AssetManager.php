<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 *  - Identifies required assets
 *  - Includes assets in the rendered HTML page
 *  - Manages asset merging and minifying
 *  - Manages server-side cache
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
    const MERGED_CSS_FILE = "asset_manager_global_css.css";
    const MERGED_JS_FILE = "asset_manager_global_js.js";
    const CSS_IMPORT_EVENT = "AssetManager.getCssFiles";
    const JS_IMPORT_EVENT = "AssetManager.getJsFiles";
    const MERGED_FILE_DIR = "tmp/assets/";
    const CSS_IMPORT_DIRECTIVE = "<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\" />\n";
    const JS_IMPORT_DIRECTIVE = "<script type=\"text/javascript\" src=\"%s\"></script>\n";
    const GET_CSS_MODULE_ACTION = "index.php?module=Proxy&action=getCss";
    const GET_JS_MODULE_ACTION = "index.php?module=Proxy&action=getJs";
    const MINIFIED_JS_RATIO = 100;

    /**
     * Returns CSS file inclusion directive(s) using the markup <link>
     *
     * @return string
     */
    public static function getCssAssets()
    {
        if (self::getDisableMergedAssets()) {
            // Individual includes mode
            self::removeMergedAsset(self::MERGED_CSS_FILE);
            return self::getIndividualCssIncludes();
        }
        return sprintf(self::CSS_IMPORT_DIRECTIVE, self::GET_CSS_MODULE_ACTION);
    }

    /**
     * Returns JS file inclusion directive(s) using the markup <script>
     *
     * @return string
     */
    public static function getJsAssets()
    {
        if (self::getDisableMergedAssets()) {
            // Individual includes mode
            self::removeMergedAsset(self::MERGED_JS_FILE);
            return self::getIndividualJsIncludes();
        }
        return sprintf(self::JS_IMPORT_DIRECTIVE, self::GET_JS_MODULE_ACTION);
    }

    /**
     * Generate the merged css file.
     *
     * @throws Exception if a file can not be opened in write mode
     */
    private static function generateMergedCssFile()
    {
        $mergedContent = "";

        // absolute path to doc root
        $rootDirectory = realpath(PIWIK_DOCUMENT_ROOT);
        if ($rootDirectory != '/' && substr_compare($rootDirectory, '/', -1)) {
            $rootDirectory .= '/';
        }
        $rootDirectoryLen = strlen($rootDirectory);

        // Loop through each css file
        $files = self::getCssFiles();
        foreach ($files as $file) {

            self::validateCssFile($file);

            $fileLocation = self::getAbsoluteLocation($file);
            $content = file_get_contents($fileLocation);

            // Rewrite css url directives
            // - assumes these are all relative paths
            // - rewrite windows directory separator \\ to /
            $baseDirectory = dirname($file);
            $content = preg_replace_callback(
                "/(url\(['\"]?)([^'\")]*)/",
                create_function(
                    '$matches',
                    "return \$matches[1] . str_replace('\\\\', '/', substr(realpath(PIWIK_DOCUMENT_ROOT . '/$baseDirectory/' . \$matches[2]), $rootDirectoryLen));"
                ),
                $content
            );
            $mergedContent = $mergedContent . $content;
        }

        $mergedContent = cssmin::minify($mergedContent);
        $mergedContent = str_replace("\n", "\r\n", $mergedContent);

        Piwik_PostEvent('AssetManager.filterMergedCss', $mergedContent);

        self::writeAssetToFile($mergedContent, self::MERGED_CSS_FILE);
    }

    private static function writeAssetToFile($mergedContent, $name)
    {
        // Remove the previous file
        self::removeMergedAsset($name);

        // Tries to open the new file
        $newFilePath = self::getAbsoluteMergedFileLocation($name);
        $newFile = @fopen($newFilePath, "w");

        if (!$newFile) {
            throw new Exception ("The file : " . $newFile . " can not be opened in write mode.");
        }

        // Write the content in the new file
        fwrite($newFile, $mergedContent);
        fclose($newFile);
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

            self::validateCssFile($cssFile);
            $cssIncludeString = $cssIncludeString . sprintf(self::CSS_IMPORT_DIRECTIVE, $cssFile);
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
            'libs/',
            'themes/default/common.css',
            'themes/default/',
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
    private static function validateCssFile($cssFile)
    {
        if (!self::assetIsReadable($cssFile)) {
            throw new Exception("The css asset with 'href' = " . $cssFile . " is not readable");
        }
    }

    /**
     * Generate the merged js file.
     *
     * @throws Exception if a file can not be opened in write mode
     */
    private static function generateMergedJsFile()
    {
        $mergedContent = "";

        // Loop through each js file
        $files = self::getJsFiles();
        foreach ($files as $file) {

            self::validateJsFile($file);

            $fileLocation = self::getAbsoluteLocation($file);
            $content = file_get_contents($fileLocation);

            if (!self::isMinifiedJs($content)) {
                $content = JSMin::minify($content);
            }

            $mergedContent = $mergedContent . PHP_EOL . $content;
        }
        $mergedContent = str_replace("\n", "\r\n", $mergedContent);

        Piwik_PostEvent('AssetManager.filterMergedJs', $mergedContent);

        self::writeAssetToFile($mergedContent, self::MERGED_JS_FILE);
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
        foreach ($jsFiles as $jsFile) {
            self::validateJsFile($jsFile);
            $jsIncludeString = $jsIncludeString . sprintf(self::JS_IMPORT_DIRECTIVE, $jsFile);
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
            'libs/jquery/jquery.browser.js',
            'libs/',
            'themes/default/common.js',
            'themes/default/',
            'plugins/CoreHome/templates/broadcast.js',
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
    private static function validateJsFile($jsFile)
    {
        if (!self::assetIsReadable($jsFile)) {
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
        return Piwik_Config::getInstance()->Debug['disable_merged_assets'];
    }

    /**
     * Returns the css merged file absolute location.
     * If there is none, the generation process will be triggered.
     *
     * @return string The absolute location of the css merged file
     */
    public static function getMergedCssFileLocation()
    {
        $isGenerated = self::isGenerated(self::MERGED_CSS_FILE);

        if (!$isGenerated) {
            self::generateMergedCssFile();
        }

        return self::getAbsoluteMergedFileLocation(self::MERGED_CSS_FILE);
    }

    /**
     * Returns the js merged file absolute location.
     * If there is none, the generation process will be triggered.
     *
     * @return string The absolute location of the js merged file
     */
    public static function getMergedJsFileLocation()
    {
        $isGenerated = self::isGenerated(self::MERGED_JS_FILE);

        if (!$isGenerated) {
            self::generateMergedJsFile();
        }

        return self::getAbsoluteMergedFileLocation(self::MERGED_JS_FILE);
    }

    /**
     * Check if the provided merged file is generated
     *
     * @param string $filename filename of the merged asset
     * @return boolean true is file exists and is readable, false otherwise
     */
    private static function isGenerated($filename)
    {
        return is_readable(self::getAbsoluteMergedFileLocation($filename));
    }

    /**
     * Removes the previous merged file if it exists.
     * Also tries to remove compressed version of the merged file.
     *
     * @param string $filename filename of the merged asset
     * @see Piwik::serveStaticFile()
     * @throws Exception if the file couldn't be deleted
     */
    private static function removeMergedAsset($filename)
    {
        $isGenerated = self::isGenerated($filename);

        if ($isGenerated) {
            if (!unlink(self::getAbsoluteMergedFileLocation($filename))) {
                throw Exception("Unable to delete merged file : " . $filename . ". Please delete the file and refresh");
            }

            // Tries to remove compressed version of the merged file.
            // See Piwik::serveStaticFile() for more info on static file compression
            $compressedFileLocation = PIWIK_USER_PATH . Piwik::COMPRESSED_FILE_LOCATION . $filename;

            @unlink($compressedFileLocation . ".deflate");
            @unlink($compressedFileLocation . ".gz");
        }
    }

    /**
     * Remove previous merged assets
     */
    public static function removeMergedAssets()
    {
        self::removeMergedAsset(self::MERGED_CSS_FILE);
        self::removeMergedAsset(self::MERGED_JS_FILE);
    }

    /**
     * Check if asset is readable
     *
     * @param string $relativePath Relative path to file
     * @return boolean
     */
    private static function assetIsReadable($relativePath)
    {
        return is_readable(self::getAbsoluteLocation($relativePath));
    }

    /**
     * Check if the merged file directory exists and is writable.
     *
     * @return string The directory location
     * @throws Exception if directory is not writable.
     */
    private static function getMergedFileDirectory()
    {
        $mergedFileDirectory = PIWIK_USER_PATH . '/' . self::MERGED_FILE_DIR;

        if (!is_dir($mergedFileDirectory)) {
            Piwik_Common::mkdir($mergedFileDirectory);
        }

        if (!is_writable($mergedFileDirectory)) {
            throw new Exception("Directory " . $mergedFileDirectory . " has to be writable.");
        }

        return $mergedFileDirectory;
    }

    /**
     * Builds the absolute location of the requested merged file
     *
     * @param string $mergedFile Name of the merge file
     * @return string absolute location of the merged file
     */
    private static function getAbsoluteMergedFileLocation($mergedFile)
    {
        return self::getMergedFileDirectory() . $mergedFile;
    }

    /**
     * Returns the full path of an asset file
     *
     * @param string $relativePath Relative path to file
     * @return string
     */
    private static function getAbsoluteLocation($relativePath)
    {
        // served by web server directly, so must be a public path
        return PIWIK_DOCUMENT_ROOT . "/" . $relativePath;
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
    private static function isMinifiedJs($content)
    {
        $lineCount = substr_count($content, "\n");
        if ($lineCount == 0) {
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
        foreach ($priorityOrder as $filePattern) {
            $newFiles = array_merge($newFiles, preg_grep('~^' . $filePattern . '~', $files));
        }
        return array_keys(array_flip($newFiles));
    }
}
