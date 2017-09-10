<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Common;
use Piwik\Date;
use Piwik\Filesystem;
use Piwik\Log;
use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CorePluginsAdmin\Controller as PluginsController;
use Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin;
use Piwik\Plugins\CorePluginsAdmin\PluginInstaller;
use Piwik\Plugins\Marketplace\Input\Mode;
use Piwik\Plugins\Marketplace\Input\PluginName;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\ProxyHttp;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;
use Exception;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    const UPDATE_NONCE = 'Marketplace.updatePlugin';
    const INSTALL_NONCE = 'Marketplace.installPlugin';

    /**
     * @var LicenseKey
     */
    private $licenseKey;
    /**
     * @var Plugins
     */
    private $plugins;

    /**
     * @var Api\Client
     */
    private $marketplaceApi;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var PluginInstaller
     */
    private $pluginInstaller;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var Environment
     */
    private $environment;

    public function __construct(LicenseKey $licenseKey, Plugins $plugins, Api\Client $marketplaceApi, Consumer $consumer, PluginInstaller $pluginInstaller, Environment $environment)
    {
        $this->licenseKey = $licenseKey;
        $this->plugins = $plugins;
        $this->marketplaceApi = $marketplaceApi;
        $this->consumer = $consumer;
        $this->pluginInstaller = $pluginInstaller;
        $this->pluginManager = Plugin\Manager::getInstance();
        $this->environment = $environment;

        parent::__construct();
    }

    public function subscriptionOverview()
    {
        Piwik::checkUserHasSuperUserAccess();

        // we want to make sure to fetch the latest results, eg in case user has purchased a subscription meanwhile
        // this is also like a self-repair to clear the caches :)
        $this->marketplaceApi->clearAllCacheEntries();
        $this->consumer->clearCache();

        $hasLicenseKey = $this->licenseKey->has();

        $consumer = $this->consumer->getConsumer();

        $subscriptions = array();
        $loginUrl = '';

        if (!empty($consumer['loginUrl'])) {
            $loginUrl = $consumer['loginUrl'];
        }

        if (!empty($consumer['licenses'])) {
            foreach ($consumer['licenses'] as $subscription) {
                $subscription['start'] = $this->getPrettyLongDate($subscription['startDate']);
                $subscription['end'] = $this->getPrettyLongDate($subscription['endDate']);
                $subscription['nextPayment'] = $this->getPrettyLongDate($subscription['nextPaymentDate']);
                $subscriptions[] = $subscription;
            }
        }

        return $this->renderTemplate('@Marketplace/subscription-overview', array(
            'hasLicenseKey' => $hasLicenseKey,
            'subscriptions' => $subscriptions,
            'loginUrl' => $loginUrl,
            'numUsers' => $this->environment->getNumUsers()
        ));
    }

    private function getPrettyLongDate($date)
    {
        if (empty($date)) {
            return '';
        }

        return Date::factory($date)->getLocalized(Date::DATE_FORMAT_LONG);
    }

    public function pluginDetails()
    {
        $view = $this->configureViewAndCheckPermission('@Marketplace/plugin-details');

        $pluginName = new PluginName();
        $pluginName = $pluginName->getPluginName();

        $activeTab  = Common::getRequestVar('activeTab', '', 'string');
        if ('changelog' !== $activeTab) {
            $activeTab = '';
        }

        try {
            $plugin = $this->plugins->getPluginInfo($pluginName);

            if (empty($plugin['name'])) {
                throw new Exception('Plugin does not exist');
            }
        } catch (Exception $e) {
            $plugin = null;
            $view->errorMessage = $e->getMessage();
        }

        $view->plugin       = $plugin;
        $view->isSuperUser  = Piwik::hasUserSuperUserAccess();
        $view->installNonce = Nonce::getNonce(static::INSTALL_NONCE);
        $view->updateNonce  = Nonce::getNonce(static::UPDATE_NONCE);
        $view->activeTab    = $activeTab;
        $view->isAutoUpdatePossible = SettingsPiwik::isAutoUpdatePossible();
        $view->isAutoUpdateEnabled = SettingsPiwik::isAutoUpdateEnabled();
        $view->numUsers = $this->environment->getNumUsers();

        return $view->render();
    }

    public function download()
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->dieIfPluginsAdminIsDisabled();

        $pluginName = new PluginName();
        $pluginName = $pluginName->getPluginName();

        Nonce::checkNonce($pluginName);

        $filename = $pluginName . '.zip';

        try {
            $pathToPlugin = $this->marketplaceApi->download($pluginName);
            ProxyHttp::serverStaticFile($pathToPlugin, 'application/zip', $expire = 0, $start = false, $end = false, $filename);
        } catch (Exception $e) {
            Common::sendResponseCode(500);
            Log::warning('Could not download file . ' . $e->getMessage());
        }

        if (!empty($pathToPlugin)) {
            Filesystem::deleteFileIfExists($pathToPlugin);
        }
    }

    public function overview()
    {
        $view = $this->configureViewAndCheckPermission('@Marketplace/overview');

        $show  = Common::getRequestVar('show', 'plugins', 'string');
        $query = Common::getRequestVar('query', '', 'string', $_POST);

        $sort = new Sort();
        $sort = $sort->getSort();

        $mode = new Mode();
        $mode = $mode->getMode();

        // we're fetching all available plugins to decide which tabs need to be shown in the UI and to know the number
        // of total available plugins
        $allPlugins = $this->plugins->getAllPlugins();
        $allThemes   = $this->plugins->getAllThemes();
        $paidPlugins = $this->plugins->getAllPaidPlugins();

        $showThemes  = ($show === 'themes');
        $showPlugins = !$showThemes;
        $showPaid    = ($show === 'premium');
        $showAll    = !$showPaid;

        if ($showPlugins && $showPaid) {
            $type = PurchaseType::TYPE_PAID;
            $view->numAvailablePlugins = count($paidPlugins);
        } elseif ($showPlugins && $showAll) {
            $type = PurchaseType::TYPE_ALL;
            $view->numAvailablePlugins = count($allPlugins);
        } else {
            $type = PurchaseType::TYPE_ALL;
            $view->numAvailablePlugins = count($allThemes);
        }

        $pluginsToShow = $this->plugins->searchPlugins($query, $sort, $showThemes, $type);

        $paidPluginsToInstallAtOnce = array();
        if (SettingsPiwik::isAutoUpdatePossible()) {
            foreach ($paidPlugins as $paidPlugin) {
                if ($this->canPluginBeInstalled($paidPlugin)
                    || ($this->pluginManager->isPluginInstalled($paidPlugin['name'])
                        && !$this->pluginManager->isPluginActivated($paidPlugin['name']))) {
                    $paidPluginsToInstallAtOnce[] = $paidPlugin['name'];
                }
            }
        }

        $view->paidPluginsToInstallAtOnce = $paidPluginsToInstallAtOnce;
        $view->pluginsToShow = $pluginsToShow;
        $view->isValidConsumer = $this->consumer->isValidConsumer();
        $view->paidPlugins = $paidPlugins;
        $view->freePlugins = $allPlugins;
        $view->themes = $allThemes;
        $view->showThemes = $showThemes;
        $view->showPlugins = $showPlugins;
        $view->showFree = $showAll;
        $view->showPaid = $showPaid;
        $view->pluginType = $show;
        $view->pluginTypeOptions = array(
            'plugins' => Piwik::translate('General_Plugins'),
            'premium' => Piwik::translate('Marketplace_PaidPlugins'),
            'themes' => Piwik::translate('CorePluginsAdmin_Themes')
        );
        $view->pluginSortOptions = array(
            Sort::METHOD_LAST_UPDATED => Piwik::translate('Marketplace_SortByLastUpdated'),
            Sort::METHOD_POPULAR => Piwik::translate('Marketplace_SortByPopular'),
            Sort::METHOD_NEWEST => Piwik::translate('Marketplace_SortByNewest'),
            Sort::METHOD_ALPHA => Piwik::translate('Marketplace_SortByAlpha'),
        );
        $view->mode = $mode;
        $view->query = $query;
        $view->sort = $sort;
        $view->hasLicenseKey = $this->licenseKey->has();
        $view->hasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();
        $view->installNonce = Nonce::getNonce(static::INSTALL_NONCE);
        $view->updateNonce = Nonce::getNonce(static::UPDATE_NONCE);
        $view->deactivateNonce = Nonce::getNonce(PluginsController::DEACTIVATE_NONCE);
        $view->activateNonce = Nonce::getNonce(PluginsController::ACTIVATE_NONCE);
        $view->isSuperUser = Piwik::hasUserSuperUserAccess();
        $view->isPluginsAdminEnabled = CorePluginsAdmin::isPluginsAdminEnabled();
        $view->isAutoUpdatePossible = SettingsPiwik::isAutoUpdatePossible();
        $view->isAutoUpdateEnabled = SettingsPiwik::isAutoUpdateEnabled();
        $view->isPluginUploadEnabled = CorePluginsAdmin::isPluginUploadEnabled();

        return $view->render();
    }

    public function installAllPaidPlugins()
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->dieIfPluginsAdminIsDisabled();
        Plugin\ControllerAdmin::displayWarningIfConfigFileNotWritable();

        Nonce::checkNonce(static::INSTALL_NONCE);

        $paidPlugins = $this->plugins->getAllPaidPlugins();

        $hasErrors = false;
        foreach ($paidPlugins as $paidPlugin) {
            if (!$this->canPluginBeInstalled($paidPlugin)) {
                continue;
            }

            $pluginName = $paidPlugin['name'];

            try {

                $this->pluginInstaller->installOrUpdatePluginFromMarketplace($pluginName);

            } catch (\Exception $e) {

                $notification = new Notification($e->getMessage());
                $notification->context = Notification::CONTEXT_ERROR;
                if (method_exists($e, 'isHtmlMessage') && $e->isHtmlMessage()) {
                    $notification->raw = true;
                }
                Notification\Manager::notify('Marketplace_Install' . $pluginName, $notification);

                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            Url::redirectToReferrer();
            return;
        }

        $dependency = new Plugin\Dependency();

        for ($i = 0; $i <= 10; $i++) {
            foreach ($paidPlugins as $index => $paidPlugin) {
                if (empty($paidPlugin)) {
                    continue;
                }

                $pluginName = $paidPlugin['name'];

                if ($this->pluginManager->isPluginActivated($pluginName)) {
                    // we do not use unset since it might skip a plugin afterwards when removing index
                    $paidPlugins[$index] = null;
                    continue;
                }

                if (!$this->pluginManager->isPluginInFilesystem($pluginName)) {
                    $paidPlugins[$index] = null;
                    continue;
                }

                if (empty($paidPlugin['require'])
                    || !$dependency->hasDependencyToDisabledPlugin($paidPlugin['require'])) {

                    $paidPlugins[$index] = null;

                    try {
                        $this->pluginManager->activatePlugin($pluginName);
                    } catch (Exception $e) {

                        $hasErrors = true;
                        $notification = new Notification($e->getMessage());
                        $notification->context = Notification::CONTEXT_ERROR;
                        Notification\Manager::notify('Marketplace_Install' . $pluginName, $notification);
                    }
                }
            }

            $paidPlugins = array_filter($paidPlugins);
        }

        if ($hasErrors) {
            $notification = new Notification(Piwik::translate('Marketplace_OnlySomePaidPluginsInstalledAndActivated'));
            $notification->context = Notification::CONTEXT_INFO;
        } else {
            $notification = new Notification(Piwik::translate('Marketplace_AllPaidPluginsInstalledAndActivated'));
            $notification->context = Notification::CONTEXT_SUCCESS;
        }

        Notification\Manager::notify('Marketplace_InstallAll', $notification);

        Url::redirectToReferrer();
    }

    public function updatePlugin()
    {
        $view = $this->createUpdateOrInstallView('updatePlugin', static::UPDATE_NONCE);
        return $view->render();
    }

    public function installPlugin()
    {
        $view = $this->createUpdateOrInstallView('installPlugin', static::INSTALL_NONCE);
        $view->nonce = Nonce::getNonce(PluginsController::ACTIVATE_NONCE);

        return $view->render();
    }

    private function createUpdateOrInstallView($template, $nonceName)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->dieIfPluginsAdminIsDisabled();
        $this->displayWarningIfConfigFileNotWritable();

        $pluginName = $this->getPluginNameIfNonceValid($nonceName);

        $view = new View('@Marketplace/' . $template);
        $this->setBasicVariablesView($view);
        $view->errorMessage = '';
        $view->plugin = array('name' => $pluginName);

        try {
            $this->pluginInstaller->installOrUpdatePluginFromMarketplace($pluginName);

        } catch (\Exception $e) {

            $notification = new Notification($e->getMessage());
            $notification->context = Notification::CONTEXT_ERROR;
            $notification->type = Notification::TYPE_PERSISTENT;
            $notification->flags = Notification::FLAG_CLEAR;
            if (method_exists($e, 'isHtmlMessage') && $e->isHtmlMessage()) {
                $notification->raw = true;
            }
            Notification\Manager::notify('CorePluginsAdmin_InstallPlugin', $notification);

            Url::redirectToReferrer();
            return;
        }

        $view->plugin = $this->plugins->getPluginInfo($pluginName);

        return $view;
    }

    private function getPluginNameIfNonceValid($nonceName)
    {
        $nonce = Common::getRequestVar('nonce', null, 'string');

        if (!Nonce::verifyNonce($nonceName, $nonce)) {
            throw new \Exception(Piwik::translate('General_ExceptionNonceMismatch'));
        }

        Nonce::discardNonce($nonceName);

        $pluginName = Common::getRequestVar('pluginName', null, 'string');

        if (!$this->pluginManager->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name');
        }

        return $pluginName;
    }

    private function dieIfPluginsAdminIsDisabled()
    {
        if (!CorePluginsAdmin::isPluginsAdminEnabled()) {
            throw new \Exception('Enabling, disabling and uninstalling plugins has been disabled by Piwik admins.
            Please contact your Piwik admins with your request so they can assist you.');
        }
    }

    private function canPluginBeInstalled($plugin)
    {
        if (empty($plugin['isDownloadable'])) {
            return false;
        }

        $pluginName = $plugin['name'];

        $isAlreadyInstalled = $this->pluginManager->isPluginInstalled($pluginName)
            || $this->pluginManager->isPluginLoaded($pluginName)
            || $this->pluginManager->isPluginActivated($pluginName);

        return !$isAlreadyInstalled;
    }

    protected function configureViewAndCheckPermission($template)
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View($template);
        $this->setBasicVariablesView($view);
        $this->displayWarningIfConfigFileNotWritable();

        $view->errorMessage = '';

        return $view;
    }
}
