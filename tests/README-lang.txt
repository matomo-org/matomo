=========================================
== Committing Latest Translation Files ==
=========================================

This is the procedure to take the latest Piwik translation files,
from oTrance translation platform,
cleanup all files,
and import these new translation files into our code repository.

HOW TO:

 # Build the download package in oTrance and download and extra to the local Piwik dev lang/ folder

 # Run the integration tests that will clean up the files:
	cd /home/www/piwik/tests/PHPUnit/
	rm ../../tmp/*.php
    phpunit Plugins/LanguagesManagerTest.php

 # Copy the cleaned up files over, then delete some currently inactive translations
    cp ../../tmp/*.php ../../lang/
	rm ../../lang/am.php
	rm ../../lang/az.php
	rm ../../lang/bn.php
	rm ../../lang/bs.php
	rm ../../lang/eo.php
	rm ../../lang/ms.php
	rm ../../lang/ur.php
	dos2unix ../../lang/*

 # lang/ directory should now be clean, and tests should pass:
    phpunit Plugins/LanguagesManagerTest.php

 # Commit referencing #3430
