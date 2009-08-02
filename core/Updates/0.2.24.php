<?php

class Piwik_Updates_0_2_24 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'CREATE INDEX index_type_name
				ON '. Piwik::prefixTable('log_action') .' (type, name(15))' => false,
			'CREATE INDEX index_idsite_date
				ON '. Piwik::prefixTable('log_visit') .' (idsite, visit_server_date)' => false,
			'DROP INDEX index_idsite ON '. Piwik::prefixTable('log_visit') => false,
			'DROP INDEX index_visit_server_date ON '. Piwik::prefixTable('log_visit') => false,
		));
	}
}
