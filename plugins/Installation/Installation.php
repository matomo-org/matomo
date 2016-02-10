<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\Installation\Exception\DatabaseConnectionFailedException;
use Piwik\View as PiwikView;

/**
 *
 */
class Installation extends \Piwik\Plugin
{
    protected $installationControllerName = '\\Piwik\\Plugins\\Installation\\Controller';

    /**
     * @see Piwik\Plugin::registerEvents
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

        if (Request::isApiRequest($_GET)) {
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
        return new $this->installationControllerName();
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

        if ($this->isAllowedAction($action)) {
            echo FrontController::getInstance()->dispatch('Installation', $action, array($message));
        } else {
            Piwik::exitWithErrorMessage(Piwik::translate('Installation_NoConfigFound'));
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
        $isActionWhiteListed = in_array($action, array('saveLanguage', 'getBaseCss', 'reuseTables'));

        return in_array($action, array_keys($controller->getInstallationSteps()))
                || $isActionWhiteListed;
    }
}
