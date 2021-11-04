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
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Renderer\Json;
use Piwik\Date;
use Piwik\IP;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Version;

/**
 * API for plugin Feedback
 *
 * @method static \Piwik\Plugins\Feedback\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Sends feedback for a specific feature to the Matomo team or alternatively to the email address configured in the
     * config: "feedback_email_address".
     *
     * @param string      $featureName  The name of a feature you want to give feedback to.
     * @param bool|int    $like         Whether you like the feature or not
     * @param string|bool $message      A message containing the actual feedback
     */
    public function sendFeedbackForFeature($featureName, $like, $message = false)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        $featureName = $this->getEnglishTranslationForFeatureName($featureName);

        $likeText = 'Yes';
        if (empty($like)) {
            $likeText = 'No';
        }

        $body = sprintf("Feature: %s\nLike: %s\n", $featureName, $likeText);

        $feedbackMessage = "";
        if (!empty($message) && $message != 'undefined') {
            $feedbackMessage = sprintf("Feedback:\n%s\n", trim($message));
        }
        $body .= $feedbackMessage ? $feedbackMessage : " \n";

        $subject = sprintf("%s for %s %s",
            empty($like) ? "-1" : "+1",
            $featureName,
            empty($feedbackMessage) ? "" : "(w/ feedback)"
        );

        $this->sendMail($subject, $body);
    }

    /**
     * Sends feedback for a specific feature to the Matomo team or alternatively to the email address configured in the
     * config: "feedback_email_address".
     *
     * @param $question
     * @param string|bool $message A message containing the actual feedback
     * @throws \Piwik\NoAccessException
     */
    public function sendFeedbackForSurvey($question,  $message = false)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        $featureName = $this->getEnglishTranslationForFeatureName($question);


        $body = sprintf("Question: %s\n", $featureName);

        $feedbackMessage = "";
        if (!empty($message) && $message != 'undefined') {
            $feedbackMessage = sprintf("Answer:\n%s\n", trim($message));
        }
        $body .= $feedbackMessage ? $feedbackMessage : " \n";

        $subject = sprintf("%s for %s %s",
          empty($like) ? "-1" : "+1",
          $featureName,
          empty($feedbackMessage) ? "" : "(w/ feedback Survey)"
        );

        $this->sendMail($subject, $body);

        //if feed is sent never show again.
        $feedbackReminder = new FeedbackReminder();
        $feedbackReminder->setUserOption(-1);

    }

    public function updateFeedbackReminderDate()
    {
        Piwik::checkUserIsNotAnonymous();

        //push reminder for 6 month
        $nextReminder = Date::now()->getStartOfDay()->addMonth(6)->toString('Y-m-d');
        $feedbackReminder = new FeedbackReminder();
        $feedbackReminder->setUserOption($nextReminder);

        Json::sendHeaderJSON();
        return json_encode(['Next reminder date: ' . $nextReminder]);
    }

    private function sendMail($subject, $body)
    {
        $feedbackEmailAddress = Config::getInstance()->General['feedback_email_address'];

        $subject = '[ Feedback Feature - Matomo ] ' . $subject;
        $body    = Common::unsanitizeInputValue($body) . "\n"
                 . 'Matomo ' . Version::VERSION . "\n"
                 . 'URL: ' . Url::getReferrer() . "\n";

        $mail = new Mail();
        $mail->setFrom(Piwik::getCurrentUserEmail());
        $mail->addTo($feedbackEmailAddress, 'Matomo Team');
        $mail->setSubject($subject);
        $mail->setBodyText($body);
        @$mail->send();
    }

    private function getEnglishTranslationForFeatureName($featureName)
    {
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        if ($translator->getCurrentLanguage() == 'en') {
            return $featureName;
        }

        $translationKeyForFeature = $translator->findTranslationKeyForTranslation($featureName);

        return Piwik::translate($translationKeyForFeature, array(), 'en');
    }
}
