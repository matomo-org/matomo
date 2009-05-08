<?php

Piwik_Query( "ALTER TABLE ". Piwik::prefixTable('log_visit') . " DROP config_quicktime" );
