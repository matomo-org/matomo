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
        $stylesheets[] = "plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.less";
        $stylesheets[] = "plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.less";
    }

    public function getJsFiles(&$jsFiles)
    {
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Feedback_ThankYouHeart';
        $translationKeys[] = 'Feedback_ThankYouForSpreading';
        $translationKeys[] = 'Feedback_RateFeatureTitle';
        $translationKeys[] = 'Feedback_RateFeatureThankYouTitle';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLike';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLikeNamedFeature';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLikeExtra';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLikeExtraConfigurable';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLikeExtraEasy';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageLikeExtraUseful';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislike';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislikeNamedFeature';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislikeExtra';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislikeExtraBugs';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislikeExtraMissing';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislikeExtraSpeed';
        $translationKeys[] = 'Feedback_RateFeatureLeaveMessageDislikeExtraEasier';
        $translationKeys[] = 'Feedback_RateFeatureOtherReason';
        $translationKeys[] = 'Feedback_SendFeedback';
        $translationKeys[] = 'Feedback_RateFeatureSendFeedbackInformation';
        $translationKeys[] = 'Feedback_RateFeatureUsefulInfo';
        $translationKeys[] = 'Feedback_RateFeatureEasyToUse';
        $translationKeys[] = 'Feedback_RateFeatureConfigurable';
        $translationKeys[] = 'Feedback_RateFeatureDislikeAddMissingFeatures';
        $translationKeys[] = 'Feedback_RateFeatureDislikeMakeEasier';
        $translationKeys[] = 'Feedback_RateFeatureDislikeSpeedUp';
        $translationKeys[] = 'Feedback_RateFeatureDislikeFixBugs';
        $translationKeys[] = 'Feedback_ReviewMatomoTitle';
        $translationKeys[] = 'Feedback_PleaseLeaveExternalReviewForMatomo';
        $translationKeys[] = 'Feedback_RemindMeLater';
        $translationKeys[] = 'Feedback_NeverAskMeAgain';
        $translationKeys[] = 'Feedback_WontShowAgain';
        $translationKeys[] = 'Feedback_AppreciateFeedback';
        $translationKeys[] = 'Feedback_Policy';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'General_Cancel';
        $translationKeys[] = 'Feedback_Question0';
        $translationKeys[] = 'Feedback_Question1';
        $translationKeys[] = 'Feedback_Question2';
        $translationKeys[] = 'Feedback_Question3';
        $translationKeys[] = 'Feedback_Question4';
        $translationKeys[] = 'Feedback_FeedbackTitle';
        $translationKeys[] = 'Feedback_FeedbackSubtitle';
        $translationKeys[] = 'Feedback_Policy';
        $translationKeys[] = 'Feedback_ThankYourForFeedback';
        $translationKeys[] = 'Feedback_ThankYou';
        $translationKeys[] = 'Feedback_MessageBodyValidationError';
    }

    public function renderViewsAndAddToPage(&$pageHtml)
    {
        //only show on superuser
        if (!Piwik::hasUserSuperUserAccess()) {
            return $pageHtml;
        }
        $feedbackQuestionBanner = $this->renderFeedbackQuestion();

        $matches = preg_split('/(<body.*?>)/i', $pageHtml, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $pageHtml = $matches[0] . $matches[1] . $feedbackQuestionBanner . $matches[2];
    }


    public function renderFeedbackQuestion()
    {
        $feedbackQuestionBanner = new View('@Feedback/feedbackQuestionBanner');
        $feedbackQuestionBanner->showQuestionBanner = (int)$this->showQuestionBanner();

        return $feedbackQuestionBanner->render();
    }

    public function showQuestionBanner()
    {
        if (Piwik::isUserIsAnonymous()) {
            return false;
        }

        // Hide Feedback popup in all tests except if forced
        if ($this->isDisabledInTestMode()) {
            return false;
        }

        $shouldShowQuestionBanner = true;

        Piwik::postEvent('Feedback.showQuestionBanner', [&$shouldShowQuestionBanner]);

        if (!$shouldShowQuestionBanner) {
            return false;
        }

        $feedbackReminder = new FeedbackReminder();
        $nextReminderDate = $feedbackReminder->getUserOption();
        $now = Date::now()->getTimestamp();

        // If there isn't any reminder date set, or never remind me was selected previously (-1) we determine a new date
        if ($nextReminderDate === false || $nextReminderDate <= 0) {

            // if user was created within the last 6 months, we set the date to 6 months after his creation date
            $userCreatedDate = Piwik::getCurrentUserCreationDate();
            if (!empty($userCreatedDate) && Date::factory($userCreatedDate)->addMonth(6)->getTimestamp() > $now) {
                $nextReminder = Date::factory($userCreatedDate)->addMonth(6)->toString('Y-m-d');
                $feedbackReminder->setUserOption($nextReminder);
                return false;
            }

            // Otherwise we set the date to somewhen within the next 6 months
            $nextReminder = Date::now()->getStartOfDay()->addDay(Common::getRandomInt(1, 6*30))->toString('Y-m-d');
            $feedbackReminder->setUserOption($nextReminder);
            return false;
        }

        $nextReminderDate = Date::factory($nextReminderDate);
        if ($nextReminderDate->getTimestamp() > $now) {
            return false;
        }
        return true;

    }

    // needs to be protected not private for testing purpose
    protected function isDisabledInTestMode()
    {
        return defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE && !Common::getRequestVar('forceFeedbackTest', false);
    }

}
