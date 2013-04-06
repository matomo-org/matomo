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
 * Twig class
 *
 * @package Piwik
 * @subpackage Piwik_Twig
 */
class Piwik_Twig
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Default theme used in Piwik.
     */
    const DEFAULT_THEME="Zeitgeist";

    public function __construct($theme = self::DEFAULT_THEME)
    {
        $loader = $this->getDefaultThemeLoader();

        $this->addPluginNamespaces($loader);

        // If theme != default we need to chain
        $chainLoader = new Twig_Loader_Chain(array($loader));

        // Create new Twig Environment and set cache dir
        $this->twig = new Twig_Environment($chainLoader,
            array(
                //'cache' => PIWIK_DOCUMENT_ROOT . '/tmp/templates_c',
            )
        );
        $this->twig->clearTemplateCache();

        // Add default filters
        $this->addFilters();
        // Register namespaces
        $includeAssetsFunction = new Twig_SimpleFunction('includeAssets', function ($params) {
            if (!isset($params['type'])) {
                throw new Exception("The smarty function includeAssets needs a 'type' parameter.");
            }

            $assetType = strtolower($params['type']);
            switch ($assetType) {
                case 'css':

                    return Piwik_AssetManager::getCssAssets();

                case 'js':

                    return Piwik_AssetManager::getJsAssets();

                default:
                    throw new Exception("The twig function includeAssets 'type' parameter needs to be either 'css' or 'js'.");
            }
        });
        $this->twig->addFunction($includeAssetsFunction);
        $urlFunction = new Twig_SimpleFunction('url', function($params) {
            return 'index.php' . Piwik_Url::getCurrentQueryStringWithParametersModified($params);
        });
        $this->twig->addFunction($urlFunction);

        $loadJsTranslationsFunction = new Twig_SimpleFunction('loadJavascriptTranslations', function(array $plugins, $disableScriptTag = false) {
            static $pluginTranslationsAlreadyLoaded = array();
            if (in_array($plugins, $pluginTranslationsAlreadyLoaded)) {
                return;
            }
            $pluginTranslationsAlreadyLoaded[] = $plugins;
            $jsTranslations = Piwik_Translate::getInstance()->getJavascriptTranslations($plugins);
            $jsCode = '';
            if ($disableScriptTag) {
                $jsCode .= $jsTranslations;
            } else {
                $jsCode .= '<script type="text/javascript">';
                $jsCode .= $jsTranslations;
                $jsCode .= '</script>';
            }
            return $jsCode;
        });
        $this->twig->addFunction($loadJsTranslationsFunction);

        $sparklineFunction = new Twig_SimpleFunction('sparkline', function($src) {
            $graph = new Piwik_Visualization_Sparkline();
            $width = $graph->getWidth();
            $height = $graph->getHeight();
            return sprintf('<img class="sparkline" alt="" src="%s" width="%d" height="%d" />', $src, $width, $height);
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($sparklineFunction);

        $postEventFunction = new Twig_SimpleFunction('postEvent', function($eventName) {
            $str = '';
            Piwik_PostEvent($eventName, $str);
            return $str;
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($postEventFunction);
    }

    /**
     * @return Twig_Loader_Filesystem
     */
    private function getDefaultThemeLoader()
    {
        $themeLoader = new Twig_Loader_Filesystem(array(
            sprintf("%s/plugins/%s/theme/", PIWIK_INCLUDE_PATH, self::DEFAULT_THEME)
        ));

        return $themeLoader;
    }

    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    public function initFilters()
    {
        /*
        $this->load_filter('output', 'cachebuster');

        $use_ajax_cdn = Piwik_Config::getInstance()->General['use_ajax_cdn'];
        if ($use_ajax_cdn) {
            $this->load_filter('output', 'ajaxcdn');
        }

        $this->load_filter('output', 'trimwhitespace');*/
    }

    private function addFilters()
    {
        $translateFilter = new Twig_SimpleFilter('translate', function ($stringToken) {
            if (func_num_args() <= 1) {
                $aValues = array();
            } else {
                $aValues = func_get_args();
                array_shift($aValues);
            }

            try {
                $stringTranslated = Piwik_Translate($stringToken, $aValues);
            } catch (Exception $e) {
                $stringTranslated = $stringToken;
            }
            return $stringTranslated;
        });
        $this->twig->addFilter($translateFilter);

        $urlRewriteFilter = new Twig_SimpleFilter('urlRewriteWithParameters', function ($parameters) {
            $parameters['updated'] = null;
            $url = Piwik_Url::getCurrentQueryStringWithParametersModified($parameters);
            return $url;
        });
        $this->twig->addFilter($urlRewriteFilter);

        $sumtimeFilter = new Twig_SimpleFilter('sumtime', function($numberOfSeconds) {
            return Piwik::getPrettyTimeFromSeconds($numberOfSeconds);
        });
        $this->twig->addFilter($sumtimeFilter);

        $moneyFilter = new Twig_SimpleFilter('money', function($amount)
        {
            if (func_num_args() != 2) {
                throw new Exception('the smarty modifier money expects one parameter: the idSite.');
            }
            $idSite = func_get_args();
            $idSite = $idSite[1];
            return Piwik::getPrettyMoney($amount, $idSite);
        });
        $this->twig->addFilter($moneyFilter);
    }

    private function addPluginNamespaces(Twig_Loader_Filesystem $loader)
    {
        $plugins = Piwik_PluginsManager::getInstance()->getLoadedPluginsName();
        foreach($plugins as $name) {
            $name = Piwik::unprefixClass($name);
            $path = sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, $name);
            if (is_dir($path)) {
                $loader->addPath(PIWIK_INCLUDE_PATH . '/plugins/' . $name . '/templates', $name);
            }
        }
    }

    /**
     * Prepend relative paths with absolute Piwik path
     *
     * @param string $value relative path (pass by reference)
     * @param int $key (don't care)
     * @param string $path Piwik root
     */
    public static function addPiwikPath(&$value, $key, $path)
    {
        if ($value[0] != '/' && $value[0] != DIRECTORY_SEPARATOR) {
            $value = $path . "/$value";
        }
    }
}