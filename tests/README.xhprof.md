
Piwik is distributed with a copy of XHProf, the PHP profiler created by Facebook. Piwik
also comes with a copy of VisualPHPUnit that has been modified to easily use XHProf. Using
these two tools, you can profile tests and benchmarks.

## Installing XHProf

First, XHProf must be built (this guide assumes you're using a linux variant):

 * 	Navigate to the XHProf extension directory.

		$ cd /path/to/piwik/tests/lib/
		$ wget http://pecl.php.net/get/xhprof
		$ tar -xzvf xhprof
    
 * 	Build XHProf.

		$ phpize
		$ ./configure
		$ make
    
	xhprof.so will now exist in the ./modules directory.
    
 *	Configure PHP to use XHProf. Add the following to your php.ini file:
      
	```
	[xhprof]
	extension=/path/to/piwik/tests/lib/xhprof-0.9.4/extension/modules/xhprof.so
	xhprof.output_dir=/path/to/output/dir
	```
      
	Replace /path/to/output/dir with an existing directory. All your profiles will be
	stored there.

Restart your webserver and you're done. VisualPHPUnit will automatically detect if XHProf
is installed and act accordingly.

## Using XHProf

To use XHProf, first load VisualPHPUnit by pointing your browser to:

http://path/to/piwik/tests/lib/visualphpunit/

Select a test or get ready to run a benchmark. Make sure the 'Profile with XHProf' select
box is set to 'Yes' and click 'Run Tests'.

When the test finishes, a link will be displayed that will let you view the profile that
was created.

## Notes

* Currently, it is not possible to use XHProf with more than one test, so if multiple
      tests are selected, XHProf will not be used.
* XHProf will not delete old profiles, you must do that yourself, though individual
      profiles do not take much space.
