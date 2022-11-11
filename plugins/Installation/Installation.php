<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Exception\NotYetInstalledException;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\Installation\Exception\DatabaseConnectionFailedException;
use Piwik\SettingsPiwik;
use Piwik\SiteContentDetector;
use Piwik\View as PiwikView;

/**
 *
 */
class Installation extends \Piwik\Plugin
{
    protected $installationControllerName = '\\Piwik\\Plugins\\Installation\\Controller';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $hooks = array(
            'Config.NoConfigurationFile'      => 'dispatch',
            'Config.badConfigurationFile'     => 'dispatch',
            'Db.cannotConnectToDb'            => 'displayDbConnectionMessage',
            'Request.dispatch'                => 'dispatchIfNotInstalledYet',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        );
        return $hooks;
    }

    public function displayDbConnectionMessage($exception = null)
    {
        Common::sendResponseCode(500);

        $errorMessage = $exception->getMessage();

        if (Request::isApiRequest(null)) {
            $ex = new DatabaseConnectionFailedException($errorMessage);
            throw $ex;
        }

        $view = new PiwikView("@Installation/cannotConnectToDb");
        $view->exceptionMessage = $errorMessage;

        $ex = new DatabaseConnectionFailedException($view->render());
        $ex->setIsHtmlMessage();

        throw $ex;
    }

    public function dispatchIfNotInstalledYet(&$module, &$action, &$parameters)
    {
        $general = Config::getInstance()->General;

        if (!SettingsPiwik::isMatomoInstalled() && !$general['enable_installer']) {
            throw new NotYetInstalledException('Matomo is not set up yet');
        }

        if (empty($general['installation_in_progress'])) {
            return;
        }

        if ($module == 'Installation') {
            return;
        }

        $module = 'Installation';

        if (!$this->isAllowedAction($action)) {
            $action = 'welcome';
        }

        $parameters = array();
    }

    public function setControllerToLoad($newControllerName)
    {
        $this->installationControllerName = $newControllerName;
    }

    protected function getInstallationController()
    {
        return new $this->installationControllerName(new SiteContentDetector());
    }

    /**
     * @param \Exception|null $exception
     */
    public function dispatch($exception = null)
    {
        if ($exception) {
            $message = $exception->getMessage();
        } else {
            $message = '';
        }

        $action = Common::getRequestVar('action', 'welcome', 'string');

        if ($this->isAllowedAction($action) && (!defined('PIWIK_ENABLE_DISPATCH') || PIWIK_ENABLE_DISPATCH)) {
            echo FrontController::getInstance()->dispatch('Installation', $action, array($message));
        } elseif (defined('PIWIK_ENABLE_DISPATCH') && !PIWIK_ENABLE_DISPATCH) {
            if ($exception && $exception instanceof \Exception) {
                throw $exception;
            }
            return;
        } else {
            Piwik::exitWithErrorMessage($this->getMessageToInviteUserToInstallPiwik($message));
        }

        exit;
    }

    /**
     * Adds CSS files to list of CSS files for asset manager.
     */
    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Installation/stylesheets/systemCheckPage.less";
    }

    private function isAllowedAction($action)
    {
        $controller = $this->getInstallationController();
        $isActionAllowed = in_array($action, array('saveLanguage', 'getInstallationCss', 'getInstallationJs', 'reuseTables'));

        return in_array($action, array_keys($controller->getInstallationSteps()))
                || $isActionAllowed;
    }

    /**
     * @param $message
     * @return string
     */
    private function getMessageToInviteUserToInstallPiwik($message)
    {
        $messageWhenPiwikSeemsNotInstalled =
            $message .
            "\n<br/>" .
            Piwik::translate('Installation_NoConfigFileFound') .
            "<br/><b>» " .
            Piwik::translate('Installation_YouMayInstallPiwikNow', array("<a href='index.php'>", "</a></b>")) .
            "<br/><small>" .
            Piwik::translate('Installation_IfPiwikInstalledBeforeTablesCanBeKept') .
            "</small>";
        return $messageWhenPiwikSeemsNotInstalled;
    }
}
