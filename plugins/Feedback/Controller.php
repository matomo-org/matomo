<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Feedback
 */
namespace Piwik\Plugins\Feedback;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Mail;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;

/**
 *
 * @package Feedback
 */
class Controller extends \Piwik\Plugin\Controller
{
    function index()
    {
        $view = new View('@Feedback/index');
        $view->nonce = Nonce::getNonce('Feedback.sendFeedback', 3600);
        return $view->render();
    }

    /**
     * send email to Piwik team and display nice thanks
     * @throws Exception
     */
    function sendFeedback()
    {
        $email = Common::getRequestVar('email', '', 'string');
        $body = Common::getRequestVar('body', '', 'string');
        $category = Common::getRequestVar('category', '', 'string');
        $nonce = Common::getRequestVar('nonce', '', 'string');

        $view = new View('@Feedback/sendFeedback');
        $view->feedbackEmailAddress = Config::getInstance()->General['feedback_email_address'];
        try {
            $minimumBodyLength = 40;
            if (strlen($body) < $minimumBodyLength
                // Avoid those really annoying automated security test emails
                || strpos($email, 'probe@') !== false
                || strpos($body, '&lt;probe') !== false
            ) {
                throw new Exception(Piwik::translate('Feedback_ExceptionBodyLength', array($minimumBodyLength)));
            }
            if (!Piwik::isValidEmailString($email)) {
                throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidEmail'));
            }
            if (preg_match('/https?:/i', $body)) {
                throw new Exception(Piwik::translate('Feedback_ExceptionNoUrls'));
            }
            if (!Nonce::verifyNonce('Feedback.sendFeedback', $nonce)) {
                throw new Exception(Piwik::translate('General_ExceptionNonceMismatch'));
            }
            Nonce::discardNonce('Feedback.sendFeedback');

            $mail = new Mail();
            $mail->setFrom(Common::unsanitizeInputValue($email));
            $mail->addTo($view->feedbackEmailAddress, 'Piwik Team');
            $mail->setSubject('[ Feedback form - Piwik ] ' . $category);
            $mail->setBodyText(Common::unsanitizeInputValue($body) . "\n"
                . 'Piwik ' . Version::VERSION . "\n"
                . 'IP: ' . IP::getIpFromHeader() . "\n"
                . 'URL: ' . Url::getReferrer() . "\n");
            @$mail->send();
        } catch (Exception $e) {
            $view->errorString = $e->getMessage();
            $view->message = $body;
        }

        return $view->render();
    }
}
