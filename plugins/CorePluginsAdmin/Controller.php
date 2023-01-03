<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Exception;
use Piwik\Access;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Exception\MissingFilePermissionException;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Plugins\Marketplace\Controller as MarketplaceController;
use Piwik\Plugins\Marketplace\Plugins;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;

class Controller extends Plugin\ControllerAdmin
{
    const ACTIVATE_NONCE = 'CorePluginsAdmin.activatePlugin';
    const DEACTIVATE_NONCE = 'CorePluginsAdmin.deactivatePlugin';
    const UNINSTALL_NONCE = 'CorePluginsAdmin.uninstallPlugin';

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Plugin\SettingsProvider
     */
    private $settingsProvider;

    /**
     * @var PluginInstaller
     */
    private $pluginInstaller;
    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var Plugins
     */
    private $marketplacePlugins;

    /**
     * @var PasswordVerifier
     */
    private $passwordVerify;

    /**
     * Controller constructor.
     * @param Translator $translator
     * @param Plugin\SettingsProvider $settingsProvider
     * @param PluginInstaller $pluginInstaller
     * @param Plugins $marketplacePlugins
     * @param PasswordVerifier $passwordVerify
     */
    public function __construct(Translator $translator,
                                Plugin\SettingsProvider $settingsProvider,
                                PluginInstaller $pluginInstaller,
                                PasswordVerifier $passwordVerify,
                                $marketplacePlugins = null
    ) {
        $this->translator = $translator;
        $this->settingsProvider = $settingsProvider;
        $this->pluginInstaller = $pluginInstaller;
        $this->pluginManager = Plugin\Manager::getInstance();
        $this->passwordVerify = $passwordVerify;

        if (!empty($marketplacePlugins)) {
            $this->marketplacePlugins = $marketplacePlugins;
        } elseif (Marketplace::isMarketplaceEnabled()) {
            // we load it manually as marketplace might not be loaded
            $this->marketplacePlugins = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins');
        }

        parent::__construct();
    }

    public function uploadPlugin()
    {
        static::dieIfPluginsAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();

        if (!CorePluginsAdmin::isPluginUploadEnabled()) {
            throw new \Exception('Plugin upload disabled by config');
        }

        $nonce = Common::getRequestVar('nonce', null, 'string');

        if (!Nonce::verifyNonce(MarketplaceController::INSTALL_NONCE, $nonce)) {
            throw new \Exception($this->translator->translate('General_ExceptionSecurityCheckFailed'));
        }

        Nonce::discardNonce(MarketplaceController::INSTALL_NONCE);

        if (!$this->passwordVerify->isPasswordCorrect(
            Piwik::getCurrentUserLogin(),
            Common::getRequestVar('confirmPassword', null, 'string')
        )) {
            throw new \Exception($this->translator->translate('Login_LoginPasswordNotCorrect'));
        }

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

        $pluginMetadata = $this->pluginInstaller->installOrUpdatePluginFromFile($file);

        $view->nonce = Nonce::getNonce(static::ACTIVATE_NONCE);
        $view->plugin = array(
            'name'        => $pluginMetadata->name,
            'version'     => $pluginMetadata->version,
            'isTheme'     => !empty($pluginMetadata->theme),
            'isActivated' => $this->pluginManager->isPluginActivated($pluginMetadata->name)
        );

        return $view->render();
    }

    public function tagManagerTeaser()
    {
        $this->dieIfPluginsAdminIsDisabled();
        Piwik::checkUserHasSomeAdminAccess();

        $tagManagerTeaser = new TagManagerTeaser(Piwik::getCurrentUserLogin());

        if (!$tagManagerTeaser->shouldShowTeaser()) {
            $this->redirectToIndex('CoreHome', 'index');
            return;
        }

        $nonce = '';
        if (Piwik::hasUserSuperUserAccess()) {
            $nonce = Nonce::getNonce(static::ACTIVATE_NONCE);
        }

        $view = new View('@CorePluginsAdmin/tagManagerTeaser');
        $this->setGeneralVariablesView($view);
        $view->contactEmail = implode(',', Piwik::getContactEmailAddresses());
        $view->nonce = $nonce;
        return $view->render();
    }

    public function disableActivateTagManagerPage()
    {
        $this->dieIfPluginsAdminIsDisabled();
        Piwik::checkUserHasSomeAdminAccess();

        $tagManagerTeaser = new TagManagerTeaser(Piwik::getCurrentUserLogin());

        if (Piwik::hasUserSuperUserAccess()) {
            $tagManagerTeaser->disableGlobally();
        } else {
            $tagManagerTeaser->disableForUser();
        }

        $date = Common::getRequestVar('date', false);
        $this->redirectToIndex('CoreHome', 'index', $websiteId = null, $defaultPeriod = null, $date);
    }

    private function dieIfPluginsAdminIsDisabled()
    {
        Piwik::checkUserIsNotAnonymous();
        if (!CorePluginsAdmin::isPluginsAdminEnabled()) {
            throw new \Exception('Enabling, disabling and uninstalling plugins has been disabled by Piwik admins.
            Please contact your Piwik admins with your request so they can assist you.');
        }
    }

    private function createPluginsOrThemesView($template, $themesOnly)
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = $this->configureView('@CorePluginsAdmin/' . $template);

        $this->securityPolicy->addPolicy('img-src', '*.matomo.org');
        $this->securityPolicy->addPolicy('default-src', '*.matomo.org');

        $view->updateNonce = Nonce::getNonce(MarketplaceController::UPDATE_NONCE);
        $view->activateNonce = Nonce::getNonce(static::ACTIVATE_NONCE);
        $view->uninstallNonce = Nonce::getNonce(static::UNINSTALL_NONCE);
        $view->deactivateNonce = Nonce::getNonce(static::DEACTIVATE_NONCE);
        $view->pluginsInfo = $this->getPluginsInfo($themesOnly);

        $users = Request::processRequest('UsersManager.getUsers', array('filter_limit' => '-1'));
        $view->otherUsersCount = count($users) - 1;
        $view->themeEnabled = $this->pluginManager->getThemeEnabled()->getPluginName();

        $view->pluginNamesHavingSettings = array_keys($this->settingsProvider->getAllSystemSettings());
        $view->isMarketplaceEnabled = Marketplace::isMarketplaceEnabled();
        $view->isPluginsAdminEnabled = CorePluginsAdmin::isPluginsAdminEnabled();

        $view->pluginsHavingUpdate    = array();
        $view->marketplacePluginNames = array();

        if (Marketplace::isMarketplaceEnabled() && $this->marketplacePlugins) {
            try {
                $view->marketplacePluginNames = $this->marketplacePlugins->getAvailablePluginNames($themesOnly);
                $view->pluginsHavingUpdate    = $this->marketplacePlugins->getPluginsHavingUpdate();
            } catch(Exception $e) {
                // curl exec connection error (ie. server not connected to internet)
            }
        }

        $view->isPluginUploadEnabled = CorePluginsAdmin::isPluginUploadEnabled();
        $view->uploadLimit = SettingsServer::getPostMaxUploadSize();
        $view->installNonce = Nonce::getNonce(MarketplaceController::INSTALL_NONCE);

        return $view;
    }

    public function plugins()
    {
        $view = $this->createPluginsOrThemesView('plugins', $themesOnly = false);
        return $view->render();
    }

    public function themes()
    {
        $view = $this->createPluginsOrThemesView('themes', $themesOnly = true);
        return $view->render();
    }

    protected function configureView($template)
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View($template);
        $this->setBasicVariablesView($view);

        // If user can manage plugins+themes, display a warning if config not writable
        if (CorePluginsAdmin::isPluginsAdminEnabled()) {
            $this->displayWarningIfConfigFileNotWritable();
        }

        $view->errorMessage = '';

        return $view;
    }

    protected function getPluginsInfo($themesOnly = false)
    {
        $plugins = $this->pluginManager->loadAllPluginsAndGetTheirInfo();

        foreach ($plugins as $pluginName => &$plugin) {

            $plugin['isCorePlugin'] = $this->pluginManager->isPluginBundledWithCore($pluginName);
            $plugin['isOfficialPlugin'] = false;

            if (isset($plugin['info']) && isset($plugin['info']['authors'])) {
                foreach ($plugin['info']['authors'] as $author) {
                    if (in_array(strtolower($author['name']), array('piwik', 'innocraft', 'matomo', 'matomo-org'))) {
                        $plugin['isOfficialPlugin'] = true;
                        break;
                    }
                }
            }

            if (!empty($plugin['info']['description'])) {
                $plugin['info']['description'] = $this->translator->translate($plugin['info']['description']);
            }

            if (!isset($plugin['info'])) {

                $suffix = $this->translator->translate('CorePluginsAdmin_PluginNotWorkingAlternative');
                // If the plugin has been renamed, we do not show message to ask user to update plugin
                list($pluginNameRenamed, $methodName) = Request::getRenamedModuleAndAction($pluginName, 'index');
                if ($pluginName != $pluginNameRenamed) {
                    $suffix = "You may uninstall the plugin or manually delete the files in /path/to/matomo/plugins/$pluginName/";
                }

                if ($this->pluginManager->isPluginInFilesystem($pluginName)) {
                    $description = '<strong>'
                        . $this->translator->translate('CorePluginsAdmin_PluginNotCompatibleWith',
                            array($pluginName, self::getPiwikVersion()))
                        . '</strong><br/>'
                        . $suffix;
                } else {
                    $description = $this->translator->translate('CorePluginsAdmin_PluginNotFound',
                            array($pluginName))
                        . "\n"
                        . $this->translator->translate('CorePluginsAdmin_PluginNotFoundAlternative');
                }
                $plugin['info'] = array(
                    'description' => $description,
                    'version'     => $this->translator->translate('General_Unknown'),
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
        if (ob_get_length()) {
            ob_clean();
        }

        $this->tryToRepairPiwik();

        if (empty($lastError) && defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
            $lastError = array(
                'message' => Common::getRequestVar('error_message', null, 'string'),
                'file'    => Common::getRequestVar('error_file', null, 'string'),
                'line'    => Common::getRequestVar('error_line', null, 'integer')
            );
        } elseif (empty($lastError)) {
            throw new Exception('Safemode not available');
        }

        $outputFormat = Common::getRequestVar('format', 'html', 'string');
        $outputFormat = strtolower($outputFormat);

        if (!empty($outputFormat) && 'html' !== $outputFormat) {

            $errorMessage = $lastError['message'];

            if (!empty($lastError['backtrace'])
                && \Piwik_ShouldPrintBackTraceWithMessage()
            ) {
                $errorMessage .= $lastError['backtrace'];
            }

            if (Piwik::isUserIsAnonymous()) {
                $errorMessage = 'A fatal error occurred.';
            }

            $response = new \Piwik\API\ResponseBuilder($outputFormat, [], false); // don't print the exception backtrace since it will be useless
            $message  = $response->getResponseException(new Exception($errorMessage));

            return $message;
        }

        if (Common::isPhpCliMode()) {
            throw new Exception("Error: " . var_export($lastError, true));
        }

        if (!\Piwik_ShouldPrintBackTraceWithMessage()) {
            unset($lastError['backtrace']);
        }

        $view = new View('@CorePluginsAdmin/safemode');
        $view->lastError   = $lastError;
        $view->isAllowedToTroubleshootAsSuperUser = $this->isAllowedToTroubleshootAsSuperUser();
        $view->isSuperUser = Piwik::hasUserSuperUserAccess();
        $view->isAnonymousUser = Piwik::isUserIsAnonymous();
        $view->plugins         = $this->pluginManager->loadAllPluginsAndGetTheirInfo();
        $view->deactivateNonce = Nonce::getNonce(static::DEACTIVATE_NONCE);
        $view->deactivateIAmSuperUserSalt = Common::getRequestVar('i_am_super_user', '', 'string');
        $view->uninstallNonce  = Nonce::getNonce(static::UNINSTALL_NONCE);
        $view->contactEmail  = implode(',', Piwik::getContactEmailAddresses());
        $view->piwikVersion    = Version::VERSION;
        $view->showVersion     = !Common::getRequestVar('tests_hide_piwik_version', 0);
        $view->pluginCausesIssue = '';

        // When the CSS merger in StylesheetUIAssetMerger throws an exception, safe mode is displayed.
        // This flag prevents an infinite loop where safemode would try to re-generate the cache buster which requires CSS merger..
        $view->disableCacheBuster();

        if (!empty($lastError['file'])) {
            preg_match('/piwik\/plugins\/(.*)\//', $lastError['file'], $matches);

            if (!empty($matches[1])) {
                $view->pluginCausesIssue = $matches[1];
            }
        }

        return $view->render();
    }

    public function activate($redirectAfter = true)
    {
        $this->dieIfPluginsAdminIsDisabled();

        $params = [
            'module' => 'CorePluginsAdmin',
            'action' => 'activate',
            'pluginName' => Common::getRequestVar('pluginName'),
            'nonce' => Common::getRequestVar('nonce'),
            'redirectTo' => Common::getRequestVar('redirectTo', '', 'string'),
            'referrer' => urlencode(Url::getReferrer()),
        ];

        if (!$this->passwordVerify->requirePasswordVerifiedRecently($params)) {
            return;
        }

        $pluginName = $this->initPluginModification(static::ACTIVATE_NONCE);

        $this->pluginManager->activatePlugin($pluginName);

        if ($redirectAfter) {
            $message = $this->translator->translate('CorePluginsAdmin_SuccessfullyActicated', array($pluginName));

            if ($this->settingsProvider->getSystemSettings($pluginName)) {
                $target   = sprintf('<a href="index.php%s#%s">',
                    Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreAdminHome', 'action' => 'generalSettings')),
                    $pluginName);
                $message .= ' ' . $this->translator->translate('CorePluginsAdmin_ChangeSettingsPossible', array($target, '</a>'));
            }

            $notification = new Notification($message);
            $notification->raw     = true;
            $notification->title   = $this->translator->translate('General_WellDone');
            $notification->context = Notification::CONTEXT_SUCCESS;
            Notification\Manager::notify('CorePluginsAdmin_PluginActivated', $notification);

            $redirectTo = Common::getRequestVar('redirectTo', '', 'string');
            if (!empty($redirectTo) && $redirectTo === 'marketplace') {
                $this->redirectToIndex('Marketplace', 'overview');
            } elseif (!empty($redirectTo) && $redirectTo === 'tagmanager') {
                $this->redirectToIndex('TagManager', 'gettingStarted');
            } elseif (!empty($redirectTo) && $redirectTo === 'referrer') {
                $this->redirectAfterModification($redirectAfter);
            } else {
                $plugin = $this->pluginManager->loadPlugin($pluginName);

                $actionToRedirect = 'plugins';
                if ($plugin->isTheme()) {
                    $actionToRedirect = 'themes';
                }

                $this->redirectToIndex('CorePluginsAdmin', $actionToRedirect);
            }

        }
    }

    public function deactivate($redirectAfter = true)
    {
        $params = [
            'module' => 'CorePluginsAdmin',
            'action' => 'deactivate',
            'pluginName' => Common::getRequestVar('pluginName'),
            'nonce' => Common::getRequestVar('nonce'),
            'redirectTo' => Common::getRequestVar('redirectTo'),
            'referrer' => urlencode(Url::getReferrer()),
        ];
        if (!$this->passwordVerify->requirePasswordVerifiedRecently($params)) {
            return;
        }

        if($this->isAllowedToTroubleshootAsSuperUser()) {
            Access::doAsSuperUser(function() use ($redirectAfter) {
                $this->doDeactivatePlugin($redirectAfter);
            });
        } else {
            $this->doDeactivatePlugin($redirectAfter);
        }
    }

    public function uninstall($redirectAfter = true)
    {
        $this->dieIfPluginsAdminIsDisabled();

        $params = [
            'module' => 'CorePluginsAdmin',
            'action' => 'uninstall',
            'pluginName' => Common::getRequestVar('pluginName'),
            'nonce' => Common::getRequestVar('nonce'),
            'referrer' => urlencode(Url::getReferrer()),
        ];
        if (!$this->passwordVerify->requirePasswordVerifiedRecently($params)) {
            return;
        }

        $pluginName = $this->initPluginModification(static::UNINSTALL_NONCE);

        $uninstalled = $this->pluginManager->uninstallPlugin($pluginName);

        if (!$uninstalled) {
            $path = Plugin\Manager::getPluginDirectory($pluginName) . '/';

            $messagePermissions = Filechecks::getErrorMessageMissingPermissions($path);

            $messageIntro = $this->translator->translate("Warning: \"%s\" could not be uninstalled. Piwik did not have enough permission to delete the files in $path. ",
                $pluginName);
            $exitMessage  = $messageIntro . "<br/><br/>" . $messagePermissions;
            $exitMessage .= "<br> Or manually delete this directory (using FTP or SSH access)";

            $ex = new MissingFilePermissionException($exitMessage);
            $ex->setIsHtmlMessage();

            throw $ex;
        }

        $this->redirectAfterModification($redirectAfter);
    }

    public function showLicense()
    {
        Piwik::checkUserHasSomeViewAccess();

        $pluginName = Common::getRequestVar('pluginName', null, 'string');

        if (!Plugin\Manager::getInstance()->isPluginInFilesystem($pluginName)) {
            throw new Exception('Invalid plugin');
        }

        $metadata = new Plugin\MetadataLoader($pluginName);
        $license_file = $metadata->getPathToLicenseFile();

        $license = 'No license file found for this plugin.';
        if(!empty($license_file)) {
            $license = file_get_contents($license_file);
            $license = nl2br($license);
        }

        $view = $this->configureView('@CorePluginsAdmin/license');
        $view->pluginName = $pluginName;
        $view->license = $license;
        return $view->render();
    }

    protected function initPluginModification($nonceName)
    {
        Piwik::checkUserHasSuperUserAccess();

        $nonce = Common::getRequestVar('nonce', null, 'string');

        if (!Nonce::verifyNonce($nonceName, $nonce)) {
            throw new \Exception($this->translator->translate('General_ExceptionSecurityCheckFailed'));
        }

        Nonce::discardNonce($nonceName);

        $pluginName = Common::getRequestVar('pluginName', null, 'string');

        if (!$this->pluginManager->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name');
        }

        return $pluginName;
    }

    protected function redirectAfterModification($redirectAfter)
    {
        if (!$redirectAfter) {
            return;
        }

        $referrer = Common::getRequestVar('referrer', false);
        $referrer = Common::unsanitizeInputValue($referrer);
        if (!empty($referrer)
            && Url::isLocalUrl($referrer)
        ) {
            Url::redirectToUrl($referrer);
        } else {
            Url::redirectToReferrer();
        }
    }

    private function tryToRepairPiwik()
    {
        // in case any opcaches etc were not cleared after an update for instance. Might prevent from getting the
        // error again
        try {
            Filesystem::deleteAllCacheOnUpdate();
        } catch (Exception $e) {}
    }

    /**
     * Let Super User troubleshoot in safe mode, even when Login is broken, with this special trick
     *
     * @return bool
     * @throws Exception
     */
    protected function isAllowedToTroubleshootAsSuperUser()
    {
        $isAllowedToTroubleshootAsSuperUser = false;
        $salt = SettingsPiwik::getSalt();
        if (!empty($salt)) {
            $saltFromRequest = Common::getRequestVar('i_am_super_user', '', 'string');
            $isAllowedToTroubleshootAsSuperUser = ($salt == $saltFromRequest);
        }
        return $isAllowedToTroubleshootAsSuperUser;
    }

    /**
     * @param $redirectAfter
     * @throws Exception
     */
    protected function doDeactivatePlugin($redirectAfter)
    {
        $pluginName = $this->initPluginModification(static::DEACTIVATE_NONCE);
        $this->dieIfPluginsAdminIsDisabled();

        $this->pluginManager->deactivatePlugin($pluginName);
        $this->redirectAfterModification($redirectAfter);
    }

}
