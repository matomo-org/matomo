## Piwik modifications to libs/

In general, bug fixes and improvements are reported upstream.  Until these are
included upstream, we maintain a list of bug fixes and local mods made to
third-party libraries:

 * HTML/Quickform2/
   - in r2626, php 5.1.6 incompatibility
   - in r3040, exception classes don't follow PEAR naming convention
 * pChart2.1.3/
   - the following unused files were removed:
     class/pBarcode39.class.php, class/pBarcode128.class.php,
     class/pBubble.class.php, class/pCache.class.php, class/pIndicator.class.php,
     class/pRadar.class.php, class/pScatter.class.php, class/pSplit.class.php,
     class/pSpring.class.php, class/pStock.class.php, class/pSurface.class.php,
     data/, examples/, fonts/, palettes/
   - The bug #4206 (GD with JIS-mapped Japanese Font Support) was fixed in this
     commit: https://github.com/piwik/piwik/commit/516c13d9b13ca3b908575eb809f7ad9d9397f0e1
     Changed files: class/pImage.class.php class/pDraw.class.php
 * PEAR/, PEAR.php
   - in r2419, add static keyword to isError and raiseError as it throws notices
     in HTML_Quickform2
   - in r2422, is_a() is deprecated for php 5.0 to 5.2.x
 * sparkline/
   - in r1296, remove require_once
   - empty sparklines with floats, off-by-one errors, and locale conflict
 * tcpdf/
   - in 6f945465fe40021d579bc2b4b8876468da69b062 fixed a bug reported in the forums
   - in 566c63a52e31b2b2d3e1a83f8f63e74e8d661b21 fixed another couple bugs with fopen throwing warnings
 * Zend/
   - strip require_once (to support autoloading)
   - in r3694, fix ZF-10888 and ZF-10835
   - ZF-10871 - undefined variables when socket support disabled
