<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Feedback
 */

/**
 *
 * @package Piwik_Feedback
 */
class Piwik_Feedback_Controller extends Piwik_Controller
{
    function index()
    {
        $view = Piwik_View::factory('index');
        $view->nonce = Piwik_Nonce::getNonce('Piwik_Feedback.sendFeedback', 3600);
        echo $view->render();
    }

    /**
     * send email to Piwik team and display nice thanks
     * @throws Exception
     */
    function sendFeedback()
    {
        $email = Piwik_Common::getRequestVar('email', '', 'string');
        $body = Piwik_Common::getRequestVar('body', '', 'string');
        $category = Piwik_Common::getRequestVar('category', '', 'string');
        $nonce = Piwik_Common::getRequestVar('nonce', '', 'string');

        $view = Piwik_View::factory('sent');
        $view->feedbackEmailAddress = Piwik_Config::getInstance()->General['feedback_email_address'];
        try {
            $minimumBodyLength = 40;
            if (strlen($body) < $minimumBodyLength
                // Avoid those really annoying automated security test emails
                || strpos($email, 'probe@') !== false
                || strpos($body, '&lt;probe') !== false
            ) {
                throw new Exception(Piwik_TranslateException('Feedback_ExceptionBodyLength', array($minimumBodyLength)));
            }
            if (!Piwik::isValidEmailString($email)) {
                throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidEmail'));
            }
            if (preg_match('/https?:/i', $body)) {
                throw new Exception(Piwik_TranslateException('Feedback_ExceptionNoUrls'));
            }
            if (!Piwik_Nonce::verifyNonce('Piwik_Feedback.sendFeedback', $nonce)) {
                throw new Exception(Piwik_TranslateException('General_ExceptionNonceMismatch'));
            }
            Piwik_Nonce::discardNonce('Piwik_Feedback.sendFeedback');

            $mail = new Piwik_Mail();
            $mail->setFrom(Piwik_Common::unsanitizeInputValue($email));
            $mail->addTo($view->feedbackEmailAddress, 'Piwik Team');
            $mail->setSubject('[ Feedback form - Piwik ] ' . $category);
            $mail->setBodyText(Piwik_Common::unsanitizeInputValue($body) . "\n"
                . 'Piwik ' . Piwik_Version::VERSION . "\n"
                . 'IP: ' . Piwik_IP::getIpFromHeader() . "\n"
                . 'URL: ' . Piwik_Url::getReferer() . "\n");
            @$mail->send();
        } catch (Exception $e) {
            $view->ErrorString = $e->getMessage();
            $view->message = $body;
        }

        echo $view->render();
    }
}
