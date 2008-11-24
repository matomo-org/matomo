<?php

Piwik_Query('CREATE INDEX index_type_name 
				ON '. Piwik::prefixTable('log_action') . ' (type, name(15))');

Piwik_Query('DROP INDEX index_idsite ON '. Piwik::prefixTable('log_visit'));
Piwik_Query('DROP INDEX index_visit_server_date ON '. Piwik::prefixTable('log_visit'));

Piwik_Query('CREATE INDEX index_idsite_date 
				ON '. Piwik::prefixTable('log_visit') . ' (idsite, visit_server_date)');

