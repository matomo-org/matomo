## Piwik modifications to libs/

In general, bug fixes and improvements are reported upstream.  Until these are
included upstream, we maintain a list of bug fixes and local mods made to
third-party libraries:

 * HTML/Quickform2/
   - in r2626, php 5.1.6 incompatibility
   - in r3040, exception classes don't follow PEAR naming convention
 * sparkline/
   - in r1296, remove require_once
   - empty sparklines with floats, off-by-one errors, and locale conflict
   - in 270821ed (https://github.com/matomo-org/piwik/pull/8410) PHP7 compatibility: renamed the constructors
 * tcpdf/
   - in 6f945465fe40021d579bc2b4b8876468da69b062 fixed a bug reported in the forums
   - in 566c63a52e31b2b2d3e1a83f8f63e74e8d661b21 fixed another couple bugs with fopen throwing warnings
 * Zend/
   - strip require_once (to support autoloading)
   - in r3694, fix ZF-10888 and ZF-10835
   - ZF-10871 - undefined variables when socket support disabled
   - fix #6980 ("Array to string conversion") in `Zend/Session/Exception.php`
   - fix Zend/Validate using deprecated iconv_set_encoding()
   - Make sure sessions work when storing notifications
 * materialize/
   - in materialize.min.css removed the loading of Roboto fonts as the paths do not match and couldn't be loaded. Also
     we do not want to load as many different font styles of Roboto font.