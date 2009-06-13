<?php

Piwik_Query('ALTER TABLE '. Piwik::prefixTable('log_conversion') .' 
				CHANGE idlink_va idlink_va int(11)  default NULL;');
Piwik_Query('ALTER TABLE '. Piwik::prefixTable('log_conversion') .' 
				CHANGE idaction idaction int(11)  default NULL;');
