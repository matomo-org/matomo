<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Settings\Setting;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Url;
use Piwik\View;
use Piwik\Plugin\Manager as PluginManager;

class OptOutManager
{
    /** @var DoNotTrackHeaderChecker */
    private $doNotTrackHeaderChecker;

    /** @var array */
    private $javascripts;

    /** @var array */
    private $stylesheets;

    /** @var string */
    private $title;

    /** @var View|null */
    private $view;

    /** @var array */
    private $queryParameters = array();

    /**
     * @param DoNotTrackHeaderChecker $doNotTrackHeaderChecker
     */
    public function __construct(DoNotTrackHeaderChecker $doNotTrackHeaderChecker = null)
    {
        $this->doNotTrackHeaderChecker = $doNotTrackHeaderChecker ?: new DoNotTrackHeaderChecker();

        $this->javascripts = array(
            'inline' => array(),
            'external' => array(),
        );

        $this->stylesheets = array(
            'inline' => array(),
            'external' => array(),
        );
    }

    /**
     * Add a javascript file|code into the OptOut View
     * Note: This method will not escape the inline javascript code!
     *
     * @param string $javascript
     * @param bool $inline
     */
    public function addJavaScript($javascript, $inline = true)
    {
        $type = $inline ? 'inline' : 'external';
        $this->javascripts[$type][] = $javascript;
    }

    /**
     * @return array
     */
    public function getJavaScripts()
    {
        return $this->javascripts;
    }

    /**
     * Add a stylesheet file|code into the OptOut View
     * Note: This method will not escape the inline css code!
     *
     * @param string $stylesheet Escaped stylesheet
     * @param bool $inline
     */
    public function addStylesheet($stylesheet, $inline = true)
    {
        $type = $inline ? 'inline' : 'external';
        $this->stylesheets[$type][] = $stylesheet;
    }

    /**
     * @return array
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $override
     *
     * @return bool
     */
    public function addQueryParameter($key, $value, $override = true)
    {
        if (!isset($this->queryParameters[$key]) || true === $override) {
            $this->queryParameters[$key] = $value;
            return true;
        }

        return false;
    }

    /**
     * @param array $items
     * @param bool|true $override
     */
    public function addQueryParameters(array $items, $override = true)
    {
        foreach ($items as $key => $value) {
            $this->addQueryParameter($key, $value, $override);
        }
    }

    /**
     * @param $key
     */
    public function removeQueryParameter($key)
    {
        unset($this->queryParameters[$key]);
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    /**
     * @return View
     * @throws \Exception
     */
    public function getOptOutView()
    {
        if ($this->view) {
            return $this->view;
        }

        $trackVisits = !IgnoreCookie::isIgnoreCookieFound();
        $dntFound = $this->getDoNotTrackHeaderChecker()->isDoNotTrackFound();

        $setCookieInNewWindow = Common::getRequestVar('setCookieInNewWindow', false, 'int');
        if ($setCookieInNewWindow) {
            $reloadUrl = Url::getCurrentQueryStringWithParametersModified(array(
                'showConfirmOnly' => 1,
                'setCookieInNewWindow' => 0,
            ));
        } else {
            $reloadUrl = false;

            $nonce = Common::getRequestVar('nonce', false);
            if ($nonce !== false && Nonce::verifyNonce('Piwik_OptOut', $nonce)) {
                Nonce::discardNonce('Piwik_OptOut');
                IgnoreCookie::setIgnoreCookie();
                $trackVisits = !$trackVisits;
            }
        }

        $language = Common::getRequestVar('language', '');
        $lang = APILanguagesManager::getInstance()->isLanguageAvailable($language)
            ? $language
            : LanguagesManager::getLanguageCodeForCurrentUser();

        $this->addQueryParameters(array(
            'module' => 'CoreAdminHome',
            'action' => 'optOut',
            'language' => $lang,
            'setCookieInNewWindow' => 1
        ), false);

        $this->addStylesheet($this->optOutStyling());

        $this->view = new View("@CoreAdminHome/optOut");

        $this->addJavaScript('plugins/CoreAdminHome/javascripts/optOut.js', false);

        $this->view->setXFrameOptions('allow');
        $this->view->dntFound = $dntFound;
        $this->view->trackVisits = $trackVisits;
        $this->view->nonce = Nonce::getNonce('Piwik_OptOut', 3600);
        $this->view->language = $lang;
        $this->view->showConfirmOnly = Common::getRequestVar('showConfirmOnly', false, 'int');
        $this->view->reloadUrl = $reloadUrl;
        $this->view->javascripts = $this->getJavaScripts();
        $this->view->stylesheets = $this->getStylesheets();
        $this->view->title = $this->getTitle();
        $this->view->queryParameters = $this->getQueryParameters();
        $this->setOptOutText();
        return $this->view;
    }

    private function setOptOutText()
    {
        if (!PluginManager::getInstance()->isPluginActivated('PrivacyManager')) {
            $this->view->optedInText = self::getDefaultOptedInText();
            $this->view->optedOutText = self::getDefaultOptedOutText();
            return;
        }

        // TODO: these values should probably be cached
        $settings = new \Piwik\Plugins\PrivacyManager\SystemSettings();
        $this->view->optedInText = $this->getCachedSettingValue($settings->defaultOptedInText);
        $this->view->optedOutText = $this->getCachedSettingValue($settings->defaultOptedOutText);
    }

    private function optOutStyling()
    {
        $cssfontsize = Common::unsanitizeInputValue(Common::getRequestVar('fontSize', false, 'string'));
        $cssfontcolour = Common::unsanitizeInputValue(Common::getRequestVar('fontColor', false, 'string'));
        $cssfontfamily = Common::unsanitizeInputValue(Common::getRequestVar('fontFamily', false, 'string'));
        $cssbackgroundcolor = Common::unsanitizeInputValue(Common::getRequestVar('backgroundColor', false, 'string'));
        $cssbody = 'body { ';

        $hexstrings = array(
            'fontColor' => $cssfontcolour, 
            'backgroundColor' => $cssbackgroundcolor
        );
        foreach ($hexstrings as $key => $testcase) {
            if ($testcase && !(ctype_xdigit($testcase) && in_array(strlen($testcase),array(3,6), true))) {
                throw new \Exception("The URL parameter $key value of '$testcase' is not valid. Expected value is for example 'ffffff' or 'fff'.\n");
            }
        }

        if ($cssfontsize && (preg_match("/^[0-9]+[\.]?[0-9]*(px|pt|em|rem|%)$/", $cssfontsize))) {
            $cssbody .= 'font-size: ' . $cssfontsize . '; '; 
        } else if ($cssfontsize) {
            throw new \Exception("The URL parameter fontSize value of '$cssfontsize' is not valid. Expected value is for example '15pt', '1.2em' or '13px'.\n");
        }

        if ($cssfontfamily && (preg_match('/^[a-zA-Z0-9-\ ,\'"]+$/', $cssfontfamily))) {
            $cssbody .= 'font-family: ' . $cssfontfamily . '; ';
        } else if ($cssfontfamily) {
            throw new \Exception("The URL parameter fontFamily value of '$cssfontfamily' is not valid. Expected value is for example 'sans-serif' or 'Monaco, monospace'.\n");
        }

        if ($cssfontcolour) {
            $cssbody .= 'color: #' . $cssfontcolour . '; ';
        }
        if ($cssbackgroundcolor) {
            $cssbody .= 'background-color: #' . $cssbackgroundcolor . '; ';
        }

        $cssbody .= '}';
        return $cssbody;
    }
    /**
     * @return DoNotTrackHeaderChecker
     */
    protected function getDoNotTrackHeaderChecker()
    {
        return $this->doNotTrackHeaderChecker;
    }

    public static function getDefaultOptedInText()
    {
        return Piwik::translate('CoreAdminHome_YouMayOptOut2') . " " . Piwik::translate('CoreAdminHome_YouMayOptOut3');
    }

    public static function getDefaultOptedOutText()
    {
        return Piwik::translate('CoreAdminHome_OptOutComplete') . "\n\n" . Piwik::translate('CoreAdminHome_OptOutCompleteBis');
    }

    private function getCachedSettingValue(Setting $setting)
    {
        $cacheKey = 'OptOutManager.' . $setting->getName();
        $eagerCache = Cache::getEagerCache();

        if (!$eagerCache->contains($cacheKey)) {
            $value = $this->addParagraphs($setting->getValue());
            $eagerCache->save($cacheKey, $value);
        }
        return $eagerCache->fetch($cacheKey);
    }

    private function addParagraphs($value)
    {
        $value = preg_replace('/\n\n+/', '__PARAGRAPH__', $value); // using a placeholder since Common::sanitizeInputValue removes newlines
        $value = Common::sanitizeInputValue($value);
        $value = '<p>' . str_replace('__PARAGRAPH__', '</p><p>', $value) . '</p>';
        return $value;
    }
}
