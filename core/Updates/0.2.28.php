<?php

Piwik_Query( "ALTER TABLE `".Piwik::prefixTable('log_visit')."` 
			ADD `visit_goal_converted` VARCHAR( 1 ) NOT NULL AFTER `visit_total_time` ;");

//TODO
// alter all archive_*
// KEY `index_all` (`idsite`,`date1`,`date2`,`name`,`ts_archived`)

