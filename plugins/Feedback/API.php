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
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\SettingsServer;
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
     * @param string|null $featureName  The name of a feature you want to give feedback to.
     * @param string|null $like         Whether you like the feature or not
     * @param string|null $choice       Multiple choice option chosen
     * @param string|null $message      A message containing the actual feedback
     */
    public function sendFeedbackForFeature($featureName, $like = null, $choice = null, $message = null)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        if (empty($message) || $message === 'undefined' ||  strlen($message) < 4) {
            return Piwik::translate("Feedback_FormNotEnoughFeedbackText");
        }

        $featureName = $this->getEnglishTranslationForFeatureName($featureName);

        $likeText = 'Yes';
        if (empty($like)) {
            $likeText = 'No';
        }

        $body = sprintf("Feature: %s\nLike: %s\n", $featureName, $likeText);

        if (!empty($choice) && $choice !== 'undefined') {
            $body .= "Choice: ".$choice."\n";
        }

        $body .= sprintf("Feedback:\n%s\n", trim($message));

        $subject = sprintf("%s for %s",
            empty($like) ? "-1" : "+1",
            $featureName
        );

        // Determine where Matomo is running and add as source
        if (Config::getHostname() === 'demo.matomo.cloud') {
            $source = 'Demo';
        } else if (SettingsServer::isMatomoForWordPress()) {
            $source = 'Wordpress';
        } else {
            $source = 'On-Premise';
        }
        $body .= "Source: ".$source."\n";

        $this->sendMail($subject, $body);

        return 'success';
    }

    /**
     * Sends feedback for a specific feature to the Matomo team or alternatively to the email address configured in the
     * config: "feedback_email_address".
     *
     * @param $question
     * @param string|bool $message A message containing the actual feedback
     * @throws \Piwik\NoAccessException
     * @throws \Exception
     */
    public function sendFeedbackForSurvey($question,  $message = false)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        if ($message == '' || strlen($message) < 10) {
            return Piwik::translate("Feedback_MessageBodyValidationError");
        }

        $featureName = $this->getEnglishTranslationForFeatureName($question);
        $body = sprintf("Question: %s\n", $featureName);
        $feedbackMessage = "";

        if (!empty($message) && $message !== 'undefined') {
            $feedbackMessage = sprintf("Answer:\n%s\n", trim($message));
        }

        $body .= $feedbackMessage ? $feedbackMessage : " \n";

        $subject = sprintf("%s for %s %s",
          empty($like) ? "-1" : "+1",
          $featureName,
          empty($feedbackMessage) ? "" : "(w/ feedback Survey)"
        );

        $this->sendMail($subject, $body);

        //if feedback is sent set next one to 6 month.
        $nextReminder = Date::now()->getStartOfDay()->addMonth(6)->toString('Y-m-d');
        $feedbackReminder = new FeedbackReminder();
        $feedbackReminder->setUserOption($nextReminder);

        return 'success';

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
