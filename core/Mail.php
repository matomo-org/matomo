<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

require_once "Zend/Mail.php";

/**
 * Class for sending mails, for more information see: 
 * http://framework.zend.com/manual/en/zend.mail.html 
 *
 * @package Piwik
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
	}
}
