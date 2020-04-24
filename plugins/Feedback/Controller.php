<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Common;
use Piwik\Container\StaticContainer;
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
        $popularHelpTopics = StaticContainer::get('popularHelpTopics');
        $view->popularHelpTopics = $popularHelpTopics;
        $view->piwikVersion = Version::VERSION;
        return $view->render();
    }

    /**
     * Store the next date that the feedback reminder popup should be displayed to this user.
     */
    public function updateFeedbackReminderDate()
    {
        Piwik::checkUserIsNotAnonymous();
        $nextReminder = Common::getRequestVar('nextReminder');
        if ($nextReminder !== Feedback::NEVER_REMIND_ME_AGAIN) {
            $nextReminder = Date::now()->getStartOfDay()->addDay($nextReminder)->toString('Y-m-d');
        }

        $optionKey = 'Feedback.nextFeedbackReminder.' . Piwik::getCurrentUserLogin();
        Option::set($optionKey, $nextReminder);
    }
}
