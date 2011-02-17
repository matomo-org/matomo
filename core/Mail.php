<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Class for sending mails, for more information see: 
 *
 * @package Piwik
 * @see Zend_Mail, libs/Zend/Mail.php
 * @link http://framework.zend.com/manual/en/zend.mail.html 
 */
class Piwik_Mail extends Zend_Mail
{
	/**
	 * Default charset utf-8
	 * @param string $charset
	 */
	public function __construct($charset = 'utf-8')
	{
		parent::__construct($charset);
		$this->initSmtpTransport();
	}
	
	public function setFrom($email, $name = null)
	{
		$hostname = Zend_Registry::get('config')->mail->defaultHostnameIfEmpty;
		$piwikHost = Piwik_Url::getCurrentHost($hostname);
		$email = str_replace('{DOMAIN}', $piwikHost, $email);
		parent::setFrom($email, $name);
	}
	
	private function initSmtpTransport()
	{
		$config = Zend_Registry::get('config')->mail;
		if ( empty($config->host) 
			 || $config->transport != 'smtp')
		{
			return;
		}
		$smtpConfig = array();
		if ( !empty($config->type)
			 || !empty($config->username)
			 || !empty($config->password)
			 || !empty($config->encryption)
		)
		{
			$smtpConfig = array(
    						'auth' => $config->type,
            				'username' => $config->username,
            				'password' => $config->password,
            				'ssl' => $config->encryption,
			);
		}
		
		$tr = new Zend_Mail_Transport_Smtp($config->host,$smtpConfig);
		Piwik_Mail::setDefaultTransport($tr);
		ini_set("smtp_port",$config->port);
	}
}
