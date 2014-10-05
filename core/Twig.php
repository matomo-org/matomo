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
use Piwik\DataTable\Filter\SafeDecodeLabel;
use Piwik\Period\Range;
use Piwik\Translate;
use Piwik\View\RenderTokenParser;
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

		//get current theme
		$manager = Plugin\Manager::getInstance();
		$theme   = $manager->getThemeEnabled();
		$loaders = array();

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
        $templatesCompiledPath = PIWIK_USER_PATH . '/tmp/templates_c';
        $templatesCompiledPath = SettingsPiwik::rewriteTmpPathWithInstanceId($templatesCompiledPath);

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
        $this->addFilter_percentage();
        $this->addFilter_prettyDate();
        $this->addFilter_safeDecodeRaw();
        $this->twig->addFilter(new Twig_SimpleFilter('implode', 'implode'));
        $this->twig->addFilter(new Twig_SimpleFilter('ucwords', 'ucwords'));

        $this->addFunction_includeAssets();
        $this->addFunction_linkTo();
        $this->addFunction_sparkline();
        $this->addFunction_postEvent();
        $this->addFunction_isPluginLoaded();
        $this->addFunction_getJavascriptTranslations();

        $this->twig->addTokenParser(new RenderTokenParser());
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
            $params = array_merge( array( &$str ), $params);

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
	protected function getCustomThemeLoader(Plugin $theme){
		if (!file_exists(sprintf("%s/plugins/%s/templates/", PIWIK_INCLUDE_PATH, $theme->getPluginName()))){
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

            return SafeDecodeLabel::decodeLabelSafe($string);

        }, array('is_safe' => array('all')));
        $this->twig->addFilter($rawSafeDecoded);
    }

    protected function addFilter_prettyDate()
    {
        $prettyDate = new Twig_SimpleFilter('prettyDate', function ($dateString, $period) {
            return Range::factory($period, $dateString)->getLocalizedShortString();
        });
        $this->twig->addFilter($prettyDate);
    }

    protected function addFilter_percentage()
    {
        $percentage = new Twig_SimpleFilter('percentage', function ($string, $totalValue, $precision = 1) {
            return Piwik::getPercentageSafe($string, $totalValue, $precision) . '%';
        });
        $this->twig->addFilter($percentage);
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
        $plugins = \Piwik\Plugin\Manager::getInstance()->getAllPluginsNames();
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
        $plugins = \Piwik\Plugin\Manager::getInstance()->getAllPluginsNames();
        foreach ($plugins as $name) {
            $path = sprintf("%s/plugins/%s/templates/plugins/%s/", PIWIK_INCLUDE_PATH, $pluginName, $name);
            if (is_dir($path)) {
                $loader->addPath(PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/templates/plugins/'. $name , $name);
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
