## Legal notice

See the [LEGALNOTICE file](https://github.com/matomo-org/matomo/blob/4.x-dev/LEGALNOTICE).

## Matomo modifications to libs/

In general, bug fixes and improvements are reported upstream.  Until these are
included upstream, we maintain a list of bug fixes and local mods made to
third-party libraries:

 * HTML/Quickform2/
   - in r2626, php 5.1.6 incompatibility
   - in r3040, exception classes don't follow PEAR naming convention
 * Zend/
   - strip require_once (to support autoloading)
   - fix #6980 ("Array to string conversion") in `Zend/Session/Exception.php`
   - various adjustments to remove unused parts of Zend Framework
   - Make sure sessions work when storing notifications
 * materialize/
   - in materialize.min.css removed the loading of Roboto fonts as the paths do not match and couldn't be loaded. Also
     we do not want to load as many different font styles of Roboto font.
