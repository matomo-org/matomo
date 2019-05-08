<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Common;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
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

    public function updateFeedbackReminder()
    {
        // -1 means "never remind me again", otherwise add the interval onto today's date
        $nextReminder = Common::getRequestVar('nextReminder', -1, 'int');
        if ($nextReminder !== -1) {
            $nextReminder = Date::today()->addDay($nextReminder)->toString('Y-m-d');
        }
        $optionKey = 'CoreHome.nextFeedbackReminder.' . Piwik::getCurrentUserLogin();
        Option::set($optionKey, $nextReminder);
        return '';
    }
}
