<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
	 * Public constructor, default charset utf-8
	 *
	 * @param string $charset
	 */
	public function __construct($charset = 'utf-8')
	{
		parent::__construct($charset);
		$this->setTransport();
	}
	
	
	public function setTransport()
	{
		try
		{
			$config = Zend_Registry::get('config')->mail;
			if ( !empty($config->host) 
				 && !empty($config->port) 
				 && strcmp($config->transport,"smtp") ==0
			)
			{
				if ( !empty($config->auth->type)
					 || !empty($config->auth->username)
					 || !empty($config->auth->password)
				)
				{
					$config_param = array('auth' => $config->auth->type,
					'username' => $config->auth->username,
					'password' => $config->auth->password);
				}
				
				$smtp_address = $config->host;
				$smtp_port = $config->port;
				if(isset($config_param))
				{
					$tr = new Zend_Mail_Transport_Smtp("$smtp_address",$config_param);
				}
				else
				{
					$tr = new Zend_Mail_Transport_Smtp("$smtp_address");
				}			
				Piwik_Mail::setDefaultTransport($tr);
				ini_set("smtp_port","$smtp_port");
			}
		}
		catch(Exception $e)
		{
			throw new Exception("Configuration SMTP error");
		}
		
	}
}
