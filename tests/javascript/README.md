# JavaScript Tests

## Setup
Javascript integration tests require an installed Piwik and ensure the `[database_tests]` section in `piwik/config/config.ini.php` is set up correctly, i.e. with th correct password to prevent the following error: `SQLSTATE[28000] [1045] Access denied for user 'root'@'localhost' (using password: NO)`

The tests will create a database named `tracker_tests` and store several tracking requests in it.

## Execute

Either open http://piwik.example.com/tests/javascript/ in a browser or execute `phantomjs testrunner.js` on the command line. You can download PhantomJS here: http://phantomjs.org/
