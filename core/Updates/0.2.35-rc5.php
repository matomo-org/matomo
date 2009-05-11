<?php

Piwik_Query( "ALTER TABLE ".  Piwik::prefixTable('user_dashboard') . " CHANGE `layout` `layout` TEXT NOT NULL  " );
Piwik_Query( "ALTER TABLE ". Piwik::prefixTable('log_visit') . " DROP config_quicktime" );
