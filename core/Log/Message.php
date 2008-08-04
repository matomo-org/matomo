<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Message.php 492 2008-05-23 01:08:12Z matt $
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_Message
 */

/**
 * Class used to log a standard message event.
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_Message
 */
class Piwik_Log_Message extends Piwik_Log
{
	const ID = 'logger_message';
	function __construct()
	{
		$logToFileFilename = self::ID;
		$logToDatabaseTableName = self::ID;
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_Message_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
	}
	
	public function log( $message )
	{
		$event = array();
		$event['message'] = $message;
		
		parent::log($event);
	}
}


/**
 * Format a standard message event to be displayed on the screen.
 * The message can be a PHP array or a string.
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_Message
 */
class Piwik_Log_Formatter_Message_ScreenFormatter extends Piwik_Log_Formatter_ScreenFormatter 
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
    	if(is_array($event['message']))
    	{
    		$message = "<pre>".var_export($event['message'], true)."</pre>";
    	}
    	else
    	{
    		$message = $event['message'];
    	}
    	
    	return parent::format($message);
    }
}

