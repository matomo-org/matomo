<?php

Piwik_Query( "DELETE FROM ".  Piwik::prefixTable('user_dashboard') . " 
				WHERE layout LIKE '%.getEvolutionGraph%'" );
