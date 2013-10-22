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
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\View\ViewInterface;
use Twig_Environment;

/**
 * Transition for pre-Piwik 0.4.4
 */
if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

/**
 * Encapsulates and manages a [Twig](http://twig.sensiolabs.org/) template.
 *
 * View lets you set properties that will be passed on to a Twig template.
 * View will also set several properties that will be available in all Twig
 * templates, including:
 * 
 * - **currentModule**: The value of the 'module' query parameter.
 * - **currentAction**: The value of the 'action' query parameter.
 * - **userLogin**: The current user login name.
 * - **sites**: List of site data for every site the current user has at least
 *              view access for.
 * - **url**: The current URL (sanitized).
 * - **token_auth**: The current user's token auth.
 * - **userHasSomeAdminAccess**: True if the user has admin access to at least
 *                               one site, false if otherwise.
 * - **userIsSuperUser**: True if the user is the superuser, false if otherwise.
 * - **latest_version_available**: The latest version of Piwik available.
 * - **isWidget**: The value of the 'widget' query parameter.
 * - **show_autocompleter**: Whether the site selector should be shown or not.
 * - **loginModule**: The name of the currently used authentication module.
 * - **userAlias**: The alias of the current user.
 * 
 * ### Twig
 * 
 * Twig templates must exist in the **templates** folder in a plugin's root
 * folder.
 * 
 * The following filters are available to twig templates:
 * 
 * - **translate**: Outputs internationalized text using a translation token, eg,
 *                  `{{ 'General_Date'|translate }}`. sprintf parameters can be passed
 *                  to the filter.
 * - **urlRewriteWithParameters**: Modifies the current query string with the given
 *                                 set of parameters, eg,
 *                                 ```
 *                                 {{ {'module':'MyPlugin', 'action':'index'} | urlRewriteWithParameters }}
 *                                 ```
 * - **sumTime**: Pretty formats an number of seconds.
 * - **money**: Formats a numerical value as a monetary value using the currency
 *              of the supplied site (second arg is site ID).
 *              eg, `{{ 23|money(site.idsite)|raw }}
 * - **truncate**: Truncates the text to certain length (determined by first arg.)
 *                 eg, `{{ myReallyLongText|truncate(80) }}`
 * - **implode**: Calls `implode`.
 * - **ucwords**: Calls `ucwords`.
 * 
 * The following functions are available to twig templates:
 * 
 * - **linkTo**: Modifies the current query string with the given set of parameters,
 *               eg `{{ linkTo({'module':'MyPlugin', 'action':'index'}) }}`.
 * - **sparkline**: Outputs a sparkline image HTML element using the sparkline image
 *                  src link. eg, `{{ sparkline(sparklineUrl) }}`.
 * - **postEvent**: Posts an event that allows event observers to add text to a string
 *                  which is outputted in the template, eg, `{{ postEvent('MyPlugin.event') }}`
 * - **isPluginLoaded**: Returns true if the supplied plugin is loaded, false if otherwise.
 *                       `{% if isPluginLoaded('Goals') %}...{% endif %}`
 * 
 * ### Examples
 * 
 * **Basic usage**
 * 
 *     $view = new View("@MyPlugin/myView");
 *     $view->property1 = "a view property";
 *     $view->property2 = "another view property";
 *     echo $view->render();
 * 
 * @package Piwik
 *
 * @api
 */
class View implements ViewInterface
{
    private $template = '';

    /**
     * Instance
     * @var Twig_Environment
     */
    private $twig;
    private $templateVars = array();
    private $contentType = 'text/html; charset=utf-8';
    private $xFrameOptions = null;

    /**
     * Constructor.
     * 
     * @param string $templateFile The template file to load. Must be in the following format:
     *                             `"@MyPlugin/templateFileName"`. Note the absence of .twig
     *                             from the end of the name.
     */
    public function __construct($templateFile)
    {
        $templateExt = '.twig';
        if (substr($templateFile, -strlen($templateExt)) !== $templateExt) {
            $templateFile .= $templateExt;
        }
        $this->template = $templateFile;

        $this->initializeTwig();

        $this->piwik_version = Version::VERSION;
        $this->piwikUrl = Common::sanitizeInputValue(Url::getCurrentUrlWithoutFileName());
    }

    /**
     * Returns the template filename.
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->template;
    }

    /**
     * Returns the variables to bind to the template when rendering.
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    /**
     * Directly assigns a variable to the view script.
     * Variable names may not be prefixed with '_'.
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     */
    public function __set($key, $val)
    {
        $this->templateVars[$key] = $val;
    }

    /**
     * Retrieves an assigned variable.
     * Variable names may not be prefixed with '_'.
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->templateVars[$key];
    }

    private function initializeTwig()
    {
        $piwikTwig = new Twig();
        $this->twig = $piwikTwig->getTwigEnvironment();
    }

    /**
     * Renders the current view. Also sends the stored 'Content-Type' HTML header.
     * See [setContentType](#setContentType).
     *
     * @return string Generated template.
     */
    public function render()
    {
        try {
            $this->currentModule = Piwik::getModule();
            $this->currentAction = Piwik::getAction();
            $userLogin = Piwik::getCurrentUserLogin();
            $this->userLogin = $userLogin;

            $count = SettingsPiwik::getWebsitesCountToDisplay();

            $sites = APISitesManager::getInstance()->getSitesWithAtLeastViewAccess($count);
            usort($sites, function ($site1, $site2) {
                return strcasecmp($site1["name"], $site2["name"]);
            });
            $this->sites = $sites;
            $this->url = Common::sanitizeInputValue(Url::getCurrentUrl());
            $this->token_auth = Piwik::getCurrentUserTokenAuth();
            $this->userHasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();
            $this->userIsSuperUser = Piwik::isUserIsSuperUser();
            $this->latest_version_available = UpdateCheck::isNewestVersionAvailable();
            $this->disableLink = Common::getRequestVar('disableLink', 0, 'int');
            $this->isWidget = Common::getRequestVar('widget', 0, 'int');
            if (Config::getInstance()->General['autocomplete_min_sites'] <= count($sites)) {
                $this->show_autocompleter = true;
            } else {
                $this->show_autocompleter = false;
            }

            $this->loginModule = Piwik::getLoginPluginName();

            $user = APIUsersManager::getInstance()->getUser($userLogin);
            $this->userAlias = $user['alias'];
        } catch (Exception $e) {
            // can fail, for example at installation (no plugin loaded yet)
        }

        try {
            $this->totalTimeGeneration = Registry::get('timer')->getTime();
            $this->totalNumberOfQueries = Profiler::getQueryCount();
        } catch (Exception $e) {
            $this->totalNumberOfQueries = 0;
        }

        ProxyHttp::overrideCacheControlHeaders('no-store');

        @header('Content-Type: ' . $this->contentType);
        // always sending this header, sometimes empty, to ensure that Dashboard embed loads (which could call this header() multiple times, the last one will prevail)
        @header('X-Frame-Options: ' . (string)$this->xFrameOptions);

        return $this->renderTwigTemplate();
    }

    protected function renderTwigTemplate()
    {
        $output = $this->twig->render($this->template, $this->templateVars);
        $output = $this->applyFilter_cacheBuster($output);
        return $output;
    }

    protected function applyFilter_cacheBuster($output)
    {
        $cacheBuster = AssetManager::generateAssetsCacheBuster();
        $tag = 'cb=' . $cacheBuster;

        $pattern = array(
            '~<script type=[\'"]text/javascript[\'"] src=[\'"]([^\'"]+)[\'"]>~',
            '~<script src=[\'"]([^\'"]+)[\'"] type=[\'"]text/javascript[\'"]>~',
            '~<link rel=[\'"]stylesheet[\'"] type=[\'"]text/css[\'"] href=[\'"]([^\'"]+)[\'"] ?/?>~',
            // removes the double ?cb= tag
            '~(src|href)=\"index.php\?module=([A-Za-z0-9_]+)&action=([A-Za-z0-9_]+)\?cb=~',
        );

        $replace = array(
            '<script type="text/javascript" src="$1?' . $tag . '">',
            '<script type="text/javascript" src="$1?' . $tag . '">',
            '<link rel="stylesheet" type="text/css" href="$1?' . $tag . '" />',
            '$1="index.php?module=$2&amp;action=$3&amp;cb=',
        );

        return preg_replace($pattern, $replace, $output);
    }

    /**
     * Set stored value used in the Content-Type HTTP header field. The header is
     * set just before rendering.
     * 
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Set X-Frame-Options field in the HTTP response. The header is set just
     * before rendering.
     * 
     * Note: setting this allows you to make sure the View **cannot** be
     * embedded in iframes. Learn more [here](https://developer.mozilla.org/en-US/docs/HTTP/X-Frame-Options).
     *
     * @param string $option ('deny' or 'sameorigin')
     */
    public function setXFrameOptions($option = 'deny')
    {
        if ($option === 'deny' || $option === 'sameorigin') {
            $this->xFrameOptions = $option;
        }
        if ($option == 'allow') {
            $this->xFrameOptions = null;
        }
    }

    /**
     * Add form to view
     *
     * @param QuickForm2 $form
     * @ignore
     */
    public function addForm(QuickForm2 $form)
    {

        // assign array with form data
        $this->assign('form_data', $form->getFormData());
        $this->assign('element_list', $form->getElementList());
    }

    /**
     * Assign value to a variable for use in a template
     * ToDo: This is ugly.
     * @param string|array $var
     * @param mixed $value
     * @ignore
     */
    public function assign($var, $value = null)
    {
        if (is_string($var)) {
            $this->$var = $value;
        } elseif (is_array($var)) {
            foreach ($var as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Clear compiled Smarty templates
     * @ignore
     */
    static public function clearCompiledTemplates()
    {
        $view = new View(null);
        $view->twig->clearTemplateCache();
    }

    /**
     * Creates a View for and then renders the single report template.
     *
     * @param string $title The report title.
     * @param string $reportHtml The report body HTML.
     * @param bool $fetch If true, return report contents as a string; otherwise echo to screen.
     * @return string|void The report contents if `$fetch` is true.
     */
    static public function singleReport($title, $reportHtml, $fetch = false)
    {
        $view = new View('@CoreHome/_singleReport');
        $view->title = $title;
        $view->report = $reportHtml;

        if ($fetch) {
            return $view->render();
        }
        echo $view->render();
    }
}