# JavaScript Tests

## Setup
Javascript integration tests require sqlite:
 * ensure this PHP extension is enabled to make sure you run all tests apt-get install php5-sqlite
 * Then please create an empty file `enable_sqlite` in `tests/javascript/enable_sqlite`
 * Re-execute the tests and make sure the "missing sqlite" error message does not display

## Execute

Either open http://piwik.example.com/tests/javascript/ in a browser or execute `phantomjs testrunner.js` on the command line. You can download PhantomJS here: http://phantomjs.org/
