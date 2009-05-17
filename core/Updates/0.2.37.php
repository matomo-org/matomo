<?php

Piwik_Query( "DELETE FROM ".  Piwik::prefixTable('user_dashboard') . " 
				WHERE layout LIKE '%.getLastVisitsGraph%' 
					OR layout LIKE '%.getLastVisitsReturningGraph%'" );
