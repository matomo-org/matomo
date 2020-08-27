<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Plugin\Manager;

/**
 * This class contains logic to make Themes work beautifully.
 *
 */
class Theme
{
    /** @var string  */
    private $themeName;

    /** @var \Piwik\Plugin  */
    private $theme;

    /**
     * @var Plugin $plugin
     */
    public function __construct($plugin = false)
    {
        $this->createThemeFromPlugin($plugin ? $plugin : Manager::getInstance()->getThemeEnabled());
    }

    /**
     * @param Plugin $plugin
     */
    private function createThemeFromPlugin($plugin)
    {
        $this->theme = $plugin;
        $this->themeName = $plugin->getPluginName();
    }

    public function getStylesheet()
    {
        if ($this->themeName == \Piwik\Plugin\Manager::DEFAULT_THEME) {
            return false;
        }

        $info = $this->theme->getInformation();
        if (!isset($info['stylesheet'])) {
            return false;
        }
        $themeStylesheet = 'plugins/' . $this->theme->getPluginName() . '/' . $info['stylesheet'];
        return $themeStylesheet;
    }

    public function getJavaScriptFiles()
    {
        if ($this->themeName == \Piwik\Plugin\Manager::DEFAULT_THEME) {
            return false;
        }

        $info = $this->theme->getInformation();
        if (empty($info['javascript'])) {
            return false;
        }
        $jsFiles = $info['javascript'];
        if (!is_array($jsFiles)) {
            $jsFiles = array($jsFiles);
        }
        foreach ($jsFiles as &$jsFile) {
            $jsFile = 'plugins/' . $this->theme->getPluginName() . '/' . $jsFile;
        }
        return $jsFiles;
    }

    public function rewriteAssetsPathToTheme($output)
    {
        if ($this->themeName == \Piwik\Plugin\Manager::DEFAULT_THEME
            && !Manager::getAlternativeWebRootDirectories()) {
            return $output;
        }

        $pattern = array(
            // Rewriting scripts includes to overrides
            '~<script type=[\'"]text/javascript[\'"] (src)=[\'"]([^\'"]+)[\'"]>~',
            '~<script (src)=[\'"]([^\'"]+)[\'"] type=[\'"]text/javascript[\'"]>~',
            '~<link (rel)=[\'"]stylesheet[\'"] type=[\'"]text/css[\'"] href=[\'"]([^\'"]+)[\'"] ?/?>~',

            // Images as well
            '~(src|href)=[\'"]([^\'"]+)[\'"]~',

            // rewrite images in CSS files
            '~(url\()[\'"]([^\)]?[plugins]+[^\)]+[.jpg|png|gif|svg]?)[\'"][\)]~',

            // url(plugins/....)
            '~(url\()([^\)]?[plugins]+[^\)]+[.jpg|png|gif|svg]?)[\)]~',

            // rewrites images in JS files
            '~(=)[\s]?[\'"]([^\'"]+[.jpg|.png|.gif|svg]?)[\'"]~',
        );
        return preg_replace_callback($pattern, array($this, 'rewriteAssetPathIfOverridesFound'), $output);
    }

    private function rewriteAssetPathIfOverridesFound($src)
    {
        $source = $src[0];
        $pathAsset = $src[2];

        // Basic health check, we don't replace if not starting with plugins/
        $posPluginsInPath = strpos($pathAsset, 'plugins');
        if ($posPluginsInPath !== 0) {
            return $source;
        }

        // or if it's already rewritten
        if (strpos($pathAsset, $this->themeName) !== false) {
            return $source;
        }

        $pathPluginName = substr($pathAsset, strlen('plugins/'));
        $nextSlash = strpos($pathPluginName, '/');
        if ($nextSlash === false) {
            return $source;
        }
        $pathPluginName = substr($pathPluginName, 0, $nextSlash);

        // replace all plugin assets to the theme, if the theme overrides this asset
        // when there are name conflicts (two plugins define the same asset name in same folder),
        // we shall rename so there is no more conflict.
        $defaultThemePath = "plugins/" . $pathPluginName;
        $newThemePath = "plugins/" . $this->themeName;
        $overridingAsset = str_replace($defaultThemePath, $newThemePath, $pathAsset);

        // Strip trailing query string
        $fileToCheck = $overridingAsset;
        $queryStringPos = strpos($fileToCheck, '?');
        if ($queryStringPos !== false) {
            $fileToCheck = substr($fileToCheck, 0, $queryStringPos);
        }

        if (file_exists($fileToCheck)) {
            return str_replace($pathAsset, $overridingAsset, $source);
        }

        // not rewritten by theme, but may be located in custom webroot directory
        foreach (Manager::getAlternativeWebRootDirectories() as $absDir => $webRootDirectory) {
            $withoutPlugins = str_replace('plugins/', '', $pathAsset);
            if (file_exists($absDir . '/' . $withoutPlugins)) {
	            return str_replace($pathAsset, $webRootDirectory . $withoutPlugins, $source);
            }
        }

        return $source;
    }

    /**
     * @return string
     */
    public function getThemeName()
    {
        return $this->themeName;
    }
}
