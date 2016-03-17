<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Filter\SafeDecodeLabel;
use Piwik\Metrics\Formatter;
use Piwik\Tracker\GoalManager;
use Piwik\View\RenderTokenParser;
use Piwik\Visualization\Sparkline;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Twig_SimpleTest;

function piwik_filter_truncate($string, $size)
{
    if (strlen($string) < $size) {
        return $string;
    } else {
        $array = str_split($string, $size);
        return array_shift($array) . "...";
    }
}

function piwik_format_number($string, $minFractionDigits, $maxFractionDigits)
{
    $formatter = NumberFormatter::getInstance();
    return $formatter->format($string, $minFractionDigits, $maxFractionDigits);
}

function piwik_fix_lbrace($string)
{
    $chars = array('{', '&#x7B;', '&#123;', '&lcub;', '&lbrace;', '&#x0007B;');

    static $search;
    static $replace;

    if (!isset($search)) {
        $search = array_map(function ($val) { return $val . $val; }, $chars);
    }
    if (!isset($replace)) {
        $replace = array_map(function ($val) { return $val . '&#8291;' . $val; }, $chars);
    }

    return str_replace($search, $replace, $string);
}

function piwik_escape_filter(Twig_Environment $env, $string, $strategy = 'html', $charset = null, $autoescape = false) {

    $string = twig_escape_filter($env, $string, $strategy, $charset, $autoescape);

    switch ($strategy) {
        case 'html':
        case 'html_attr':
            return piwik_fix_lbrace($string);
        case 'url':
            $encoded = rawurlencode('{');
            return str_replace('{{', $encoded . $encoded, $string);
        case 'css':
        case 'js':
        default:
            return $string;
    }
}

function piwik_format_money($amount, $idSite)
{
    $currencySymbol = Site::getCurrencySymbolFor($idSite);
    $numberFormatter = NumberFormatter::getInstance();
    return $numberFormatter->formatCurrency($amount, $currencySymbol, GoalManager::REVENUE_PRECISION);
}

class PiwikTwigFilterExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('e', '\Piwik\piwik_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe')),
            new Twig_SimpleFilter('escape', '\Piwik\piwik_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe'))
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'escaper2';
    }
}

/**
 * Twig class
 *
 */
class Twig
{
    const SPARKLINE_TEMPLATE = '<img alt="" data-src="%s" width="%d" height="%d" />
    <script type="text/javascript">$(function() { piwik.initSparklines(); });</script>';

    /**
     * @var Twig_Environment
     */
    private $twig;

    private $formatter;

    public function __construct()
    {
        $loader = $this->getDefaultThemeLoader();
        $this->addPluginNamespaces($loader);

        //get current theme
        $manager = Plugin\Manager::getInstance();
        $theme   = $manager->getThemeEnabled();
        $loaders = array();

        $this->formatter = new Formatter();

        //create loader for custom theme to overwrite twig templates
        if ($theme && $theme->getPluginName() != \Piwik\Plugin\Manager::DEFAULT_THEME) {
            $customLoader = $this->getCustomThemeLoader($theme);
            if ($customLoader) {
                //make it possible to overwrite plugin templates
                $this->addCustomPluginNamespaces($customLoader, $theme->getPluginName());
                $loaders[] = $customLoader;
            }
        }

        $loaders[] = $loader;

        $chainLoader = new Twig_Loader_Chain($loaders);

        // Create new Twig Environment and set cache dir
        $templatesCompiledPath = StaticContainer::get('path.tmp') . '/templates_c';

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
        $this->addFilter_notification();
        $this->addFilter_percent();
        $this->addFilter_percentage();
        $this->addFilter_percentEvolution();
        $this->addFilter_piwikProAdLink();
        $this->addFilter_piwikProOnPremisesAdLink();
        $this->addFilter_piwikProCloudAdLink();
        $this->addFilter_prettyDate();
        $this->addFilter_safeDecodeRaw();
        $this->addFilter_number();
        $this->twig->addFilter(new Twig_SimpleFilter('implode', 'implode'));
        $this->twig->addFilter(new Twig_SimpleFilter('ucwords', 'ucwords'));
        $this->twig->addFilter(new Twig_SimpleFilter('lcfirst', 'lcfirst'));

        $this->addFunction_includeAssets();
        $this->addFunction_linkTo();
        $this->addFunction_sparkline();
        $this->addFunction_postEvent();
        $this->addFunction_isPluginLoaded();
        $this->addFunction_getJavascriptTranslations();

        $this->twig->addTokenParser(new RenderTokenParser());

        $this->addTest_false();
        $this->addTest_true();
        $this->addTest_emptyString();

        $this->twig->addExtension(new PiwikTwigFilterExtension());
    }

    private function addTest_false()
    {
        $test = new Twig_SimpleTest(
            'false',
            function ($value) {
                return false === $value;
            }
        );
        $this->twig->addTest($test);
    }

    private function addTest_true()
    {
        $test = new Twig_SimpleTest(
            'true',
            function ($value) {
                return true === $value;
            }
        );
        $this->twig->addTest($test);
    }

    private function addTest_emptyString()
    {
        $test = new Twig_SimpleTest(
            'emptyString',
            function ($value) {
                return '' === $value;
            }
        );
        $this->twig->addTest($test);
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
                    return AssetManager::getInstance()->getCssInclusionDirective();
                case 'js':
                    return AssetManager::getInstance()->getJsInclusionDirective();
                default:
                    throw new Exception("The twig function includeAssets 'type' parameter needs to be either 'css' or 'js'.");
            }
        });
        $this->twig->addFunction($includeAssetsFunction);
    }

    protected function addFunction_postEvent()
    {
        $postEventFunction = new Twig_SimpleFunction('postEvent', function ($eventName) {
            // get parameters to twig function
            $params = func_get_args();
            // remove the first value (event name)
            array_shift($params);

            // make the first value the string that will get output in the template
            // plugins can modify this string
            $str = '';
            $params = array_merge(array( &$str ), $params);

            Piwik::postEvent($eventName, $params);
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

    /**
     * create template loader for a custom theme
     * @param \Piwik\Plugin $theme
     * @return \Twig_Loader_Filesystem
     */
    protected function getCustomThemeLoader(Plugin $theme)
    {
        if (!file_exists(sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, $theme->getPluginName()))) {
            return false;
        }
        $themeLoader = new Twig_Loader_Filesystem(array(
                                                       sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, $theme->getPluginName())
                                                  ));

        return $themeLoader;
    }

    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    protected function addFilter_notification()
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

            if (!empty($options['raw'])) {
                $template .= $message;
            } else {
                $template .= twig_escape_filter($twigEnv, $message, 'html');
            }

            $template .= '</div>';

            return $template;

        }, array('is_safe' => array('html')));
        $this->twig->addFilter($notificationFunction);
    }

    protected function addFilter_safeDecodeRaw()
    {
        $rawSafeDecoded = new Twig_SimpleFilter('rawSafeDecoded', function ($string) {
            $string = str_replace('+', '%2B', $string);
            $string = str_replace('&nbsp;', html_entity_decode('&nbsp;'), $string);

            $string = SafeDecodeLabel::decodeLabelSafe($string);

            return piwik_fix_lbrace($string);

        }, array('is_safe' => array('all')));
        $this->twig->addFilter($rawSafeDecoded);
    }

    protected function addFilter_prettyDate()
    {
        $prettyDate = new Twig_SimpleFilter('prettyDate', function ($dateString, $period) {
            return Period\Factory::build($period, $dateString)->getLocalizedShortString();
        });
        $this->twig->addFilter($prettyDate);
    }

    protected function addFilter_percentage()
    {
        $percentage = new Twig_SimpleFilter('percentage', function ($string, $totalValue, $precision = 1) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercent(Piwik::getPercentageSafe($string, $totalValue, $precision), $precision);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilter_percent()
    {
        $percentage = new Twig_SimpleFilter('percent', function ($string, $precision = 1) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercent($string, $precision);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilter_percentEvolution()
    {
        $percentage = new Twig_SimpleFilter('percentEvolution', function ($string) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercentEvolution($string);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilter_piwikProAdLink()
    {
        $ads = $this->getPiwikProAdvertising();
        $piwikProAd = new Twig_SimpleFilter('piwikProCampaignParameters', function ($url, $campaignName, $campaignMedium, $campaignContent = '') use ($ads) {
            $url = $ads->addPromoCampaignParametersToUrl($url, $campaignName, $campaignMedium, $campaignContent);
            return $url;
        });
        $this->twig->addFilter($piwikProAd);
    }

    protected function addFilter_piwikProOnPremisesAdLink()
    {
        $twigEnv = $this->getTwigEnvironment();
        $ads = $this->getPiwikProAdvertising();
        $piwikProAd = new Twig_SimpleFilter('piwikProOnPremisesPromoUrl', function ($medium, $content = '') use ($twigEnv, $ads) {

            $url = $ads->getPromoUrlForOnPremises($medium, $content);

            return twig_escape_filter($twigEnv, $url, 'html_attr');

        }, array('is_safe' => array('html_attr')));
        $this->twig->addFilter($piwikProAd);
    }

    protected function addFilter_piwikProCloudAdLink()
    {
        $twigEnv = $this->getTwigEnvironment();
        $ads = $this->getPiwikProAdvertising();
        $piwikProAd = new Twig_SimpleFilter('piwikProCloudPromoUrl', function ($medium, $content = '') use ($twigEnv, $ads) {

            $url = $ads->getPromoUrlForCloud($medium, $content);

            return twig_escape_filter($twigEnv, $url, 'html_attr');

        }, array('is_safe' => array('html_attr')));
        $this->twig->addFilter($piwikProAd);
    }

    private function getPiwikProAdvertising()
    {
        return StaticContainer::get('Piwik\PiwikPro\Advertising');
    }

    protected function addFilter_number()
    {
        $formatter = new Twig_SimpleFilter('number', function ($string, $minFractionDigits = 0, $maxFractionDigits = 0) {
            return piwik_format_number($string, $minFractionDigits, $maxFractionDigits);
        });
        $this->twig->addFilter($formatter);
    }

    protected function addFilter_truncate()
    {
        $truncateFilter = new Twig_SimpleFilter('truncate', function ($string, $size) {
            return piwik_filter_truncate($string, $size);
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
            return piwik_format_money($amount, $idSite);
        });
        $this->twig->addFilter($moneyFilter);
    }

    protected function addFilter_sumTime()
    {
        $formatter = $this->formatter;
        $sumtimeFilter = new Twig_SimpleFilter('sumtime', function ($numberOfSeconds) use ($formatter) {
            return $formatter->getPrettyTimeFromSeconds($numberOfSeconds, true);
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
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $pluginManager->getAllPluginsNames();
        foreach ($plugins as $name) {
            $path = sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, $name);
            if (is_dir($path)) {
                $loader->addPath(PIWIK_INCLUDE_PATH . '/plugins/' . $name . '/templates', $name);
            }
        }
    }

    /**
    *
    * Plugin-Templates can be overwritten by putting identically named templates in plugins/[theme]/templates/plugins/[plugin]/
    *
    */
    private function addCustomPluginNamespaces(Twig_Loader_Filesystem $loader, $pluginName)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $pluginManager->getAllPluginsNames();
        foreach ($plugins as $name) {
            $path = sprintf("%s/plugins/%s/templates/plugins/%s/", PIWIK_INCLUDE_PATH, $pluginName, $name);
            if (is_dir($path)) {
                $loader->addPath(PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/templates/plugins/'. $name, $name);
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
