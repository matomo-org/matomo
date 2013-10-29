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
namespace Piwik;

use Exception;
use Piwik\Translate;
use Piwik\Visualization\Sparkline;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Twig class
 *
 * @package Piwik
 * @subpackage Twig
 */
class Twig
{
    const SPARKLINE_TEMPLATE = '<img alt="" data-src="%s" width="%d" height="%d" />
    <script type="text/javascript">$(function() { piwik.initSparklines(); });</script>';

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct()
    {
        $loader = $this->getDefaultThemeLoader();

        $this->addPluginNamespaces($loader);

        // If theme != default we need to chain
        $chainLoader = new Twig_Loader_Chain(array($loader));

        // Create new Twig Environment and set cache dir
        $templatesCompiledPath = PIWIK_USER_PATH . '/tmp/templates_c';
        $templatesCompiledPath = SettingsPiwik::rewriteTmpPathWithHostname($templatesCompiledPath);

        $this->twig = new Twig_Environment($chainLoader,
            array(
                 'debug'            => true, // to use {{ dump(var) }} in twig templates
                 'strict_variables' => true, // throw an exception if variables are invalid
                 'cache'            => $templatesCompiledPath,
            )
        );
        $this->twig->addExtension(new Twig_Extension_Debug());
        $this->twig->clearTemplateCache();

        $this->addFilter_translate();
        $this->addFilter_urlRewriteWithParameters();
        $this->addFilter_sumTime();
        $this->addFilter_money();
        $this->addFilter_truncate();
        $this->addFilter_notificiation();
        $this->twig->addFilter(new Twig_SimpleFilter('implode', 'implode'));
        $this->twig->addFilter(new Twig_SimpleFilter('ucwords', 'ucwords'));

        $this->addFunction_includeAssets();
        $this->addFunction_linkTo();
        $this->addFunction_sparkline();
        $this->addFunction_postEvent();
        $this->addFunction_isPluginLoaded();
        $this->addFunction_getJavascriptTranslations();
    }

    protected function addFunction_getJavascriptTranslations()
    {
        $getJavascriptTranslations = new Twig_SimpleFunction(
            'getJavascriptTranslations',
            array('Piwik\\Translate', 'getJavascriptTranslations')
        );
        $this->twig->addFunction($getJavascriptTranslations);
    }

    protected function addFunction_isPluginLoaded()
    {
        $isPluginLoadedFunction = new Twig_SimpleFunction('isPluginLoaded', function ($pluginName) {
            return \Piwik\Plugin\Manager::getInstance()->isPluginLoaded($pluginName);
        });
        $this->twig->addFunction($isPluginLoadedFunction);
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
                    return AssetManager::getCssAssets();
                case 'js':
                    return AssetManager::getJsAssets();
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
            Piwik::postEvent($eventName, array(&$str));
            return $str;
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($postEventFunction);
    }

    protected function addFunction_sparkline()
    {
        $sparklineFunction = new Twig_SimpleFunction('sparkline', function ($src) {
            $width = Sparkline::DEFAULT_WIDTH;
            $height = Sparkline::DEFAULT_HEIGHT;
            return sprintf(Twig::SPARKLINE_TEMPLATE, $src, $width, $height);
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($sparklineFunction);
    }

    protected function addFunction_linkTo()
    {
        $urlFunction = new Twig_SimpleFunction('linkTo', function ($params) {
            return 'index.php' . Url::getCurrentQueryStringWithParametersModified($params);
        });
        $this->twig->addFunction($urlFunction);
    }

    /**
     * @return Twig_Loader_Filesystem
     */
    private function getDefaultThemeLoader()
    {
        $themeLoader = new Twig_Loader_Filesystem(array(
                                                       sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, \Piwik\Plugin\Manager::DEFAULT_THEME)
                                                  ));

        return $themeLoader;
    }

    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    protected function addFilter_notificiation()
    {
        $twigEnv = $this->getTwigEnvironment();
        $notificationFunction = new Twig_SimpleFilter('notification', function ($message, $options) use ($twigEnv) {

            $template = '<div style="display:none" data-role="notification" ';

            foreach ($options as $key => $value) {
                if (ctype_alpha($key)) {
                    $template .= sprintf('data-%s="%s" ', $key, twig_escape_filter($twigEnv, $value, 'html_attr'));
                }
            }

            $template .= '>';
            $template .= $message;
            $template .= '</div>';

            return $template;

        }, array('is_safe' => array('html')));
        $this->twig->addFilter($notificationFunction);
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
            return MetricsFormatter::getPrettyMoney($amount, $idSite);
        });
        $this->twig->addFilter($moneyFilter);
    }

    protected function addFilter_sumTime()
    {
        $sumtimeFilter = new Twig_SimpleFilter('sumtime', function ($numberOfSeconds) {
            return MetricsFormatter::getPrettyTimeFromSeconds($numberOfSeconds);
        });
        $this->twig->addFilter($sumtimeFilter);
    }

    protected function addFilter_urlRewriteWithParameters()
    {
        $urlRewriteFilter = new Twig_SimpleFilter('urlRewriteWithParameters', function ($parameters) {
            $parameters['updated'] = null;
            $url = Url::getCurrentQueryStringWithParametersModified($parameters);
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
                $stringTranslated = Piwik::translate($stringToken, $aValues);
            } catch (Exception $e) {
                $stringTranslated = $stringToken;
            }
            return $stringTranslated;
        });
        $this->twig->addFilter($translateFilter);
    }

    private function addPluginNamespaces(Twig_Loader_Filesystem $loader)
    {
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
        foreach ($plugins as $name) {
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
