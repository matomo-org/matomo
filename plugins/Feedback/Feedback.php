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
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Site;
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
        $stylesheets[] = "plugins/Feedback/vue/src/RateFeature/RateFeature.less";
        $stylesheets[] = "plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.less";
    }

    public function getJsFiles(&$jsFiles)
    {
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Feedback_ThankYou';
        $translationKeys[] = 'Feedback_ThankYouForSpreading';
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
        $translationKeys[] = 'Feedback_ReferMatomo';
        $translationKeys[] = 'Feedback_ReferBannerTitle';
        $translationKeys[] = 'Feedback_ReferBannerLonger';
        $translationKeys[] = 'Feedback_ReferBannerSocialShareText';
        $translationKeys[] = 'Feedback_ReferBannerEmailShareSubject';
        $translationKeys[] = 'Feedback_ReferBannerEmailShareBody';
        $translationKeys[] = 'Feedback_WontShowAgain';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'General_Cancel';
        $translationKeys[] = 'Feedback_Question0';
        $translationKeys[] = 'Feedback_Question1';
        $translationKeys[] = 'Feedback_Question2';
        $translationKeys[] = 'Feedback_Question3';
        $translationKeys[] = 'Feedback_Question4';
        $translationKeys[] = 'Feedback_FeedbackTitle';
        $translationKeys[] = 'Feedback_FeedbackSubtitle';
        $translationKeys[] = 'Feedback_ThankYourForFeedback';
        $translationKeys[] = 'Feedback_Policy';
        $translationKeys[] = 'Feedback_ThankYourForFeedback';
        $translationKeys[] = 'Feedback_ThankYou';
    }

    public function renderViewsAndAddToPage(&$pageHtml)
    {
        $feedbackQuestionBanner = $this->renderFeedbackQuestion();

        $matches = preg_split('/(<body.*?>)/i', $pageHtml, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $pageHtml = $matches[0] . $matches[1] . $feedbackQuestionBanner . $matches[2];
    }


    public function renderFeedbackQuestion()
    {
        $feedbackQuestionBanner = new View('@Feedback/feedbackQuestionBanner');
        $feedbackQuestionBanner->showQuestionBanner = (int)$this->getShouldPromptForFeedback();

        return $feedbackQuestionBanner->render();
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
            $nextReminder = Date::now()->getStartOfDay()->addMonth(6)->toString('Y-m-d');
            $feedbackReminder->setUserOption($nextReminder);

            return false;
        }

        $now = Date::now()->getTimestamp();
        $nextReminderDate = Date::factory($nextReminderDate);

        // if user is created 6 month ago, it won't show.
        // I am not sure if this test really works. Because fake access is trade as isUserIsAnonymous
        $userCreatedDate =Piwik::getCurrentUserCreationData();
        if (!empty($userCreatedDate) && Date::factory($userCreatedDate)->addMonth(6)->getTimestamp() < $now) {
            return false;
        }

        return $nextReminderDate->getTimestamp() <= $now;
    }

    // needs to be protected not private for testing purpose
    protected function isDisabledInTestMode()
    {
        return defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE && !Common::getRequestVar('forceFeedbackTest', false);
    }

}
