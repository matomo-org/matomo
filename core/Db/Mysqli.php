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
 * @package Piwik
 */
class Piwik_Db_Mysqli extends Zend_Db_Adapter_Mysqli
{
	/**
	 * Reset the configuration variables in this adapter.
	 */
	public function resetConfig()
	{
		$this->_config = array();
	}
}
