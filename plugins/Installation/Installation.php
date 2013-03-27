<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Installation
 */

/**
 *
 * @package Piwik_Installation
 */
class Piwik_Installation extends Piwik_Plugin
{
    protected $installationControllerName = 'Piwik_Installation_Controller';

    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('Installation_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );

        return $info;
    }

    function getListHooksRegistered()
    {
        $hooks = array(
            'FrontController.NoConfigurationFile'  => 'dispatch',
            'FrontController.badConfigurationFile' => 'dispatch',
            'AdminMenu.add'                        => 'addMenu',
            'AssetManager.getCssFiles'             => 'getCss',
        );
        return $hooks;
    }

    public function setControllerToLoad($newControllerName)
    {
        $this->installationControllerName = $newControllerName;
    }

    protected function getInstallationController()
    {
        return new $this->installationControllerName();
    }

    /**
     * @param Piwik_Event_Notification|null $notification  notification object
     */
    function dispatch($notification = null)
    {
        if ($notification) {
            $exception = $notification->getNotificationObject();
            $message = $exception->getMessage();
        } else {
            $message = '';
        }

        Piwik_Translate::getInstance()->loadCoreTranslation();

        Piwik_PostEvent('Installation.startInstallation', $this);

        $step = Piwik_Common::getRequestVar('action', 'welcome', 'string');
        $controller = $this->getInstallationController();
        if (in_array($step, array_keys($controller->getInstallationSteps())) || $step == 'saveLanguage') {
            $controller->$step($message);
        } else {
            Piwik::exitWithErrorMessage(Piwik_Translate('Installation_NoConfigFound'));
        }

        exit;
    }

    /**
     * Adds the 'System Check' admin page if the user is the super user.
     */
    public function addMenu()
    {
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuDiagnostic', 'Installation_SystemCheck',
            array('module' => 'Installation', 'action' => 'systemCheckPage'),
            $addIf = Piwik::isUserIsSuperUser(),
            $order = 15);
    }

    /**
     * Adds CSS files to list of CSS files for asset manager.
     */
    public function getCss($notification)
    {
        $cssFiles = & $notification->getNotificationObject();

        $cssFiles[] = "plugins/Installation/templates/systemCheckPage.css";
    }
}
