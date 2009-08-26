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
		echo $view->render();
	}

	/**
	 * send email to Piwik team and display nice thanks
	 */
	function sendFeedback()
	{
		$body = Piwik_Common::getRequestVar('body', '', 'string');
		$email = Piwik_Common::getRequestVar('email', '', 'string');

		$view = Piwik_View::factory('sent');
		try 
		{
			$minimumBodyLength = 35;
			if(strlen($body) < $minimumBodyLength)
			{
				throw new Exception(sprintf("Message must be at least %s characters long.", $minimumBodyLength));
			}
			if(!Piwik::isValidEmailString($email))
			{
				throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidEmail'));
			}
			if(strpos($body, 'http://') !== false)
			{
				throw new Exception("The message cannot contain a URL, to avoid spams messages.");
			}
			
			$mail = new Piwik_Mail();
			$mail->setFrom($email);
			$mail->addTo('hello@piwik.org','Piwik Team');
			$mail->setSubject('[ Feedback form - Piwik ]');
			$mail->setBodyText($body);
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
