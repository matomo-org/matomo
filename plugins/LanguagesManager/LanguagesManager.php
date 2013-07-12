<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_LanguagesManager
 *
 */

/**
 *
 * @package Piwik_LanguagesManager
 */
class Piwik_LanguagesManager extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('LanguagesManager_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles'    => 'getCssFiles',
            'AssetManager.getJsFiles'     => 'getJsFiles',
            'TopMenu.add'                 => 'showLanguagesSelector',
            'Translate.getLanguageToLoad' => 'getLanguageToLoad',
            'UsersManager.deleteUser'     => 'deleteUserLanguage',
            'template_topBar'             => 'addLanguagesManagerToOtherTopBar',
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/Zeitgeist/stylesheets/styles.css";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/LanguagesManager/javascripts/languageSelector.js";
    }

    public function showLanguagesSelector()
    {
        Piwik_AddTopMenu('LanguageSelector', $this->getLanguagesSelector(), true, $order = 30, true);
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
        $str .= "<script type='text/javascript' src='plugins/LanguagesManager/javascripts/languageSelector.js'></script>";
        $str .= $this->getLanguagesSelector();
    }

    /**
     * Renders and returns the language selector HTML.
     *
     * @return string
     */
    private function getLanguagesSelector()
    {
        $view = new Piwik_View("@LanguagesManager/getLanguagesSelector");
        $view->languages = Piwik_LanguagesManager_API::getInstance()->getAvailableLanguageNames();
        $view->currentLanguageCode = self::getLanguageCodeForCurrentUser();
        $view->currentLanguageName = self::getLanguageNameForCurrentUser();
        return $view->render();
    }

    function getLanguageToLoad(&$language)
    {
        if (empty($language)) {
            $language = self::getLanguageCodeForCurrentUser();
        }
        if (!Piwik_LanguagesManager_API::getInstance()->isLanguageAvailable($language)) {
            $language = Piwik_Translate::getInstance()->getLanguageDefault();
        }
    }

    public function deleteUserLanguage($userLogin)
    {
        Piwik_Query('DELETE FROM ' . Piwik_Common::prefixTable('user_language') . ' WHERE login = ?', $userLogin);
    }

    /**
     * @throws Exception if non-recoverable error
     */
    public function install()
    {
        // we catch the exception
        try {
            $sql = "CREATE TABLE " . Piwik_Common::prefixTable('user_language') . " (
					login VARCHAR( 100 ) NOT NULL ,
					language VARCHAR( 10 ) NOT NULL ,
					PRIMARY KEY ( login )
					)  DEFAULT CHARSET=utf8 ";
            Piwik_Exec($sql);
        } catch (Exception $e) {
            // mysql code error 1050:table already exists
            // see bug #153 http://dev.piwik.org/trac/ticket/153
            if (!Zend_Registry::get('db')->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * @throws Exception if non-recoverable error
     */
    public function uninstall()
    {
        Piwik_DropTables(Piwik_Common::prefixTable('user_language'));
    }

    /**
     * @return string Two letters language code, eg. "fr"
     */
    static public function getLanguageCodeForCurrentUser()
    {
        $languageCode = self::getLanguageFromPreferences();
        if (!Piwik_LanguagesManager_API::getInstance()->isLanguageAvailable($languageCode)) {
            $languageCode = Piwik_Common::extractLanguageCodeFromBrowserLanguage(Piwik_Common::getBrowserLanguage(), Piwik_LanguagesManager_API::getInstance()->getAvailableLanguages());
        }
        if (!Piwik_LanguagesManager_API::getInstance()->isLanguageAvailable($languageCode)) {
            $languageCode = Piwik_Translate::getInstance()->getLanguageDefault();
        }
        return $languageCode;
    }

    /**
     * @return string Full english language string, eg. "French"
     */
    static public function getLanguageNameForCurrentUser()
    {
        $languageCode = self::getLanguageCodeForCurrentUser();
        $languages = Piwik_LanguagesManager_API::getInstance()->getAvailableLanguageNames();
        foreach ($languages as $language) {
            if ($language['code'] === $languageCode) {
                return $language['name'];
            }
        }
    }

    /**
     * @return string|false if language preference could not be loaded
     */
    static protected function getLanguageFromPreferences()
    {
        if (($language = self::getLanguageForSession()) != null) {
            return $language;
        }

        try {
            $currentUser = Piwik::getCurrentUserLogin();
            return Piwik_LanguagesManager_API::getInstance()->getLanguageForUser($currentUser);
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Returns the langage for the session
     *
     * @return string|null
     */
    static public function getLanguageForSession()
    {
        $cookieName = Piwik_Config::getInstance()->General['language_cookie_name'];
        $cookie = new Piwik_Cookie($cookieName);
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
    static public function setLanguageForSession($languageCode)
    {
        if (!Piwik_LanguagesManager_API::getInstance()->isLanguageAvailable($languageCode)) {
            return false;
        }

        $cookieName = Piwik_Config::getInstance()->General['language_cookie_name'];
        $cookie = new Piwik_Cookie($cookieName, 0);
        $cookie->set('language', $languageCode);
        $cookie->save();
    }
}
