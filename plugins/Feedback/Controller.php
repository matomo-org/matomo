<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Mail;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    function index()
    {
        $view = new View('@Feedback/index');
        $this->setGeneralVariablesView($view);
        $view->piwikVersion = Version::VERSION;
        return $view->render();
    }
}
