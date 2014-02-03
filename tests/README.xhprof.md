
Piwik is distributed with a copy of XHProf, the PHP profiler created by Facebook.

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

Restart your webserver and you're done.

XHProf will now store and process profiles of all running PHP processes. Access the reports via the XHPprof interface.

