## Introduction

The js/ folder contains:

* index.php - a servlet described below
* piwik.js  - the uncompressed piwik.js source for you to study or reference
* README.md - this documentation file

### Why Use "js/index.php"?

* js/index.php (or implicitly as "js/") can be used to serve up the minified
  piwik.js

    * it supports conditional-GET and Last-Modified, so piwik.js can be cached
      by the browser
    * it supports deflate/gzip compression if your web server (e.g., Apache
      without mod_deflate or mod_gzip), shrinking the data transfer to 8K

* js/index.php (or implicitly as "js/") can also act as a proxy to matomo.php

* If you are concerned about the impact of browser-based privacy filters which
  attempt to block tracking, you can change your tracking code to use "js/"
  instead of "piwik.js" and "matomo.php", respectively.

  Note that in order for [Page Overlay](https://matomo.org/docs/page-overlay/) to work, the Piwik tracker method `setAPIUrl()` needs to be called with its parameter pointing to the root directory of Piwik. E.g.:

  ```js
  _paq.push(['setAPIUrl', u]);

  ```

## Deployment

* piwik.js is minified using YUICompressor 2.4.8.
  To install YUICompressor run:
 
  ```bash
  $ cd /path/to/piwik/js/
  $ wget https://github.com/yui/yuicompressor/releases/download/v2.4.8/yuicompressor-2.4.8.zip
  $ unzip yuicompressor-2.4.8.zip
  ```

  To compress the code containing the evil "eval", run:

  ```bash
  $ cd /path/to/piwik/js/
  $ sed '/<DEBUG>/,/<\/DEBUG>/d' < piwik.js | sed 's/eval/replacedEvilString/' | java -jar yuicompressor-2.4.8.jar --type js --line-break 1000 | sed 's/replacedEvilString/eval/' | sed 's/^[/][*]/\/*!/' > piwik.min.js && cp piwik.min.js ../piwik.js && cp piwik.min.js ../matomo.js
  ```

  This will generate the minify /path/to/piwik/js/piwik.min.js and copy it to
  /path/to/piwik/piwik.js. Both "js/piwik.min.js" and "piwik.js" need to be committed.
  
  We recommend to execute this command under Linux. It has not been tested with Windows and 
  MacOS might add a trailing newline which fails tests.
VisitorGeolocatorTest
* In a production environment, the tests/javascript folder is not used and can
  be removed (if present).

* We use /*! to include Piwik's license header in the minified source. Read
  Stallman's "The JavaScript Trap" for more information.

* Information about the current version number you have installed can be found under [What version of Piwik do I have?](https://matomo.org/faq/how-to-update/faq_8/). 
