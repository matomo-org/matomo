As a developer it may be useful to generate test data. Follow these steps:

1. 	Install Piwik
2. 	Create a site with URL http://piwik.org/
3. 	Create a Goal eg. URL Contains "blog"
4. 	Import data from an anonimized test log file in piwik/tests/resources/ directory. Run the following command:

		$ python /home/piwik/misc/log-analytics/import_logs.py --url=http://localhost/path/ /path/to/piwik/tests/resources/access.log-dev-anon-9-days-nov-2012.log.bz2 --idsite=1 --enable-http-errors --enable-http-redirects --enable-static --enable-bots

	This will import 9 days worth of data from Nov 20th-Nov 29th 2012.

5.	You can then archive the reports with:

        $ php5 /home/piwik/misc/cron/archive.php --url=http://localhost/path/

You should now have some interesting data to test with in November 2012!
