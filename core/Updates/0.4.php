<?php

Piwik_Query('UPDATE '. Piwik::prefixTable('log_visit') .' SET location_ip=location_ip+CAST(POW(2,32) AS UNSIGNED) WHERE location_ip < 0;');
Piwik_Query('ALTER TABLE '. Piwik::prefixTable('log_visit') .' CHANGE location_ip location_ip BIGINT UNSIGNED;');

Piwik_Query('UPDATE '. Piwik::prefixTable('logger_api_call') .' SET caller_ip=caller_ip+CAST(POW(2,32) AS UNSIGNED) WHERE caller_ip < 0;');
Piwik_Query('ALTER TABLE '. Piwik::prefixTable('logger_api_call') .' CHANGE caller_ip caller_ip BIGINT UNSIGNED;');

Piwik_Query( "ALTER TABLE ". Piwik::prefixTable('log_visit') . " DROP config_java" );
