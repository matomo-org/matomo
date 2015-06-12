<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Cookie;
use Piwik\Db;
use Piwik\Intl\Locale;
use Piwik\Piwik;
use Piwik\Translation\Translator;
use Piwik\View;

/**
 *
 */
class LanguagesManager extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles'            => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'            => 'getJsFiles',
            'Config.NoConfigurationFile'                 => 'initLanguage',
            'Request.dispatch'                           => 'initLanguage',
            'Request.dispatchCoreAndPluginUpdatesScreen' => 'initLanguage',
            'Platform.initialized'                       => 'initLanguage',
            'UsersManager.deleteUser'                    => 'deleteUserLanguage',
            'Template.topBar'                            => 'addLanguagesManagerToOtherTopBar',
            'Template.jsGlobalVariables'                 => 'jsGlobalVariables'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Morpheus/stylesheets/base.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/LanguagesManager/angularjs/languageselector/languageselector.directive.js";
        $jsFiles[] = "plugins/LanguagesManager/angularjs/translationsearch/translationsearch.controller.js";
        $jsFiles[] = "plugins/LanguagesManager/angularjs/translationsearch/translationsearch.directive.js";
    }

    /**
     * Adds the languages drop-down list to topbars other than the main one rendered
     * in CoreHome/templates/top_bar.twig. The 'other' topbars are on the Installation
     * and CoreUpdater screens.
     */
    public function addLanguagesManagerToOtherTopBar(&$str)
    {
        // piwik object & scripts aren't loaded in 'other' topbars
        $str .= "<script type='text/javascript'>if (!window.piwik) window.piwik={};</script>";
        $str .= "<script type='text/javascript' src='plugins/CoreHome/angularjs/menudropdown/menudropdown.directive.js'></script>";
        $str .= "<script type='text/javascript' src='plugins/LanguagesManager/angularjs/languageselector/languageselector.directive.js'></script>";
        $str .= $this->getLanguagesSelector();
    }

    /**
     * Adds the languages drop-down list to topbars other than the main one rendered
     * in CoreHome/templates/top_bar.twig. The 'other' topbars are on the Installation
     * and CoreUpdater screens.
     */
    public function jsGlobalVariables(&$str)
    {
        // piwik object & scripts aren't loaded in 'other' topbars
        $str .= "piwik.languageName = '" .  self::getLanguageNameForCurrentUser() . "';";
    }

    /**
     * Renders and returns the language selector HTML.
     *
     * @return string
     */
    public function getLanguagesSelector()
    {
        $view = new View("@LanguagesManager/getLanguagesSelector");
        $view->languages = API::getInstance()->getAvailableLanguageNames();
        $view->currentLanguageCode = self::getLanguageCodeForCurrentUser();
        $view->currentLanguageName = self::getLanguageNameForCurrentUser();
        return $view->render();
    }

    public function initLanguage()
    {
        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');
        // Update current language.
        $translator->setCurrentLanguage(self::getCurrentLanguage());
        // Update current locale.
        $locale = $translator->translate('General_Locale');
        Locale::setLocale($locale);
    }

    public function deleteUserLanguage($userLogin)
    {
        $model = new Model();
        $model->deleteUserLanguage($userLogin);
    }

    /**
     * @throws Exception if non-recoverable error
     */
    public function install()
    {
        Model::install();
    }

    /**
     * @throws Exception if non-recoverable error
     */
    public function uninstall()
    {
        Model::uninstall();
    }

    /**
     * First check forced by request language setting,
     * then - session language setting, then - from browser,
     * and finally default configured setting.
     *
     * @return string Two letters language code, eg. "fr"
     */
    public static function getCurrentLanguage()
    {
        $languageCodes = array(
            Common::getRequestVar('language', ''),
            self::getLanguageForSession(),
            self::getLanguageCodeForCurrentUser(),
            StaticContainer::get('Piwik\Translation\Translator')->getDefaultLanguage(),
        );
        foreach ($languageCodes as $languageCode) {
            if (!empty($languageCode) && API::getInstance()->isLanguageAvailable($languageCode)) {
                return $languageCode;
            }
        }
        return null;
    }

    /**
     * @return string Two letters language code, eg. "fr"
     */
    public static function getLanguageCodeForCurrentUser()
    {
        $languageCodes = array(
            self::getLanguageFromPreferences(),
            Common::extractLanguageCodeFromBrowserLanguage(
                Common::getBrowserLanguage(),
                API::getInstance()->getAvailableLanguages()
            ),
        );
        foreach ($languageCodes as $languageCode) {
            if (!empty($languageCode) && API::getInstance()->isLanguageAvailable($languageCode)) {
                return $languageCode;
            }
        }
        return null;
    }

    /**
     * @return string Full english language string, eg. "French"
     */
    public static function getLanguageNameForCurrentUser()
    {
        $languageCode = self::getLanguageCodeForCurrentUser();
        $languages = API::getInstance()->getAvailableLanguageNames();
        foreach ($languages as $language) {
            if ($language['code'] === $languageCode) {
                return $language['name'];
            }
        }
        return false;
    }

    /**
     * @return string|false if language preference could not be loaded
     */
    protected static function getLanguageFromPreferences()
    {
        if (($language = self::getLanguageForSession()) != null) {
            return $language;
        }

        try {
            $currentUser = Piwik::getCurrentUserLogin();
            return API::getInstance()->getLanguageForUser($currentUser);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns the language for the session
     *
     * @return string|null
     */
    public static function getLanguageForSession()
    {
        $cookieName = Config::getInstance()->General['language_cookie_name'];
        $cookie = new Cookie($cookieName);
        if ($cookie->isCookieFound()) {
            return $cookie->get('language');
        }
        return null;
    }

    /**
     * Set the language for the session
     *
     * @param string $languageCode ISO language code
     * @return bool
     */
    public static function setLanguageForSession($languageCode)
    {
        if (!API::getInstance()->isLanguageAvailable($languageCode)) {
            return false;
        }

        $cookieName = Config::getInstance()->General['language_cookie_name'];
        $cookie = new Cookie($cookieName, 0);
        $cookie->set('language', $languageCode);
        $cookie->save();
        return true;
    }
}
