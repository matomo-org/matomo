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
 * Transition for pre-Piwik 0.4.4
 */
if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

/**
 * View class to render the user interface
 *
 * @package Piwik
 */
class Piwik_View implements Piwik_View_Interface
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

    public function __construct($templateFile)
    {
        $templateExt = '.twig';
        if(substr($templateFile, -strlen($templateExt)) !== $templateExt) {
            $templateFile .= $templateExt;
        }
        $this->template = $templateFile;

        $this->initializeTwig();

        $this->piwik_version = Piwik_Version::VERSION;
        $this->piwikUrl = Piwik_Common::sanitizeInputValue(Piwik_Url::getCurrentUrlWithoutFileName());
    }

    /**
     * Directly assigns a variable to the view script.
     * VAR names may not be prefixed with '_'.
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
     * VAR names may not be prefixed with '_'.
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->templateVars[$key];
    }

    public function initializeTwig()
    {
        $piwikTwig = new Piwik_Twig();
        $this->twig = $piwikTwig->getTwigEnvironment();
    }

    /**
     * Renders the current view.
     *
     * @return string Generated template
     */
    public function render()
    {
        try {
            $this->currentModule = Piwik::getModule();
            $this->currentAction = Piwik::getAction();
            $userLogin = Piwik::getCurrentUserLogin();
            $this->userLogin = $userLogin;

            $count = Piwik::getWebsitesCountToDisplay();

            $sites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess($count);
            usort($sites, create_function('$site1, $site2', 'return strcasecmp($site1["name"], $site2["name"]);'));
            $this->sites = $sites;
            $this->url = Piwik_Common::sanitizeInputValue(Piwik_Url::getCurrentUrl());
            $this->token_auth = Piwik::getCurrentUserTokenAuth();
            $this->userHasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();
            $this->userIsSuperUser = Piwik::isUserIsSuperUser();
            $this->latest_version_available = Piwik_UpdateCheck::isNewestVersionAvailable();
            $this->disableLink = Piwik_Common::getRequestVar('disableLink', 0, 'int');
            $this->isWidget = Piwik_Common::getRequestVar('widget', 0, 'int');
            if (Piwik_Config::getInstance()->General['autocomplete_min_sites'] <= count($sites)) {
                $this->show_autocompleter = true;
            } else {
                $this->show_autocompleter = false;
            }

            $this->loginModule = Piwik::getLoginPluginName();

            $user = Piwik_UsersManager_API::getInstance()->getUser($userLogin);
            $this->userAlias = $user['alias'];

        } catch (Exception $e) {
            // can fail, for example at installation (no plugin loaded yet)
        }

        try {
            $this->totalTimeGeneration = Zend_Registry::get('timer')->getTime();
            $this->totalNumberOfQueries = Piwik::getQueryCount();
        } catch (Exception $e) {
            $this->totalNumberOfQueries = 0;
        }

        Piwik::overrideCacheControlHeaders('no-store');

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
        $cacheBuster = md5(Piwik_Common::getSalt() . PHP_VERSION . Piwik_Version::VERSION);
        $tag = 'cb=' . $cacheBuster;

        $pattern = array(
            '~<script type=[\'"]text/javascript[\'"] src=[\'"]([^\'"]+)[\'"]>~',
            '~<script src=[\'"]([^\'"]+)[\'"] type=[\'"]text/javascript[\'"]>~',
            '~<link rel=[\'"]stylesheet[\'"] type=[\'"]text/css[\'"] href=[\'"]([^\'"]+)[\'"] ?/?>~',
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
     * Set Content-Type field in HTTP response.
     * Since PHP 5.1.2, header() protects against header injection attacks.
     *
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Set X-Frame-Options field in the HTTP response.
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
     * @param Piwik_QuickForm2 $form
     */
    public function addForm(Piwik_QuickForm2 $form)
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
     */
    static public function clearCompiledTemplates()
    {
        $view = new Piwik_View(null);
        $view->twig->clearTemplateCache();
    }

    /**
     * Render the single report template
     *
     * @param string $title Report title
     * @param string $reportHtml Report body
     * @param bool $fetch If true, return report contents as a string; else echo to screen
     * @return string Report contents if $fetch == true
     */
    static public function singleReport($title, $reportHtml, $fetch = false)
    {
        $view = new Piwik_View('@CoreHome/_singleReport');
        $view->title = $title;
        $view->report = $reportHtml;

        if ($fetch) {
            return $view->render();
        }
        echo $view->render();
    }

    /**
     * @deprecated
     */
    static public function factory($templateName = null)
    {
        throw new Exception("Piwik_View::factory is deprecated. Use 'new Piwik_View(\$templateFile)' instead.");
    }
}
