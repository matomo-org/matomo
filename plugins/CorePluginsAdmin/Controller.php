<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Settings\Manager as SettingsManager;
use Piwik\Url;
use Piwik\View;
use Piwik\Version;
use Exception;

/**
 * @package CorePluginsAdmin
 */
class Controller extends Plugin\ControllerAdmin
{
    const UPDATE_NONCE = 'CorePluginsAdmin.updatePlugin';
    const INSTALL_NONCE = 'CorePluginsAdmin.installPlugin';
    const ACTIVATE_NONCE = 'CorePluginsAdmin.activatePlugin';
    const DEACTIVATE_NONCE = 'CorePluginsAdmin.deactivatePlugin';
    const UNINSTALL_NONCE = 'CorePluginsAdmin.uninstallPlugin';

    private $validSortMethods = array('popular', 'newest', 'alpha');
    private $defaultSortMethod = 'popular';

    private function createUpdateOrInstallView($template, $nonceName)
    {
        static::dieIfMarketplaceIsDisabled();

        $pluginName = $this->initPluginModification($nonceName);

        $view = $this->configureView('@CorePluginsAdmin/' . $template);

        $view->plugin = array('name' => $pluginName);

        try {
            $pluginInstaller = new PluginInstaller($pluginName);
            $pluginInstaller->installOrUpdatePluginFromMarketplace();

        } catch (\Exception $e) {

            $notification = new Notification($e->getMessage());
            $notification->context = Notification::CONTEXT_ERROR;
            Notification\Manager::notify('CorePluginsAdmin_InstallPlugin', $notification);

            $this->redirectAfterModification(true);
            return;
        }

        $marketplace = new Marketplace();
        $view->plugin = $marketplace->getPluginInfo($pluginName);

        return $view;
    }

    public function updatePlugin()
    {
        $view = $this->createUpdateOrInstallView('updatePlugin', static::UPDATE_NONCE);
        return $view->render();
    }

    public function installPlugin()
    {
        $view = $this->createUpdateOrInstallView('installPlugin', static::INSTALL_NONCE);
        $view->nonce = Nonce::getNonce(static::ACTIVATE_NONCE);

        return $view->render();
    }

    public function uploadPlugin()
    {
        Piwik::checkUserIsSuperUser();

        $nonce = Common::getRequestVar('nonce', null, 'string');

        if (!Nonce::verifyNonce(static::INSTALL_NONCE, $nonce)) {
            throw new \Exception(Piwik::translate('General_ExceptionNonceMismatch'));
        }

        Nonce::discardNonce(static::INSTALL_NONCE);

        if (empty($_FILES['pluginZip'])) {
            throw new \Exception('You did not specify a ZIP file.');
        }

        if (!empty($_FILES['pluginZip']['error'])) {
            throw new \Exception('Something went wrong during the plugin file upload. Please try again.');
        }

        $file = $_FILES['pluginZip']['tmp_name'];
        if (!file_exists($file)) {
            throw new \Exception('Something went wrong during the plugin file upload. Please try again.');
        }

        $view = $this->configureView('@CorePluginsAdmin/uploadPlugin');

        $pluginInstaller = new PluginInstaller('uploaded');
        $pluginMetadata  = $pluginInstaller->installOrUpdatePluginFromFile($file);

        $view->nonce = Nonce::getNonce(static::ACTIVATE_NONCE);
        $view->plugin = array(
            'name'        => $pluginMetadata->name,
            'version'     => $pluginMetadata->version,
            'isTheme'     => !empty($pluginMetadata->theme),
            'isActivated' => \Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginMetadata->name)
        );

        return $view->render();
    }

    public function pluginDetails()
    {
        static::dieIfMarketplaceIsDisabled();

        $pluginName = Common::getRequestVar('pluginName', null, 'string');
        $activeTab  = Common::getRequestVar('activeTab', '', 'string');
        if ('changelog' !== $activeTab) {
            $activeTab = '';
        }

        $view = $this->configureView('@CorePluginsAdmin/pluginDetails');

        try {
            $marketplace  = new Marketplace();
            $view->plugin = $marketplace->getPluginInfo($pluginName);
            $view->isSuperUser  = Piwik::isUserIsSuperUser();
            $view->installNonce = Nonce::getNonce(static::INSTALL_NONCE);
            $view->updateNonce  = Nonce::getNonce(static::UPDATE_NONCE);
            $view->activeTab    = $activeTab;
        } catch (\Exception $e) {
            $view->errorMessage = $e->getMessage();
        }

        return $view->render();
    }

    private function dieIfMarketplaceIsDisabled()
    {
        if (!CorePluginsAdmin::isMarketplaceEnabled()) {
            throw new \Exception('The Marketplace is disabled. Enable the Marketplace by changing the config entry "enable_marketplace" to 1.');
        }
    }

    private function createBrowsePluginsOrThemesView($template, $themesOnly)
    {
        static::dieIfMarketplaceIsDisabled();

        $query = Common::getRequestVar('query', '', 'string', $_POST);
        $sort = Common::getRequestVar('sort', $this->defaultSortMethod, 'string');

        if (!in_array($sort, $this->validSortMethods)) {
            $sort = $this->defaultSortMethod;
        }

        $view = $this->configureView('@CorePluginsAdmin/' . $template);

        $marketplace = new Marketplace();
        $view->plugins = $marketplace->searchPlugins($query, $sort, $themesOnly);

        $view->query = $query;
        $view->sort = $sort;
        $view->installNonce = Nonce::getNonce(static::INSTALL_NONCE);
        $view->updateNonce = Nonce::getNonce(static::UPDATE_NONCE);
        $view->isSuperUser = Piwik::isUserIsSuperUser();

        return $view;
    }

    public function browsePlugins()
    {
        $view = $this->createBrowsePluginsOrThemesView('browsePlugins', $themesOnly = false);
        return $view->render();
    }

    public function browseThemes()
    {
        $view = $this->createBrowsePluginsOrThemesView('browseThemes', $themesOnly = true);
        return $view->render();
    }

    function extend()
    {
        static::dieIfMarketplaceIsDisabled();

        $view = $this->configureView('@CorePluginsAdmin/extend');
        $view->installNonce = Nonce::getNonce(static::INSTALL_NONCE);
        $view->isSuperUser = Piwik::isUserIsSuperUser();

        return $view->render();
    }

    private function createPluginsOrThemesView($template, $themesOnly)
    {
        Piwik::checkUserIsSuperUser();

        $view = $this->configureView('@CorePluginsAdmin/' . $template);

        $view->updateNonce = Nonce::getNonce(static::UPDATE_NONCE);
        $view->activateNonce = Nonce::getNonce(static::ACTIVATE_NONCE);
        $view->uninstallNonce = Nonce::getNonce(static::UNINSTALL_NONCE);
        $view->deactivateNonce = Nonce::getNonce(static::DEACTIVATE_NONCE);
        $view->pluginsInfo = $this->getPluginsInfo($themesOnly);

        $users = \Piwik\Plugins\UsersManager\API::getInstance()->getUsers();
        $view->otherUsersCount = count($users) - 1;
        $view->themeEnabled = \Piwik\Plugin\Manager::getInstance()->getThemeEnabled()->getPluginName();

        $view->pluginNamesHavingSettings = $this->getPluginNamesHavingSettingsForCurrentUser();
        $view->isMarketplaceEnabled = CorePluginsAdmin::isMarketplaceEnabled();

        if (CorePluginsAdmin::isMarketplaceEnabled()) {
            $marketplace = new Marketplace();
            $view->marketplacePluginNames = $marketplace->getAvailablePluginNames($themesOnly);
            $view->pluginsHavingUpdate    = $marketplace->getPluginsHavingUpdate($themesOnly);
        } else {
            $view->pluginsHavingUpdate    = array();
            $view->marketplacePluginNames = array();
        }

        return $view;
    }

    function plugins()
    {
        $view = $this->createPluginsOrThemesView('plugins', $themesOnly = false);
        return $view->render();
    }

    function themes()
    {
        $view = $this->createPluginsOrThemesView('themes', $themesOnly = true);
        return $view->render();
    }

    protected function configureView($template)
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View($template);
        $this->setBasicVariablesView($view);
        $this->displayWarningIfConfigFileNotWritable();

        $view->errorMessage = '';

        return $view;
    }

    protected function getPluginsInfo($themesOnly = false)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $pluginManager->returnLoadedPluginsInfo();

        foreach ($plugins as $pluginName => &$plugin) {

            $plugin['isCorePlugin'] = $pluginManager->isPluginBundledWithCore($pluginName);

            if (!isset($plugin['info'])) {

                $suffix = Piwik::translate('CorePluginsAdmin_PluginAskDevToUpdate');
                // If the plugin has been renamed, we do not show message to ask user to update plugin
                if($pluginName != Request::renameModule($pluginName)) {
                    $suffix = "You may uninstall the plugin or manually delete the files in piwik/plugins/$pluginName/";
                }

                $description = '<strong><em>'
                    . Piwik::translate('CorePluginsAdmin_PluginNotCompatibleWith', array($pluginName, self::getPiwikVersion()))
                    . '</strong><br/>'
                    . $suffix
                    . '</em>';
                $plugin['info'] = array(
                    'description' => $description,
                    'version'     => Piwik::translate('General_Unknown'),
                    'theme'       => false,
                );
            }
        }

        $pluginsFiltered = $this->keepPluginsOrThemes($themesOnly, $plugins);
        return $pluginsFiltered;
    }

    protected function keepPluginsOrThemes($themesOnly, $plugins)
    {
        $pluginsFiltered = array();
        foreach ($plugins as $name => $thisPlugin) {

            $isTheme = false;
            if (!empty($thisPlugin['info']['theme'])) {
                $isTheme = (bool)$thisPlugin['info']['theme'];
            }
            if (($themesOnly && $isTheme)
                || (!$themesOnly && !$isTheme)
            ) {
                $pluginsFiltered[$name] = $thisPlugin;
            }
        }
        return $pluginsFiltered;
    }

    public function safemode($lastError = array())
    {
        if (empty($lastError)) {
            $lastError = array(
                'message' => Common::getRequestVar('error_message', null, 'string'),
                'file'    => Common::getRequestVar('error_file', null, 'string'),
                'line'    => Common::getRequestVar('error_line', null, 'integer')
            );
        }

        $outputFormat = Common::getRequestVar('format', 'html', 'string');
        $outputFormat = strtolower($outputFormat);

        if (!empty($outputFormat) && 'html' !== $outputFormat) {

            $errorMessage = $lastError['message'];

            if (Piwik::isUserIsAnonymous()) {
                $errorMessage = 'A fatal error occurred.';
            }

            $response = new \Piwik\API\ResponseBuilder($outputFormat);
            $message  = $response->getResponseException(new Exception($errorMessage));

            return $message;
        }

        $view = new View('@CorePluginsAdmin/safemode');
        $view->lastError   = $lastError;
        $view->isSuperUser = Piwik::isUserIsSuperUser();
        $view->isAnonymousUser = Piwik::isUserIsAnonymous();
        $view->plugins         = Plugin\Manager::getInstance()->returnLoadedPluginsInfo();
        $view->deactivateNonce = Nonce::getNonce(static::DEACTIVATE_NONCE);
        $view->uninstallNonce  = Nonce::getNonce(static::UNINSTALL_NONCE);
        $view->emailSuperUser  = Piwik::getSuperUserEmail();
        $view->piwikVersion    = Version::VERSION;
        $view->pluginCausesIssue = '';

        if (!empty($lastError['file'])) {
            preg_match('/piwik\/plugins\/(.*)\//', $lastError['file'], $matches);

            if (!empty($matches[1])) {
                $view->pluginCausesIssue = $matches[1];
            }
        }

        return $view->render();
    }

    public function deactivate($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification(static::DEACTIVATE_NONCE);
        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($pluginName);
        $this->redirectAfterModification($redirectAfter);
    }

    protected function redirectAfterModification($redirectAfter)
    {
        if ($redirectAfter) {
            Url::redirectToReferrer();
        }
    }

    protected function initPluginModification($nonceName)
    {
        Piwik::checkUserIsSuperUser();

        $nonce = Common::getRequestVar('nonce', null, 'string');

        if (!Nonce::verifyNonce($nonceName, $nonce)) {
            throw new \Exception(Piwik::translate('General_ExceptionNonceMismatch'));
        }

        Nonce::discardNonce($nonceName);

        $pluginName = Common::getRequestVar('pluginName', null, 'string');
        return $pluginName;
    }

    public function activate($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification(static::ACTIVATE_NONCE);

        \Piwik\Plugin\Manager::getInstance()->activatePlugin($pluginName);

        if ($redirectAfter) {
            $plugin = \Piwik\Plugin\Manager::getInstance()->loadPlugin($pluginName);

            $actionToRedirect = 'plugins';
            if ($plugin->isTheme()) {
                $actionToRedirect = 'themes';
            }

            $message = Piwik::translate('CorePluginsAdmin_PluginSuccessfullyActicated', array($pluginName));
            if (SettingsManager::hasPluginSettingsForCurrentUser($pluginName)) {
                $target   = sprintf('<a href="index.php%s#%s">',
                                    Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreAdminHome', 'action' => 'pluginSettings')),
                                    $pluginName);
                $message .= ' ' . Piwik::translate('CorePluginsAdmin_ChangeSettingsPossible', array($target, '</a>'));
            }

            $notification = new Notification($message);
            $notification->raw     = true;
            $notification->title   = Piwik::translate('General_WellDone');
            $notification->context = Notification::CONTEXT_SUCCESS;
            Notification\Manager::notify('CorePluginsAdmin_PluginActivated', $notification);

            $this->redirectToIndex('CorePluginsAdmin', $actionToRedirect);
        }
    }

    public function uninstall($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification(static::UNINSTALL_NONCE);

        $uninstalled = \Piwik\Plugin\Manager::getInstance()->uninstallPlugin($pluginName);

        if (!$uninstalled) {
            $path = Filesystem::getPathToPiwikRoot() . '/plugins/' . $pluginName . '/';
            $messagePermissions = Filechecks::getErrorMessageMissingPermissions($path);

            $messageIntro = Piwik::translate("Warning: \"%s\" could not be uninstalled. Piwik did not have enough permission to delete the files in $path. ",
                $pluginName);
            $exitMessage = $messageIntro . "<br/><br/>" . $messagePermissions;
            $exitMessage .= "<br> Or manually delete this directory (using FTP or SSH access)";
            Piwik_ExitWithMessage($exitMessage, $optionalTrace = false, $optionalLinks = false, $optionalLinkBack = true);
        }

        $this->redirectAfterModification($redirectAfter);
    }

    private function getPluginNamesHavingSettingsForCurrentUser()
    {
        return array_keys(SettingsManager::getPluginSettingsForCurrentUser());
    }

}
