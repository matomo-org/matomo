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
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model;
use Piwik\View;

/**
 *
 */
class Feedback extends \Piwik\Plugin
{
    const NEVER_REMIND_ME_AGAIN = "-1";

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Controller.CoreHome.index.end'          => 'renderFeedbackPopup'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Feedback/stylesheets/feedback.less";
        $stylesheets[] = "plugins/Feedback/angularjs/ratefeature/ratefeature.directive.less";
        $stylesheets[] = "plugins/Feedback/angularjs/feedback-popup/feedback-popup.directive.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Feedback/angularjs/ratefeature/ratefeature-model.service.js";
        $jsFiles[] = "plugins/Feedback/angularjs/ratefeature/ratefeature.controller.js";
        $jsFiles[] = "plugins/Feedback/angularjs/ratefeature/ratefeature.directive.js";
        $jsFiles[] = "plugins/Feedback/angularjs/feedback-popup/feedback-popup.controller.js";
        $jsFiles[] = "plugins/Feedback/angularjs/feedback-popup/feedback-popup.directive.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Feedback_ThankYou';
        $translationKeys[] = 'Feedback_RateFeatureTitle';
        $translationKeys[] = 'Feedback_RateFeatureThankYouTitle';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLike';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislike';
        $translationKeys[] = 'Feedback_SendFeedback';
        $translationKeys[] = 'Feedback_RateFeatureSendFeedbackInformation';
        $translationKeys[] = 'Feedback_ReviewMatomoTitle';
        $translationKeys[] = 'Feedback_PleaseLeaveExternalReviewForMatomo';
        $translationKeys[] = 'Feedback_RemindMeLater';
        $translationKeys[] = 'Feedback_NeverAskMeAgain';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'General_Cancel';
    }

    public function renderFeedbackPopup(&$pageHtml)
    {
        $popupView = new View('@Feedback/feedbackPopup');
        $popupView->promptForFeedback = (int)$this->getShouldPromptForFeedback();
        $popupHtml = $popupView->render();
        $endOfBody = strpos($pageHtml, "</body>");
        $pageHtml = substr_replace($pageHtml, $popupHtml, $endOfBody, 0);
    }

    public function getShouldPromptForFeedback()
    {
        if (Piwik::isUserIsAnonymous()) {
            return false;
        }

        // Hide Feedback popup in all tests except if forced
        if ($this->isDisabledInTestMode()) {
            return false;
        }

        $login = Piwik::getCurrentUserLogin();
        $feedbackReminderKey = 'Feedback.nextFeedbackReminder.' . Piwik::getCurrentUserLogin();
        $nextReminderDate = Option::get($feedbackReminderKey);

        if ($nextReminderDate === self::NEVER_REMIND_ME_AGAIN) {
            return false;
        }

        if ($nextReminderDate === false) {
            $model = new Model();
            $user = $model->getUser($login);
            if (empty($user['date_registered'])) {
                return false;
            }
            $nextReminderDate = Date::factory($user['date_registered'])->addDay(90)->getStartOfDay();
        } else {
            $nextReminderDate = Date::factory($nextReminderDate);
        }

        $now = Date::now()->getTimestamp();
        return $nextReminderDate->getTimestamp() <= $now;
    }

    // needs to be protected not private for testing purpose
    protected function isDisabledInTestMode()
    {
        return defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE && !Common::getRequestVar('forceFeedbackTest', false);
    }

}
