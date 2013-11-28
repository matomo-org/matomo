
## Starting VisualPHPUnit

To load VisualPHPUnit point your browser to http://path/to/piwik/tests/lib/visualphpunit/.

VisualPHPUnit will already be configured for use with Piwik. 

Troubleshooting

 * If at this URL you see a listing of files instead of seeing VisualPHPUnit, 
   enable mod_rewrite apache module, and make sure your vhost in apache 
   configuration has "AllowOverride all" so that .htaccess are loaded.
 
 * If you get an error such as "Warning: require_once(PHPUnit/Autoload.php)" it is because the PEAR path 
   is not set in your php.ini. Edit in php.ini the value include_path to include the path to your
   PEAR setup, and restart Apache.

## Running tests

Once VisualPHPUnit is loaded, you can run tests by selecting files or whole directories in the
file selector on the left of the screen, then clicking the 'Run tests' button. To select
files/directories ctrl+click them.

To run all Piwik tests, ctrl+click the 'Core', 'Integration' and 'Plugins' directory, then
click the 'Run tests' button.

## Running tests by URL

If you're in need of a URL that will not only load VisualPHPUnit but run one or more tests,
you may add the list of tests to run as the hash of the URL. For example,

http://path/to/piwik/tests/lib/visualphpunit/#/Core/DataTableTest.php:/Core/CookieTest.php

will load VisualPHPUnit and immediately run the tests in DataTableTest.php and CookieTest.php.
Currently, this feature will not allow you to specify directories with tests to run.

## Using phpunit.xml

By default, VisualPHPUnit lets you run tests by selecting individual test files or directories
and clicking the 'Run Tests' button. If you want to use a phpunit.xml file, either your own or the
one that comes with Piwik, you'll need to modify VisualPHPUnit's configuration. Edit the file
located at

/path/to/piwik/tests/lib/visualphpunit/app/config/bootstrap.php

and set the 'xml_configuration_file' config option.

Please note that when a phpunit.xml file is supplied in the configuration, VisualPHPUnit will
always run tests with it, regardless of what files you select. You can override this behavior
in the web UI by selecting 'No' in the 'Use XML Config' input.

## Debugging invalid responses

Sometimes, VisualPHPUnit will run PHPUnit tests and get a response it can't read. These problems
are usually caused by an unmatched ob_start() call in the code somewhere, or by the program
prematurely exiting.

To find the cause of such issues, it can help to determine what code can & can't affect the
output VisualPHPUnit sees. Code that can affect what VisualPHPUnit sees is before the bug in
question, and code that can't is after it.
