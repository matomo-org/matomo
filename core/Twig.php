<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Filter\SafeDecodeLabel;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Manager;
use Piwik\Tracker\GoalManager;
use Piwik\Translation\Translator;
use Piwik\Twig\Extension\EscapeFilter;
use Piwik\View\RenderTokenParser;
use Piwik\Visualization\Sparkline;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * @param string $string
 * @param int $size
 * @return string
 */
function piwik_filter_truncate($string, $size)
{
    if (mb_strlen(html_entity_decode($string)) <= $size) {
        return $string;
    } else {
        preg_match('/^(&(?:[a-z\d]+|#\d+|#x[a-f\d]+);|.){' . $size . '}/i', $string, $shortenString);
        return reset($shortenString) . "...";
    }
}

/**
 * @param string $string
 * @param int $minFractionDigits
 * @param int $maxFractionDigits
 * @return mixed
 */
function piwik_format_number($string, $minFractionDigits, $maxFractionDigits)
{
    $formatter = NumberFormatter::getInstance();
    return $formatter->format($string, $minFractionDigits, $maxFractionDigits);
}

/**
 * @param Environment $env
 * @param string $string
 * @param string $strategy
 * @param ?string $charset
 * @param bool $autoescape
 * @return string
 * @throws \Twig\Error\RuntimeError
 */
function piwik_escape_filter(Environment $env, $string, $strategy = 'html', $charset = null, $autoescape = false)
{
    $string = $env->getRuntime(\Twig\Runtime\EscaperRuntime::class)
        ->escape($string, $strategy, $charset, $autoescape);

    switch ($strategy) {
        case 'url':
            $encoded = rawurlencode('{');
            return str_replace('{{', $encoded . $encoded, $string);
        default:
            return $string;
    }
}

/**
 * @param string|number $amount
 * @param int $idSite
 * @return mixed
 */
function piwik_format_money($amount, $idSite)
{
    $currencySymbol = Site::getCurrencySymbolFor($idSite);
    $numberFormatter = NumberFormatter::getInstance();
    return $numberFormatter->formatCurrency($amount, $currencySymbol, GoalManager::REVENUE_PRECISION);
}

/**
 * Twig class
 *
 */
class Twig
{
    public const SPARKLINE_TEMPLATE = '<img loading="lazy" alt="" data-src="%s" width="%d" height="%d" />
    <script type="text/javascript">$(function() { piwik.initSparklines(); });</script>';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct()
    {
        $loader = $this->getDefaultThemeLoader();
        $this->addPluginNamespaces($loader);

        //get current theme
        $manager = Plugin\Manager::getInstance();
        $theme   = $manager->getThemeEnabled();
        $loaders = [];

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

        $this->twig = new Environment(
            $chainLoader,
            [
                'debug'            => true, // to use {{ dump(var) }} in twig templates
                'strict_variables' => true, // throw an exception if variables are invalid
                'cache'            => $cache,
            ]
        );
        $this->twig->addExtension(new DebugExtension());

        $this->addFilterTranslate();
        $this->addFilterListings();
        $this->addFilterUrlRewriteWithParameters();
        $this->addFilterSumTime();
        $this->addFilterMoney();
        $this->addFilterTruncate();
        $this->addFilterNotification();
        $this->addFilterPercent();
        $this->addFilterPercentage();
        $this->addFilterPercentEvolution();
        $this->addFilterPrettyDate();
        $this->addFilterSafeDecodeRaw();
        $this->addFilterNumber();
        $this->addFilterAnonymiseSystemInfo();
        $this->addFilterNonce();
        $this->addFilterMd5();
        $this->addFilterOnlyDomain();
        $this->addFilterSafelink();
        $this->addFilterTrackMatomoLink();
        $this->addFilterImplode();
        $this->addFilterPreventLinking();
        $this->twig->addFilter(new TwigFilter('ucwords', 'ucwords'));
        $this->twig->addFilter(new TwigFilter('lcfirst', 'lcfirst'));
        $this->twig->addFilter(new TwigFilter('ucfirst', 'ucfirst'));
        $this->twig->addFilter(new TwigFilter('preg_replace', function ($subject, $pattern, $replacement) {
            return preg_replace($pattern, $replacement, $subject);
        }));

        $this->addFunctionExternalLink();
        $this->addFunctionExternalRawLink();
        $this->addFunctionIncludeAssets();
        $this->addFunctionLinkTo();
        $this->addFunctionSparkline();
        $this->addFunctionPostEvent();
        $this->addFunctionIsPluginLoaded();
        $this->addFunctionGetJavascriptTranslations();

        $this->twig->addTokenParser(new RenderTokenParser());

        $this->addTestFalse();
        $this->addTestTrue();
        $this->addTestEmptyString();
        $this->addTestIsNumeric();

        $this->twig->addExtension(new EscapeFilter());
    }

    private function addTestFalse(): void
    {
        $test = new TwigTest(
            'false',
            function ($value) {
                return false === $value;
            }
        );
        $this->twig->addTest($test);
    }

    private function addTestTrue(): void
    {
        $test = new TwigTest(
            'true',
            function ($value) {
                return true === $value;
            }
        );
        $this->twig->addTest($test);
    }

    private function addTestEmptyString(): void
    {
        $test = new TwigTest(
            'emptyString',
            function ($value) {
                return '' === $value;
            }
        );
        $this->twig->addTest($test);
    }

    protected function addFunctionGetJavascriptTranslations(): void
    {
        $getJavascriptTranslations = new TwigFunction(
            'getJavascriptTranslations',
            [StaticContainer::get(Translator::class), 'getJavascriptTranslations']
        );
        $this->twig->addFunction($getJavascriptTranslations);
    }

    protected function addFunctionIsPluginLoaded(): void
    {
        $isPluginLoadedFunction = new TwigFunction('isPluginLoaded', function ($pluginName) {
            return \Piwik\Plugin\Manager::getInstance()->isPluginLoaded($pluginName);
        });
        $this->twig->addFunction($isPluginLoadedFunction);
    }

    protected function addFunctionIncludeAssets(): void
    {
        $includeAssetsFunction = new TwigFunction('includeAssets', function ($params) {
            if (!isset($params['type'])) {
                throw new Exception("The function includeAssets needs a 'type' parameter.");
            }

            $assetType = strtolower($params['type']);
            $deferJs   = boolval($params['defer'] ?? false);
            switch ($assetType) {
                case 'css':
                    return AssetManager::getInstance()->getCssInclusionDirective();
                case 'js':
                    return AssetManager::getInstance()->getJsInclusionDirective($deferJs);
                default:
                    throw new Exception("The twig function includeAssets 'type' parameter needs to be either 'css' or 'js'.");
            }
        });
        $this->twig->addFunction($includeAssetsFunction);
    }

    protected function addFunctionPostEvent(): void
    {
        $postEventFunction = new TwigFunction('postEvent', function ($eventName) {
            // get parameters to twig function
            $params = func_get_args();
            // remove the first value (event name)
            array_shift($params);

            // make the first value the string that will get output in the template
            // plugins can modify this string
            $str    = '';
            $params = array_merge([&$str], $params);

            Piwik::postEvent($eventName, $params);
            return $str;
        }, ['is_safe' => ['html']]);
        $this->twig->addFunction($postEventFunction);
    }

    protected function addFunctionSparkline(): void
    {
        $twigEnv           = $this->getTwigEnvironment();
        $sparklineFunction = new TwigFunction(
            'sparkline',
            /**
             * @param string $src
             * @return string
             */
            function ($src) use ($twigEnv) {
                $width  = Sparkline::DEFAULT_WIDTH;
                $height = Sparkline::DEFAULT_HEIGHT;
                return sprintf(Twig::SPARKLINE_TEMPLATE, piwik_escape_filter($twigEnv, $src, 'html_attr'), $width, $height);
            },
            ['is_safe' => ['html']]
        );
        $this->twig->addFunction($sparklineFunction);
    }

    protected function addFunctionLinkTo(): void
    {
        $urlFunction = new TwigFunction(
            'linkTo',
            /**
             * @param array<string, mixed> $params
             * @return string
             */
            function ($params) {
                return 'index.php' . Url::getCurrentQueryStringWithParametersModified($params);
            }
        );
        $this->twig->addFunction($urlFunction);
    }

    /**
     * Build an external link for a URL
     *
     * Usage:
     *     externallink(url)
     *
     */
    private function addFunctionExternalLink(): void
    {
        $externalLink = new TwigFunction('externallink', function ($url) {
            return Url::getExternalLinkTag($url);
        });
        $this->twig->addFunction($externalLink);
    }

    private function addFunctionExternalRawLink(): void
    {
        $externalRawLink = new TwigFunction('externalrawlink', function ($url) {
            // Add tracking parameters if a matomo.org link
            return Url::addCampaignParametersToMatomoLink($url);
        });
        $this->twig->addFunction($externalRawLink);
    }

    private function getDefaultThemeLoader(): FilesystemLoader
    {
        $themeDir    = Manager::getPluginDirectory(\Piwik\Plugin\Manager::DEFAULT_THEME) . '/templates/';
        $themeLoader = new FilesystemLoader([$themeDir], PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR);

        return $themeLoader;
    }

    /**
     * create template loader for a custom theme
     */
    protected function getCustomThemeLoader(Plugin $theme): ?FilesystemLoader
    {
        $pluginsDir = Manager::getPluginDirectory($theme->getPluginName());
        $themeDir = $pluginsDir . '/templates/';

        if (!file_exists($themeDir)) {
            return null;
        }
        $themeLoader = new FilesystemLoader([$themeDir], PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR);

        return $themeLoader;
    }

    public function getTwigEnvironment(): Environment
    {
        return $this->twig;
    }

    protected function addFilterNotification(): void
    {
        $twigEnv              = $this->getTwigEnvironment();
        $notificationFunction = new TwigFilter('notification', function ($message, $options) use ($twigEnv) {

            $template = '<div style="display:none" data-role="notification" ';

            foreach ($options as $key => $value) {
                if (ctype_alpha($key)) {
                    $template .= sprintf('data-%s="%s" ', $key, piwik_escape_filter($twigEnv, $value, 'html_attr'));
                }
            }

            $template .= '>';

            if (!empty($options['raw'])) {
                $template .= $message;
            } else {
                $template .= piwik_escape_filter($twigEnv, $message, 'html');
            }

            $template .= '</div>';

            return $template;
        }, ['is_safe' => ['html']]);
        $this->twig->addFilter($notificationFunction);
    }

    protected function addFilterSafeDecodeRaw(): void
    {
        $rawSafeDecoded = new TwigFilter(
            'rawSafeDecoded',
            /**
             * @param ?string $string
             * @return string
             */
            function ($string) {
                if ($string === null) {
                    return '';
                }

                $string = str_replace('+', '%2B', $string);
                $string = str_replace('&nbsp;', html_entity_decode('&nbsp;', ENT_COMPAT | ENT_HTML401, 'UTF-8'), $string);

                return SafeDecodeLabel::decodeLabelSafe($string);
            },
            ['is_safe' => ['all']]
        );
        $this->twig->addFilter($rawSafeDecoded);
    }

    protected function addFilterPrettyDate(): void
    {
        $prettyDate = new TwigFilter('prettyDate', function ($dateString, $period) {
            return Period\Factory::build($period, $dateString)->getLocalizedShortString();
        });
        $this->twig->addFilter($prettyDate);
    }

    protected function addFilterPercentage(): void
    {
        $percentage = new TwigFilter('percentage', function ($string, $totalValue, $precision = 1) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercent(Piwik::getPercentageSafe($string, $totalValue, $precision), $precision);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilterPercent(): void
    {
        $percentage = new TwigFilter('percent', function ($string, $precision = 1) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercent($string, $precision);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilterPercentEvolution(): void
    {
        $percentage = new TwigFilter('percentEvolution', function ($string) {
            $formatter = NumberFormatter::getInstance();
            return $formatter->formatPercentEvolution($string);
        });
        $this->twig->addFilter($percentage);
    }

    protected function addFilterNumber(): void
    {
        $formatter = new TwigFilter('number', function ($string, $minFractionDigits = 0, $maxFractionDigits = 0) {
            return piwik_format_number($string, $minFractionDigits, $maxFractionDigits);
        });
        $this->twig->addFilter($formatter);
    }

    protected function addFilterAnonymiseSystemInfo(): void
    {
        $formatter = new TwigFilter(
            'anonymiseSystemInfo',
            /**
             * @param bool|string|null $string
             */
            function ($string) {
                if ($string === null) {
                    return '';
                }
                if (is_bool($string)) {
                    return (int)$string;
                }
                $string = str_replace([PIWIK_DOCUMENT_ROOT, str_replace('/', '\/', PIWIK_DOCUMENT_ROOT)], '$DOC_ROOT', $string);
                $string = str_replace([PIWIK_USER_PATH, str_replace('/', '\/', PIWIK_USER_PATH)], '$USER_PATH', $string);
                $string = str_replace([PIWIK_INCLUDE_PATH, str_replace('/', '\/', PIWIK_INCLUDE_PATH)], '$INCLUDE_PATH', $string);

                // replace anything token like
                $string = preg_replace('/[[:xdigit:]]{31,80}/', 'TOKEN_REPLACED', $string);

                // just in case it was somehow show in a text
                if (SettingsPiwik::isMatomoInstalled()) {
                    $string = str_replace(SettingsPiwik::getPiwikUrl() ?: '', '$MATOMO_URL', $string);
                    $string = str_replace(SettingsPiwik::getSalt(), '$MATOMO_SALT', $string);
                }
                return $string;
            }
        );
        $this->twig->addFilter($formatter);
    }

    protected function addFilterNonce(): void
    {
        $nonce = new TwigFilter('nonce', ['Piwik\\Nonce', 'getNonce']);
        $this->twig->addFilter($nonce);
    }

    private function addFilterMd5(): void
    {
        $md5 = new TwigFilter('md5', function ($value) {
            return md5($value);
        });
        $this->twig->addFilter($md5);
    }

    private function addFilterOnlyDomain(): void
    {
        $domainOnly = new TwigFilter('domainOnly', function ($url) {
            $parsed = parse_url($url);
            return ($parsed['scheme'] ?? '') . '://' . ($parsed['host'] ?? '');
        });
        $this->twig->addFilter($domainOnly);
    }

    protected function addFilterTruncate(): void
    {
        $truncateFilter = new TwigFilter('truncate', function ($string, $size) {
            return piwik_filter_truncate($string, $size);
        });
        $this->twig->addFilter($truncateFilter);
    }

    protected function addFilterMoney(): void
    {
        $moneyFilter = new TwigFilter(
            'money',
            /**
             * @param number|string $amount
             * @param string|int $idSite
             */
            function ($amount, $idSite) {
                return piwik_format_money($amount, $idSite);
            }
        );
        $this->twig->addFilter($moneyFilter);
    }

    protected function addFilterSumTime(): void
    {
        $formatter = $this->formatter;
        $sumtimeFilter = new TwigFilter('sumtime', function ($numberOfSeconds) use ($formatter) {
            return $formatter->getPrettyTimeFromSeconds($numberOfSeconds, true);
        });
        $this->twig->addFilter($sumtimeFilter);
    }

    protected function addFilterUrlRewriteWithParameters(): void
    {
        $urlRewriteFilter = new TwigFilter('urlRewriteWithParameters', function ($parameters) {
            $parameters['updated'] = null;
            return Url::getCurrentQueryStringWithParametersModified($parameters);
        });
        $this->twig->addFilter($urlRewriteFilter);
    }

    protected function addFilterTranslate(): void
    {
        $translateFilter = new TwigFilter('translate', function ($stringToken) {
            if (func_num_args() <= 1) {
                $aValues = [];
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

    protected function addFilterListings(): void
    {
        $andListing = new TwigFilter('andListing', function ($items) {
            if (!is_array($items)) {
                return $items; // don't do anything if input data is incorrect
            }
            return StaticContainer::get(Translator::class)->createAndListing($items);
        });
        $this->twig->addFilter($andListing);

        $orListing = new TwigFilter('orListing', function ($items) {
            if (!is_array($items)) {
                return $items; // don't do anything if input data is incorrect
            }
            return StaticContainer::get(Translator::class)->createOrListing($items);
        });
        $this->twig->addFilter($orListing);
    }

    private function addPluginNamespaces(FilesystemLoader $loader): void
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
    private function addCustomPluginNamespaces(FilesystemLoader $loader, string $pluginName): void
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

    private function addFilterSafelink(): void
    {
        $safelink = new TwigFilter('safelink', function ($url) {
            if (!UrlHelper::isLookLikeSafeUrl($url)) {
                return '';
            }
            return $url;
        });
        $this->twig->addFilter($safelink);
    }

    /**
     * Modify any links to matomo domains to add campaign tracking parameters
     *
     * Typical usage:
     *
     * Apply default campaign tracking parameters:
     * {{ 'https://matomo.org/faq/123'|trackmatomolink }}
     *
     * Apply custom campaign tracking parameters:
     * {{ 'https://matomo.org/faq/123'|trackmatomolink('SomeCampaign', 'SomeSource', 'SomeMedium') }}
     *
     */
    private function addFilterTrackMatomoLink(): void
    {
        $trackLink = new TwigFilter('trackmatomolink', function ($url, $campaign = null, $source = null, $medium = null) {
            return Url::addCampaignParametersToMatomoLink($url, $campaign, $source, $medium);
        });
        $this->twig->addFilter($trackLink);
    }

    private function addFilterImplode(): void
    {
        $implode = new TwigFilter('implode', function ($value, $separator) {
            return implode($separator, $value);
        });
        $this->twig->addFilter($implode);
    }

    private function addFilterPreventLinking(): void
    {
        $preventLinking = new TwigFilter('preventLinking', function ($string) {
            while (preg_match('/\w+\.\w+/i', $string, $matches)) {
                $string = str_replace($matches[0], str_replace('.', '.<!-- -->', $matches[0]), $string);
            }

            return $string;
        }, ['is_safe' => ['all']]);
        $this->twig->addFilter($preventLinking);
    }

    private function addTestIsNumeric(): void
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
