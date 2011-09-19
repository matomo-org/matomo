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
		
		// If known Piwik URL, use it instead of "localhost"
		$piwikUrl = Piwik::getPiwikUrl();
		$url = parse_url($piwikUrl);
		if(isset($url['host'])
			&& $url['host'] != 'localhost'
			&& $url['host'] != '127.0.0.1')
		{
			$piwikHost = $url['host'];
		}
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
		if (!empty($config->type))
			$smtpConfig['auth'] = strtolower($config->type);
		if (!empty($config->username))
			$smtpConfig['username'] = $config->username;
		if (!empty($config->password))
			$smtpConfig['password'] = $config->password;
		if (!empty($config->encryption))
			$smtpConfig['ssl'] = $config->encryption;
		
		$tr = new Zend_Mail_Transport_Smtp($config->host, $smtpConfig);
		Piwik_Mail::setDefaultTransport($tr);
		ini_set("smtp_port", $config->port);
	}
}
