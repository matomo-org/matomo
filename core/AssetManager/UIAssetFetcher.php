<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\Plugin\Manager;
use Piwik\Theme;

abstract class UIAssetFetcher
{
    /**
     * @var UIAssetCatalog
     */
    protected $catalog;

    /**
     * @var string[]
     */
    protected $fileLocations = array();

    /**
     * @var string[]
     */
    protected $plugins;

    /**
     * @var Theme
     */
    private $theme;

    /**
     * @param string[] $plugins
     * @param Theme $theme
     */
    public function __construct($plugins, $theme)
    {
        $this->plugins = $plugins;
        $this->theme = $theme;
    }

    /**
     * @return string[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * $return UIAssetCatalog
     */
    public function getCatalog()
    {
        if ($this->catalog == null) {
            $this->createCatalog();
        }

        return $this->catalog;
    }

    abstract protected function retrieveFileLocations();

    /**
     * @return string[]
     */
    abstract protected function getPriorityOrder();

    private function createCatalog()
    {
        $this->retrieveFileLocations();

        $this->initCatalog();

        $this->populateCatalog();

        $this->sortCatalog();
    }

    private function initCatalog()
    {
        $catalogSorter = new UIAssetCatalogSorter($this->getPriorityOrder());
        $this->catalog = new UIAssetCatalog($catalogSorter);
    }

    private function populateCatalog()
    {
        $pluginBaseDir = Manager::getPluginsDirectory();
        $pluginWebDirectories = Manager::getAlternativeWebRootDirectories();
        $matomoRootDir = $this->getBaseDirectory();

        foreach ($this->fileLocations as $fileLocation) {
            $fileAbsolute = $matomoRootDir . '/' . $fileLocation;

            $newUIAsset = new OnDiskUIAsset($this->getBaseDirectory(), $fileLocation);
            if ($newUIAsset->exists()) {
                $this->catalog->addUIAsset($newUIAsset);
                continue;
            }

            $found = false;

            if (strpos($fileAbsolute, $pluginBaseDir) === 0) {
                // we iterate over all custom plugin directories only for plugin files, not libs files (not needed there)
                foreach ($pluginWebDirectories as $pluginDirectory => $relative) {
                    $fileTest = str_replace($pluginBaseDir, $pluginDirectory, $fileAbsolute);
                    $newFileRelative = str_replace($pluginDirectory, '', $fileTest);
                    $testAsset = new OnDiskUIAsset($pluginDirectory, $newFileRelative, $relative);
                    if ($testAsset->exists()) {
                        $this->catalog->addUIAsset($testAsset);
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                // we add it anyway so it'll trigger an error about the missing file
                $this->catalog->addUIAsset($newUIAsset);
            }
        }
    }

    private function sortCatalog()
    {
        $this->catalog = $this->catalog->getSortedCatalog();
    }

    /**
     * @return string
     */
    private function getBaseDirectory()
    {
        // served by web server directly, so must be a public path
        return PIWIK_DOCUMENT_ROOT;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    public static $bowerComponentFileMappings = [
        'libs/bower_components/jquery/dist/jquery.min.js' => 'node_modules/jquery/dist/jquery.min.js',
        'libs/bower_components/jquery-ui/ui/minified/jquery-ui.min.js' => 'node_modules/jquery-ui-dist/jquery-ui.min.js',
        "libs/bower_components/sprintf/dist/sprintf.min.js" => "node_modules/sprintf-js/dist/sprintf.min.js",
        "libs/bower_components/materialize/dist/js/materialize.min.js" => "node_modules/materialize-css/dist/js/materialize.min.js",
        "libs/bower_components/jquery.scrollTo/jquery.scrollTo.min.js" => "node_modules/jquery.scrollto/jquery.scrollTo.min.js",
        "libs/bower_components/mousetrap/mousetrap.min.js" => "node_modules/mousetrap/mousetrap.min.js",
        "libs/bower_components/angular/angular.min.js" => 'node_modules/angular/angular.min.js',
        "libs/bower_components/angular-sanitize/angular-sanitize.min.js" => "node_modules/angular-sanitize/angular-sanitize.min.js",
        "libs/bower_components/angular-animate/angular-animate.min.js" => "node_modules/angular-animate/angular-animate.min.js",
        "libs/bower_components/angular-cookies/angular-cookies.min.js" => "node_modules/angular-cookies/angular-cookies.min.js",
        "libs/bower_components/ngDialog/js/ngDialog.min.js" => "node_modules/ng-dialog/js/ngDialog.min.js",
        "libs/bower_components/jQuery.dotdotdot/src/js/jquery.dotdotdot.min.js" => "node_modules/jquery.dotdotdot/dist/jquery.dotdotdot.js",
        "libs/bower_components/visibilityjs/lib/visibility.core.js" => "node_modules/visibilityjs/lib/visibility.core.js",
        "libs/bower_components/iframe-resizer/js/iframeResizer.min.js" => "node_modules/iframe-resizer/js/iframeResizer.min.js",
        "libs/bower_components/qrcode.js/qrcode.js" => "node_modules/qrcodejs2/qrcode.min.js",
        "libs/bower_components/chroma-js/chroma.min.js" => "node_modules/chroma-js/chroma.min.js",
        "libs/jquery/jquery.browser.js" => "node_modules/jquery.browser/dist/jquery.browser.min.js",
        "plugins/CoreHome/angularjs/dialogtoggler/dialogtoggler.directive.js" => null,
        "plugins/CoreHome/angularjs/dialogtoggler/dialogtoggler.controller.js" => null,
        "plugins/CoreHome/angularjs/dialogtoggler/dialogtoggler-urllistener.service.js" => null,
        "libs/jquery/jquery.truncate.js" => null,

        "libs/jquery/themes/base/jquery-ui.min.css" => "node_modules/jquery-ui-dist/jquery-ui.min.css",
        "libs/bower_components/materialize/dist/css/materialize.min.css" => "node_modules/materialize-css/dist/css/materialize.min.css",
        "node_modules/jquery-ui-dist/jquery-ui.theme.min.css" => "node_modules/jquery-ui-dist/jquery-ui.theme.min.css",
        "libs/bower_components/ngDialog/css/ngDialog.min.css" => null,
        "libs/bower_components/ngDialog/css/ngDialog-theme-default.min.css" => null,
        "plugins/CoreHome/angularjs/dialogtoggler/ngdialog.less" => null,
    ];

    protected function mapBowerComponentFilesForBC(array &$fileLocations)
    {
        foreach ($fileLocations as $index => $location) {
            if (!isset(self::$bowerComponentFileMappings[$location])) {
                continue;
            }

            if (self::$bowerComponentFileMappings[$location] === null) {
                unset($fileLocations[$index]);
            } else {
                $fileLocations[$index] = self::$bowerComponentFileMappings[$location];
            }
        }
    }
}
