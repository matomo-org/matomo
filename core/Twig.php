<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Filter\SafeDecodeLabel;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Manager;
use Piwik\Tracker\GoalManager;
use Piwik\View\RenderTokenParser;
use Piwik\Visualization\Sparkline;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

function piwik_filter_truncate($string, $size)
{
    if (mb_strlen(html_entity_decode($string)) <= $size) {
        return $string;
    } else {
        preg_match('/^(&(?:[a-z\d]+|#\d+|#x[a-f\d]+);|.){'.$size.'}/i', $string, $shortenString);
        return reset($shortenString) . "...";
    }
}

function piwik_format_number($string, $minFractionDigits, $maxFractionDigits)
{
    $formatter = NumberFormatter::getInstance();
    return $formatter->format($string, $minFractionDigits, $maxFractionDigits);
}

function piwik_fix_lbrace($string)
{
    return Common::fixLbrace($string);
}

function piwik_escape_filter(Environment $env, $string, $strategy = 'html', $charset = null, $autoescape = false) {

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

class PiwikTwigFilterExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('e', '\Piwik\piwik_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe')),
            new TwigFilter('escape', '\Piwik\piwik_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe'))
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
    const SPARKLINE_TEMPLATE = '<img loading="lazy" alt="" data-src="%s" width="%d" height="%d" />
    <script type="text/javascript">$(function() { piwik.initSparklines(); });</script>';

    /**
     * @var Environment
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

        $chainLoader = new ChainLoader($loaders);

        // Create new Twig Environment and set cache dir
        $cache = StaticContainer::get('twig.cache');

        $this->twig = new Environment($chainLoader,
            array(
                 'debug'            => true, // to use {{ dump(var) }} in twig templates
                 'strict_variables' => true, // throw an exception if variables are invalid
                 'cache'            => $cache,
            )
        );
        $this->twig->addExtension(new DebugExtension());

        $this->addFilter_translate();
        $this->addFilter_urlRewriteWithParameters();
        $this->addFilter_sumTime();
        $this->addFilter_money();
        $this->addFilter_truncate();
        $this->addFilter_notification();
        $this->addFilter_percent();
        $this->addFilter_percentage();
        $this->addFilter_percentEvolution();
        $this->addFilter_prettyDate();
        $this->addFilter_safeDecodeRaw();
        $this->addFilter_number();
        $this->addFilter_anonymiseSystemInfo();
        $this->addFilter_nonce();
        $this->addFilter_md5();
        $this->addFilter_onlyDomain();
        $this->addFilter_safelink();
        $this->addFilter_implode();
        $this->twig->addFilter(new TwigFilter('ucwords', 'ucwords'));
        $this->twig->addFilter(new TwigFilter('lcfirst', 'lcfirst'));
        $this->twig->addFilter(new TwigFilter('ucfirst', 'ucfirst'));
        $this->twig->addFilter(new TwigFilter('preg_replace', function ($subject, $pattern, $replacement) {
            return preg_replace($pattern, $replacement, $subject);
        }));

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
        $this->addTest_isNumeric();

        $this->twig->addExtension(new PiwikTwigFilterExtension());
    }

    private function addTest_false()
    {
        $test = new TwigTest(
            'false',
            function ($value) {
                return false === $value;
            }
        );
        $this->twig->addTest($test);
    }

    private function addTest_true()
    {
        $test = new TwigTest(
            'true',
            function ($value) {
                return true === $value;
            }
        );
        $this->twig->addTest($test);
    }

    private function addTest_emptyString()
    {
        $test = new TwigTest(
            'emptyString',
            function ($value) {
                return '' === $value;
            }
        );
        $this->twig->addTest($test);
    }

    protected function addFunction_getJavascriptTranslations()
    {
        $getJavascriptTranslations = new TwigFunction(
            'getJavascriptTranslations',
            array(StaticContainer::get('Piwik\Translation\Translator'), 'getJavascriptTranslations')
        );
        $this->twig->addFunction($getJavascriptTranslations);
    }

    protected function addFunction_isPluginLoaded()
    {
        $isPluginLoadedFunction = new TwigFunction('isPluginLoaded', function ($pluginName) {
            return \Piwik\Plugin\Manager::getInstance()->isPluginLoaded($pluginName);
        });
        $this->twig->addFunction($isPluginLoadedFunction);
    }

    protected function addFunction_includeAssets()
    {
        $includeAssetsFunction = new TwigFunction('includeAssets', function ($params) {
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
        $postEventFunction = new TwigFunction('postEvent', function ($eventName) {
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
        $sparklineFunction = new TwigFunction('sparkline', function ($src) {
            $width = Sparkline::DEFAULT_WIDTH;
            $height = Sparkline::DEFAULT_HEIGHT;
            return sprintf(Twig::SPARKLINE_TEMPLATE, $src, $width, $height);
        }, array('is_safe' => array('html')));
        $this->twig->addFunction($sparklineFunction);
    }

    protected function addFunction_linkTo()
    {
        $urlFunction = new TwigFunction('linkTo', function ($params) {
            return 'index.php' . Url::getCurrentQueryStringWithParametersModified($params);
        });
        $this->twig->addFunction($urlFunction);
    }

    /**
     * @return FilesystemLoader
     */
    private function getDefaultThemeLoader()
    {
        $themeDir = Manager::getPluginDirectory(\Piwik\Plugin\Manager::DEFAULT_THEME) . '/templates/';
        $themeLoader = new FilesystemLoader(array($themeDir), PIWIK_DOCUMENT_ROOT.DIRECTORY_SEPARATOR);

        return $themeLoader;
    }

    /**
     * create template loader for a custom theme
     * @param \Piwik\Plugin $theme
     * @return FilesystemLoader|bool
     */
    protected function getCustomThemeLoader(Plugin $theme)
    {
        $pluginsDir = Manager::getPluginDirectory($theme->getPluginName());
        $themeDir = $pluginsDir . '/templates/';

        if (!file_exists($themeDir)) {
            return false;
        }
        $themeLoader = new FilesystemLoader(array($themeDir), PIWIK_DOCUMENT_ROOT.DIRECTORY_SEPARATOR);

        return $themeLoader;
    }

    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    protected function addFilter_notification()
    {
        $twigEnv = $this->getTwigEnvironment();
        $notificationFunction = new TwigFilter('notification', function ($message, $options) use ($twigEnv) {

            $template = '<div style="display:none" data-role="notification" ';

            foreach ($options as $key => $value) {
                if (ctype_alpha($key)) {
                    $template .= sprintf('data-%s="%s" ', $key, twig_escape_filter($twigEnv, $value, 'html_attr'));
                }
            }

            $template .= '>';

            if (!empty($options['raw'])) {
                $template .= piwik_fix_lbrace($message);
            } else {
                $template .= piwik_escape_filter($twigEnv, $message, 'html');
            }

            $template .= '</div>';

            return $template;

        }, array('is_safe' => array('html')));
        $this->twig->addFilter($notificationFunction);
    }

    protected function addFilter_safeDecodeRaw()
    {
        $rawSafeDecoded = new TwigFilter('rawSafeDecoded', function ($string) {

            if ($string === null) {
                return '';
            }

            $string = str_replace('+', '%2B', $string);
            $string = str_replace('&nbsp;', html_entity_decode('&nbsp;', ENT_COMPAT | ENT_HTML401, 'UTF-8'), $string);

            $string = SafeDecodeLabel::decodeLabelSafe($string);

            return piwik_fix_lbrace($string);

        }, array('is_safe' => array('all')));
        $this->twig->addFilter($rawSafeDecoded);
    }

    protected function addFilter_prettyDate()
    {
        $prettyDate = new TwigFilter('prettyDate', function ($dateString, $period) {
            return Period\Factory::build($period, $dateString)->getLocalizedShortString();
        });
        $this->twig->addFilter($prettyDate);
    }

    protected function addFilter_percentage()
    {
        $percentage = new TwigFilter('percentage', function ($string, $totalValue, $precision = 1) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercent(Piwik::getPercentageSafe($string, $totalValue, $precision), $precision);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilter_percent()
    {
        $percentage = new TwigFilter('percent', function ($string, $precision = 1) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercent($string, $precision);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilter_percentEvolution()
    {
        $percentage = new TwigFilter('percentEvolution', function ($string) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercentEvolution($string);
        });
        $this->twig->addFilter($percentage);
    }

    private function getProfessionalServicesAdvertising()
    {
        return StaticContainer::get('Piwik\ProfessionalServices\Advertising');
    }

    protected function addFilter_number()
    {
        $formatter = new TwigFilter('number', function ($string, $minFractionDigits = 0, $maxFractionDigits = 0) {
            return piwik_format_number($string, $minFractionDigits, $maxFractionDigits);
        });
        $this->twig->addFilter($formatter);
    }

    protected function addFilter_anonymiseSystemInfo()
    {
        $formatter = new TwigFilter('anonymiseSystemInfo', function ($string) {
            if ($string === null) {
                return '';
            }
            if ($string === false || $string === true) {
                return (int) $string;
            }
            $string = str_replace([PIWIK_DOCUMENT_ROOT,  str_replace( '/', '\/', PIWIK_DOCUMENT_ROOT )], '$DOC_ROOT', $string);
            $string = str_replace([PIWIK_USER_PATH,  str_replace( '/', '\/', PIWIK_USER_PATH ) ], '$USER_PATH', $string);
            $string = str_replace([PIWIK_INCLUDE_PATH,  str_replace( '/', '\/', PIWIK_INCLUDE_PATH ) ], '$INCLUDE_PATH', $string);

            // replace anything token like
            $string = preg_replace('/[[:xdigit:]]{31,80}/', 'TOKEN_REPLACED', $string);

            // just in case it was somehow show in a text
            if (SettingsPiwik::isMatomoInstalled()) {
                $string = str_replace(SettingsPiwik::getPiwikUrl(), '$MATOMO_URL', $string);
                $string = str_replace(SettingsPiwik::getSalt(), '$MATOMO_SALT', $string);
            }
            return $string;
        });
        $this->twig->addFilter($formatter);
    }

    protected function addFilter_nonce()
    {
        $nonce = new TwigFilter('nonce', array('Piwik\\Nonce', 'getNonce'));
        $this->twig->addFilter($nonce);
    }

    private function addFilter_md5()
    {
        $md5 = new TwigFilter('md5', function ($value) {
            return md5($value);
        });
        $this->twig->addFilter($md5);
    }

    private function addFilter_onlyDomain()
    {
        $domainOnly = new TwigFilter('domainOnly', function ($url) {
            $parsed = parse_url($url);
            return $parsed['scheme'] . '://' . $parsed['host'];
        });
        $this->twig->addFilter($domainOnly);
    }

    protected function addFilter_truncate()
    {
        $truncateFilter = new TwigFilter('truncate', function ($string, $size) {
            return piwik_filter_truncate($string, $size);
        });
        $this->twig->addFilter($truncateFilter);
    }

    protected function addFilter_money()
    {
        $moneyFilter = new TwigFilter('money', function ($amount) {
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
        $sumtimeFilter = new TwigFilter('sumtime', function ($numberOfSeconds) use ($formatter) {
            return $formatter->getPrettyTimeFromSeconds($numberOfSeconds, true);
        });
        $this->twig->addFilter($sumtimeFilter);
    }

    protected function addFilter_urlRewriteWithParameters()
    {
        $urlRewriteFilter = new TwigFilter('urlRewriteWithParameters', function ($parameters) {
            $parameters['updated'] = null;
            $url = Url::getCurrentQueryStringWithParametersModified($parameters);
            return $url;
        });
        $this->twig->addFilter($urlRewriteFilter);
    }

    protected function addFilter_translate()
    {
        $translateFilter = new TwigFilter('translate', function ($stringToken) {
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

    private function addPluginNamespaces(FilesystemLoader $loader)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $pluginManager->getAllPluginsNames();

        foreach ($plugins as $name) {
            $pluginsDir = Manager::getPluginDirectory($name);
            $path = sprintf("%s/templates/", $pluginsDir);
            if (is_dir($path)) {
                $loader->addPath(rtrim($path, '/'), $name);
            }
        }
    }

    /**
    *
    * Plugin-Templates can be overwritten by putting identically named templates in plugins/[theme]/templates/plugins/[plugin]/
    *
    */
    private function addCustomPluginNamespaces(FilesystemLoader $loader, $pluginName)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $pluginManager->getAllPluginsNames();

        $pluginsDir = Manager::getPluginDirectory($pluginName);

        foreach ($plugins as $name) {
            $path = sprintf("%s/templates/plugins/%s/", $pluginsDir, $name);
            if (is_dir($path)) {
                $loader->addPath(rtrim($path, '/'), $name);
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

    private function addFilter_safelink()
    {
        $safelink = new TwigFilter('safelink', function ($url) {
            if (!UrlHelper::isLookLikeSafeUrl($url)) {
                return '';
            }
            return $url;
        });
        $this->twig->addFilter($safelink);
    }

    private function addFilter_implode()
    {
        $implode = new TwigFilter('implode', function ($value, $separator) {
            return implode($separator, $value);
        });
        $this->twig->addFilter($implode);
    }

    private function addTest_isNumeric()
    {
        $test = new TwigTest(
            'numeric',
            function ($value) {
                return is_numeric($value);
            }
        );
        $this->twig->addTest($test);
    }
}
