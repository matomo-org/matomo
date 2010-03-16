<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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
		$view->nonce = Piwik::getNonce('Piwik_Feedback.sendFeedback', 3600);
		echo $view->render();
	}

	/**
	 * send email to Piwik team and display nice thanks
	 */
	function sendFeedback()
	{
		$email = Piwik_Common::getRequestVar('email', '', 'string');
		$body = Piwik_Common::getRequestVar('body', '', 'string');
		$category = Piwik_Common::getRequestVar('category', '', 'string');
		$nonce = Piwik_Common::getRequestVar('nonce', '', 'string');

		$view = Piwik_View::factory('sent');
		try
		{
			$minimumBodyLength = 35;
			if(strlen($body) < $minimumBodyLength)
			{
				throw new Exception(Piwik_TranslateException('Feedback_ExceptionBodyLength', array($minimumBodyLength)));
			}
			if(!Piwik::isValidEmailString($email))
			{
				throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidEmail'));
			}
			if(strpos($body, 'http://') !== false)
			{
				throw new Exception(Piwik_TranslateException('Feedback_ExceptionNoUrls'));
			}
			if(!Piwik::verifyNonce('Piwik_Feedback.sendFeedback', $nonce))
			{
				throw new Exception(Piwik_TranslateException('General_ExceptionNonceMismatch'));
			}

			$mail = new Piwik_Mail();
			$mail->setFrom(Piwik_Common::unsanitizeInputValue($email));
			$mail->addTo('hello@piwik.org', 'Piwik Team');
			$mail->setSubject('[ Feedback form - Piwik ] ' . $category);
			$mail->setBodyText(Piwik_Common::unsanitizeInputValue($body) . "\n"
				. 'Piwik ' . Piwik_Version::VERSION . "\n"
				. 'IP: ' . Piwik_Common::getIpString() . "\n"
				. 'URL: ' . Piwik_Url::getReferer() . "\n");
			@$mail->send();
		}
		catch(Exception $e)
		{
			$view->ErrorString = $e->getMessage();
			$view->message = $body;
		}

		echo $view->render();
	}
}
