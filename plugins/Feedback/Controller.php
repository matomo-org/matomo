<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Date;
use Piwik\View;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Version;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Feedback\ReferReminder;
use Piwik\Plugins\Feedback\FeedbackReminder;
use Piwik\DataTable\Renderer\Json;

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

        $feedbackReminder = new FeedbackReminder();
        $feedbackReminder->setUserOption($nextReminder);
        
        Json::sendHeaderJSON();
        return json_encode(['Next reminder date: ' . $nextReminder]);
    }

    public function updateReferReminderDate()
    {
        Piwik::checkUserIsNotAnonymous();

        $nextReminder = Common::getRequestVar('nextReminder');

        if ($nextReminder !== Feedback::NEVER_REMIND_ME_AGAIN) {
            $nextReminder = Date::now()->getStartOfDay()->addDay($nextReminder)->toString('Y-m-d');
        }

        $referReminder = new ReferReminder();
        $referReminder->setUserOption($nextReminder);

        Json::sendHeaderJSON();
        return json_encode(['Next reminder date: ' . $nextReminder]);
    }
}
