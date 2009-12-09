<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_5 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'ALTER TABLE ' . Piwik::prefixTable('log_action'). ' ADD COLUMN `hash` INTEGER(10) UNSIGNED NOT NULL AFTER `name`;' => false,
			'ALTER TABLE '. Piwik::prefixTable('log_visit') .' CHANGE visit_exit_idaction visit_exit_idaction_url INTEGER(11) NOT NULL;' => false,
			'ALTER TABLE '. Piwik::prefixTable('log_visit') .' CHANGE visit_entry_idaction visit_entry_idaction_url INTEGER(11) NOT NULL;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_link_visit_action'). ' CHANGE `idaction_ref` `idaction_url_ref` INTEGER(10) UNSIGNED NOT NULL;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_link_visit_action'). ' CHANGE `idaction` `idaction_url` INTEGER(10) UNSIGNED NOT NULL;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_link_visit_action'). ' ADD COLUMN `idaction_name` INTEGER(10) UNSIGNED AFTER `idaction_url_ref`;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_conversion'). ' CHANGE `idaction` `idaction_url` INTEGER(11) UNSIGNED NOT NULL;' => false,
			'UPDATE '.  Piwik::prefixTable('log_action'). ' SET `hash` = CRC32(name);' => false,
			'CREATE INDEX index_type_hash ON '. Piwik::prefixTable('log_action') .' (type, hash);' => false,
			'DROP INDEX index_type_name ON '. Piwik::prefixTable('log_action') .';' => false,
		));
	}
}
