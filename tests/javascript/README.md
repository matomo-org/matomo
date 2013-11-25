# JavaScript Tests

## Setup
Javascript integration tests require sqlite:
 * ensure this PHP extension is enabled to make sure you run all tests apt-get install php5-sqlite
 * Then please create an empty file enable_sqlite in tests/javascript/enable_sqlite
 * Re-execute this page and make sure this popup does not display

## Execute

Either open http://piwik.example.com/tests/javascript/ or execute `phantomjs testrunner.js`
