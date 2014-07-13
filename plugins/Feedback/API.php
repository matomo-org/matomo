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
use Piwik\Config;
use Piwik\IP;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Translate;
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
     * Sends feedback for a specific feature to the Piwik team or alternatively to the email address configured in the
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

        $body = sprintf("Feature: %s\nLike: %s\n", $featureName, $likeText, $message);

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

    private function sendMail($subject, $body)
    {
        $feedbackEmailAddress = Config::getInstance()->General['feedback_email_address'];

        $subject = '[ Feedback Feature - Piwik ] ' . $subject;
        $body    = Common::unsanitizeInputValue($body) . "\n"
                 . 'Piwik ' . Version::VERSION . "\n"
                 . 'IP: ' . IP::getIpFromHeader() . "\n"
                 . 'URL: ' . Url::getReferrer() . "\n";

        $mail = new Mail();
        $mail->setFrom(Piwik::getCurrentUserEmail());
        $mail->addTo($feedbackEmailAddress, 'Piwik Team');
        $mail->setSubject($subject);
        $mail->setBodyText($body);
        @$mail->send();
    }

    private function getEnglishTranslationForFeatureName($featureName)
    {
        $loadedLanguage = Translate::getLanguageLoaded();

        if ($loadedLanguage == 'en') {
            return $featureName;
        }

        $translationKeyForFeature = Translate::findTranslationKeyForTranslation($featureName);

        if (!empty($translationKeyForFeature)) {
            Translate::reloadLanguage('en');

            $featureName = Piwik::translate($translationKeyForFeature);
            Translate::reloadLanguage($loadedLanguage);
            return $featureName;
        }

        return $featureName;
    }
}
