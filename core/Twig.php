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
                 'debug' => true, // to use {{ dump(var) }} in twig templates
                 'strict_variables' => true, // throw an exception if variables are invalid
                //'cache' => PIWIK_DOCUMENT_ROOT . '/tmp/templates_c',
            )
        );
        $this->twig->addExtension(new Twig_Extension_Debug());
        $this->twig->clearTemplateCache();

        $this->addFilter_translate();
        $this->addFilter_urlRewriteWithParameters();
        $this->addFilter_sumTime();
        $this->addFilter_money();
        $this->addFilter_truncate();

        $this->twig->addFilter( new Twig_SimpleFilter('implode', 'implode'));
        /*
        $this->load_filter('output', 'trimwhitespace');*/


        $this->addFunction_includeAssets();
        $this->addFunction_linkTo();
        $this->addFunction_loadJavascriptTranslations();
        $this->addFunction_sparkline();
        $this->addFunction_postEvent();
    }

    protected function addFunction_includeAssets()
    {
        $includeAssetsFunction = new Twig_SimpleFunction('includeAssets', function ($params) {
            if (!isset($params['type'])) {
                throw new Exception("The function includeAssets needs a 'type' parameter.");
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
    }

    protected function addFunction_postEvent()
    {
        $postEventFunction = new Twig_SimpleFunction('postEvent', function ($eventName) {
            $str = '';
            Piwik_PostEvent($eventName, $str);
            return $str;
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($postEventFunction);
    }

    protected function addFunction_sparkline()
    {
        $sparklineFunction = new Twig_SimpleFunction('sparkline', function ($src) {
            $graph = new Piwik_Visualization_Sparkline();
            $width = $graph->getWidth();
            $height = $graph->getHeight();
            return sprintf('<img class="sparkline" alt="" src="%s" width="%d" height="%d" />', $src, $width, $height);
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($sparklineFunction);
    }

    protected function addFunction_loadJavascriptTranslations()
    {
        $loadJsTranslationsFunction = new Twig_SimpleFunction('loadJavascriptTranslations', function (array $plugins, $disableScriptTag = false) {
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
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($loadJsTranslationsFunction);
    }

    protected function addFunction_linkTo()
    {
        $urlFunction = new Twig_SimpleFunction('linkTo', function ($params) {
            return 'index.php' . Piwik_Url::getCurrentQueryStringWithParametersModified($params);
        });
        $this->twig->addFunction($urlFunction);
    }

    /**
     * @return Twig_Loader_Filesystem
     */
    private function getDefaultThemeLoader()
    {
        $themeLoader = new Twig_Loader_Filesystem(array(
            sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, self::DEFAULT_THEME)
        ));

        return $themeLoader;
    }

    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    protected function addFilter_truncate()
    {
        $truncateFilter = new Twig_SimpleFilter('truncate', function ($string, $size) {
            if (strlen($string) < $size) {
                return $string;
            } else {
                $array = str_split($string, $size);
                return array_shift($array) . "...";
            }
        });
        $this->twig->addFilter($truncateFilter);
    }

    protected function addFilter_money()
    {
        $moneyFilter = new Twig_SimpleFilter('money', function ($amount) {
            if (func_num_args() != 2) {
                throw new Exception('the money modifier expects one parameter: the idSite.');
            }
            $idSite = func_get_args();
            $idSite = $idSite[1];
            return Piwik::getPrettyMoney($amount, $idSite);
        });
        $this->twig->addFilter($moneyFilter);
    }

    protected function addFilter_sumTime()
    {
        $sumtimeFilter = new Twig_SimpleFilter('sumtime', function ($numberOfSeconds) {
            return Piwik::getPrettyTimeFromSeconds($numberOfSeconds);
        });
        $this->twig->addFilter($sumtimeFilter);
    }

    protected function addFilter_urlRewriteWithParameters()
    {
        $urlRewriteFilter = new Twig_SimpleFilter('urlRewriteWithParameters', function ($parameters) {
            $parameters['updated'] = null;
            $url = Piwik_Url::getCurrentQueryStringWithParametersModified($parameters);
            return $url;
        });
        $this->twig->addFilter($urlRewriteFilter);
    }

    protected function addFilter_translate()
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