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
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Feedback\FeedbackReminder;

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
            'Controller.CoreHome.index.end'          => 'renderViewsAndAddToPage'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Feedback/stylesheets/feedback.less";
        $stylesheets[] = "plugins/Feedback/vue/src/RateFeature/RateFeature.less";
        $stylesheets[] = "plugins/Feedback/angularjs/feedback-popup/feedback-popup.directive.less";
    }

    public function getJsFiles(&$jsFiles)
    {
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
        $translationKeys[] = 'Feedback_WontShowAgain';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'General_Cancel';
    }

    public function renderViewsAndAddToPage(&$pageHtml)
    {
        $feedbackPopopView = $this->renderFeedbackPopup();

        $views = [$feedbackPopopView];
        $implodedViews = implode('', $views);

        $endOfBody = strpos($pageHtml, '</body>');
        $pageHtml = substr_replace($pageHtml, $implodedViews, $endOfBody, 0);
    }

    public function renderFeedbackPopup()
    {
        $popupView = new View('@Feedback/feedbackPopup');
        $popupView->promptForFeedback = (int)$this->getShouldPromptForFeedback();

        return $popupView->render();
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

        $feedbackReminder = new FeedbackReminder();
        $nextReminderDate = $feedbackReminder->getUserOption();

        if ($nextReminderDate === self::NEVER_REMIND_ME_AGAIN) {
            return false;
        }

        if ($nextReminderDate === false) {
            $nextReminder = Date::now()->getStartOfDay()->addDay(90)->toString('Y-m-d');
            $feedbackReminder->setUserOption($nextReminder);

            return false;
        }

        $now = Date::now()->getTimestamp();
        $nextReminderDate = Date::factory($nextReminderDate);

        return $nextReminderDate->getTimestamp() <= $now;
    }

    // needs to be protected not private for testing purpose
    protected function isDisabledInTestMode()
    {
        return defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE && !Common::getRequestVar('forceFeedbackTest', false);
    }

}
