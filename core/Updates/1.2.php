<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_1_2 extends Piwik_Updates
{
	static function getSql($schema = 'Myisam')
	{
		return array(
		    'ALTER TABLE `'. Piwik_Common::prefixTable('log_visit') .'` 
			    DROP `visit_server_date`,
			    DROP INDEX `index_idsite_date_config`,
		    	ADD `visit_entry_idaction_name` INT UNSIGNED NOT NULL AFTER `visit_entry_idaction_url`,
			    ADD `visit_exit_idaction_name` INT UNSIGNED NOT NULL AFTER `visit_exit_idaction_url`,
			    CHANGE `visit_exit_idaction_url` `visit_exit_idaction_url` INT UNSIGNED NOT NULL, 
			    CHANGE `visit_entry_idaction_url` `visit_entry_idaction_url` INT UNSIGNED NOT NULL
			   ' => false,
		    'ALTER TABLE `'. Piwik_Common::prefixTable('log_link_visit_action') .'` 
				ADD `idsite` INT( 10 ) UNSIGNED NOT NULL AFTER `idlink_va` , 
				ADD `server_time` DATETIME NOT NULL AFTER `idsite`,
				ADD `visitor_idcookie` char(32) NOT NULL AFTER `idsite`,
				ADD `idaction_name_ref` INT UNSIGNED NOT NULL AFTER `idaction_name`,
				ADD INDEX `index_idsite_servertime` ( `idsite` , `server_time` )
			   ' => false,
			// Backfill logs as best as we can
			'UPDATE '.Piwik_Common::prefixTable('log_link_visit_action') .' as action, 
				  	'.Piwik_Common::prefixTable('log_visit') .'  as visit
                SET action.idsite = visit.idsite, 
                	action.server_time = visit.visit_last_action_time, 
                	action.visitor_idcookie = visit.visitor_idcookie 
                WHERE action.idvisit=visit.idvisit
                ' => false, 
			'ALTER TABLE `'. Piwik_Common::prefixTable('option') .'` ADD INDEX ( `autoload` ) ' => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}

