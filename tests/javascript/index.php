<?php
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>matomo.js: Unit Tests</title>
<?php

$cacheBuster = md5(uniqid(mt_rand(), true));


// Note: when you want to debug the piwik.js during the tests, you need to set a cache buster that is always the same
// between requests so the browser knows it is the same file and know where to breakpoint.
// $cacheBuster= 'nocb'; // uncomment to debug

$root = dirname(__FILE__) . '/../..';
$testPluginPath = '*';
if (!empty($_GET['plugin'])
    && ctype_alnum($_GET['plugin'])
    && is_dir($root . '/plugins/' . $_GET['plugin'])) {
    $testPluginPath = $_GET['plugin'];
}

try {
    @ob_start();
    $mysql = include_once $root . "/tests/PHPUnit/bootstrap.php";
    @ob_end_clean();
} catch (Exception $e) {
    echo 'alert("ERROR, not all tests are running! --> ' . $e->getMessage() .  '")';
    $mysql = false;
}

use \Piwik\Plugins\CustomJsTracker\TrackerUpdater;
use \Piwik\Plugins\CustomJsTracker\TrackingCode\JsTestPluginTrackerFiles;

$targetFileName = '/tests/resources/matomo.test.js';
$sourceFile = PIWIK_DOCUMENT_ROOT . TrackerUpdater::DEVELOPMENT_PIWIK_JS;
$targetFile = PIWIK_DOCUMENT_ROOT . $targetFileName;

$updater = new TrackerUpdater($sourceFile, $targetFile);
$updater->setTrackerFiles(new JsTestPluginTrackerFiles());
$updater->checkWillSucceed();
$updater->update();

if(file_exists("stub.tpl")) {
    echo file_get_contents("stub.tpl");
}
?>
 <script type="text/javascript">
function getToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getContentToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getHeartbeatToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getCookieConsentToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getConsentToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getCampaignParamToken() {
  return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getOptInToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getAlwaysUseSendBeaconToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
function getBrowserFeatureToken() {
    return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
<?php

if ($mysql) {
  echo '
var _paq = _paq || [];

function testCallingTrackPageViewBeforeSetTrackerUrlWorks() {
    _paq.push(["setCustomData", { "token" : getToken() }]);
    _paq.push(["trackPageView", "Asynchronous Tracker ONE"]);
    _paq.push(["setSiteId", 1]);
    _paq.push(["setTrackerUrl", "matomo.php"]);
}

function testTrackPageViewAsync() {
    _paq.push(["trackPageView", "Asynchronous tracking TWO"]);
}

testCallingTrackPageViewBeforeSetTrackerUrlWorks();
testTrackPageViewAsync();

';
}
?>
 </script>
 <script src="../lib/q-1.4.1/q.js" type="text/javascript"></script>
 <script src="../..<?php echo $targetFileName ?>?rand=<?php echo $cacheBuster ?>" type="text/javascript"></script>
 <script src="../../plugins/Overlay/client/urlnormalizer.js" type="text/javascript"></script>
 <script src="matomotest.js" type="text/javascript"></script>
 <link rel="stylesheet" href="assets/qunit.css" type="text/css" media="screen" />
 <link rel="stylesheet" href="jash/Jash.css" type="text/css" media="screen" />

    <?php
    include_once $root . '/core/Filesystem.php';
    $files = \Piwik\Filesystem::globr($root . '/plugins/'.$testPluginPath.'/tests/javascript', 'head.php');
    foreach ($files as $file) {
        include_once $file;
    }
    if ($testPluginPath !== '*') {
        // CI would always include tag manager
        $files = \Piwik\Filesystem::globr($root . '/plugins/TagManager/tests/javascript', 'head.php');
        foreach ($files as $file) {
            include_once $file;
        }
    }
    ?>
<style>
    .assertSize {
        height: 1px;
        width: 1px;
    }
    .hideY {
        overflow-x: hidden !important;
    }
    #contenttest {
        position: absolute;
        left: 0px;
        right: 0px;
        top: 0px;
        bottom: 0px;
    }
</style>
 <script src="../../node_modules/jquery/dist/jquery.min.js" type="text/javascript"></script>
 <script src="assets/qunit.js" type="text/javascript"></script>

 <script type="text/javascript">
 QUnit.config.reorder = false;
 QUnit.config.altertitle = false;
function _e(id){
    if (document.getElementById)
        return document.getElementById(id);
    if (document.layers)
        return document[id];
    if (document.all)
        return document.all[id];
}
 function isIE () {
     var myNav = navigator.userAgent.toLowerCase();
     return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
 }

function _s(selector) { // select node within content test scope
 var $nodes = $('#contenttest ' + selector);
 if ($nodes.length) {
     return $nodes[0];
 } else {
     ok(false, 'selector not found but should: #contenttest ' + selector);
 }
}

function makeXhr()
{
    var xhr = window.XMLHttpRequest ? new window.XMLHttpRequest() :
        window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") :
            null;
    return xhr;
}

 // Polyfill for IndexOf for IE6-IE8
 function indexOfArray(theArray, searchElement)
 {
     if (theArray && theArray.indexOf) {
         return theArray.indexOf(searchElement);
     }

     // 1. Let O be the result of calling ToObject passing
     //    the this value as the argument.
     if (!isDefined(theArray) || theArray === null) {
         return -1;
     }

     if (!theArray.length) {
         return -1;
     }

     var len = theArray.length;

     if (len === 0) {
         return -1;
     }

     var k = 0;

     // 9. Repeat, while k < len
     while (k < len) {
         // a. Let Pk be ToString(k).
         //   This is implicit for LHS operands of the in operator
         // b. Let kPresent be the result of calling the
         //    HasProperty internal method of O with argument Pk.
         //   This step can be combined with c
         // c. If kPresent is true, then
         //    i.  Let elementK be the result of calling the Get
         //        internal method of O with the argument ToString(k).
         //   ii.  Let same be the result of applying the
         //        Strict Equality Comparison Algorithm to
         //        searchElement and elementK.
         //  iii.  If same is true, return k.
         if (theArray[k] === searchElement) {
             return k;
         }
         k++;
     }
     return -1;
 }
 function getOrigin()
 {
     if (window.location.origin) {
         return window.location.origin;
     }
     return window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
 }

 function encodeWrapper(url)
 {
     return window.encodeURIComponent(url);
 }

 function toEncodedAbsoluteUrl(url)
 {
     return encodeWrapper(toAbsoluteUrl(url));
 }

 function toAbsoluteUrl(url)
 {
     var origin = getOrigin();
     var path   = toAbsolutePath(url);

     var absoluteUrl = origin + path;

     return absoluteUrl;
 }

 function toEncodedAbsolutePath(url)
 {
     return encodeWrapper(toAbsolutePath(url));
 }

 function toAbsolutePath(url)
 {
     var path = '';

     if (0 !== url.indexOf('/')) {
         path += location.pathname;
         if (!path.match(/\/$/)) {
             path += '/';
         }
     }

     var absolutePath = path + url;

     return absolutePath;
 }

function loadJash() {
    var jashDiv = _e('jashDiv');

    jashDiv.innerHTML = '';
    document.body.appendChild(document.createElement('script')).src='jash/Jash.js';
}

 function scrollToTop()
 {
     window.scroll(0, 0);
 }

function triggerEvent(element, type, buttonNumber) {
 if ( document.createEvent ) {
     if ('undefined' === (typeof buttonNumber)) {
         buttonNumber = 0;
     }

     var event = document.createEvent( "MouseEvents" ),
         docView = element == window ? element : element.ownerDocument.defaultView;
     event.initMouseEvent(type, true, true, docView,
         0, 0, 0, 0, 0, false, false, false, false, buttonNumber, null);
     element.dispatchEvent( event );
 } else if ( element.fireEvent ) {
     element.fireEvent( "on" + type );
 }
}

 function wait(msecs)
 {
     var start = new Date().getTime();
     var cur = start
     while(cur - start < msecs)
     {
         cur = new Date().getTime();
     }
 }

 function fetchTrackedRequests(token, parse)
 {
     var xhr = makeXhr();

     xhr.open("GET", "matomo.php?requests=" + token, false);
     xhr.send(null);

     var response = xhr.responseText;
     if (parse) {
         var results = [];
         $(response).filter('span').each(function (i) {
             if (i != 0) {
                 results.push($(this).text());
             }
         });
         return results;
     }

     return response;
 }

 function dropCookie(cookieName, path, domain) {
    var expiryDate = new Date();

    expiryDate.setTime(expiryDate.getTime() - 3600);
    document.cookie = cookieName + '=;expires=' + expiryDate.toGMTString() +
        ';path=' + (path ? path : '') +
        (domain ? ';domain=' + domain : '');
    document.cookie = cookieName + ';expires=' + expiryDate.toGMTString() +
        ';path=' + (path ? path : '') +
        (domain ? ';domain=' + domain : '');
}

function deleteCookies() {
    // aggressively delete cookies

    // 1. get all cookies
    var
        cookies = (document.cookie).split(';'),
        aCookie,
        cookiePattern = new RegExp('^ *([^=]*)='),
        cookieMatch,
        cookieName,
        domain,
        domains = [],
        path,
        paths = [];

    cookies.push( '=' );

    // 2. construct list of domains
    domain = document.domain;
    if (domain.substring(0, 1) !== '.') {
        domain = '.' + domain;
    }
    domains.push( domain );
    while ((i = domain.indexOf('.')) >= 0) {
        domain = domain.substring(i+1);
        domains.push( domain );
    }
    domains.push( '' );
    domains.push( null );

    // 3. construct list of paths
    path = window.location.pathname;
    while ((i = path.lastIndexOf('/')) >= 0) {
        paths.push(path + '/');
        paths.push(path);
        path = path.substring(0, i);
    }
    paths.push( '/' );
    paths.push( '' );
    paths.push( null );

    // 4. iterate through cookies
    for (aCookie in cookies) {
        if (Object.prototype.hasOwnProperty.call(cookies, aCookie)) {

            // 5. extract cookie name
            cookieMatch = cookiePattern.exec(cookies[aCookie]);
            if (cookieMatch) {
                cookieName = cookieMatch[1];

                // 6. iterate through domains
                for (i = 0; i < domains.length; i++) {

                    // 7. iterate through paths
                    for (j = 0; j < paths.length; j++) {

                        // 8. drop cookie
                        dropCookie(cookieName, paths[j], domains[i]);
                    }
                }
            }
        }
    }
}

function mockDateMethods() {
    var oldGetTime = Date.prototype.getTime;
    Date.prototype.getTime = function getTime() {
        if (mockNowValue) {
            return mockNowValue;
        }
        return oldGetTime.apply(this, [].slice.call(arguments));
    };
}

var contentTestHtml = {};

 function removeContentTrackingFixture()
 {
     $('#contenttest').remove();
 }

function setupContentTrackingFixture(name, targetNode) {
    var url = 'content-fixtures/' + name + '.html'

    if (!contentTestHtml[name]) {
        $.ajax({
            url: url,
            success: function( content ) { contentTestHtml[name] = content; },
            dataType: 'html',
            async: false
        });
    }

    var newNode = $('<div id="contenttest">' + contentTestHtml[name] + '</div>');

    removeContentTrackingFixture();

    if (targetNode) {
        $(targetNode).prepend(newNode);
    } else {
        $('#other').append(newNode);
    }
}

 </script>
</head>
<body>
<div style="display:none;"><a id="firstLink" href="http://piwik.org/qa">First anchor link</a></div>

 <h1 id="qunit-header">piwik.js: Unit Tests</h1>
 <h2 id="qunit-banner"></h2>
 <div id="qunit-testrunner-toolbar"></div>
 <h2 id="qunit-userAgent"></h2>

 <div id="other" style="display:none;">
  <div id="div1"></div>
  <iframe name="iframe2"></iframe>
  <iframe name="iframe3"></iframe>
  <iframe name="iframe4"></iframe>
  <iframe name="iframe5"></iframe>
  <iframe name="iframe6"></iframe>
  <iframe name="iframe7"></iframe>
  <iframe name="iframe9"></iframe>
  <img id="image1" src=""/> <!-- Test require this empty source attribute before image2!! -->
  <img id="image2" data-content-piece src="img.jpg"/>
  <ul>
    <li><a id="click1" href="javascript:_e('div1').innerHTML='&lt;iframe src=&quot;http://click.example.com&quot;&gt;&lt;/iframe&gt;';void(0)" class="clicktest">ignore: implicit (JavaScript href)</a></li>
    <li><a id="click2" href="http://example.org" target="iframe2" class="piwik_ignore clicktest">ignore: explicit</a></li>
    <li><a id="click3" href="example.php" target="iframe3" class="clicktest">ignore: implicit (localhost)</a></li>
    <li><a id="click4" href="http://example.net" target="iframe4" class="clicktest">outlink: implicit (outbound URL)</a></li>
    <li><a id="click5" href="example.html" target="iframe5" class="piwik_link clicktest">outlink: explicit (localhost)</a></li>
    <li><a id="click6" href="example.pdf" target="iframe6" class="clicktest">download: implicit (file extension)</a></li>
    <li><a id="click7" href="example.word" target="iframe7" class="piwik_download clicktest">download: explicit</a></li>
    <li><a id="click8" href="example.exe" target="iframe8" class="clicktest">no click handler</a></li>
    <li><a id="click9" href="example.html" target="iframe7" download class="clicktest">download: explicit (attribute)</a></li>
    <li><a id="click11" href="http://example.co.nz/test-with-%F6%E4%FC/story/0" target="iframe9">outlink: containing iso-8859-1 encoded url</a></li>
  </ul>
  <div id="clickDiv"></div>
 </div>
 <map name="map">
     <area id="area1" shape="rect" coords="0,0,10,10" href="img.jpg" alt="Matomo">
     <area shape="circle" coords="10,10,10,20" href="img2.jpg" alt="Matomo2">
 </map>

 <ol id="qunit-tests"></ol>

 <div id="main" style="display:none;"></div>

 <script>

 if (isIE()) {
     (function () {
         // otherwise because of position:absolute some nodes will be visible but should not... it will show scroll bars in IE
         function fixWidthNode(tagName){
             var node = document.getElementsByTagName(tagName)[0];
             node.className = node.className + ' hideY ie';
         }
         fixWidthNode('html');
         fixWidthNode('body');
     })();
 }

var mockNowValue = null;
var hasLoaded = false;
function PiwikTest() {
    hasLoaded = true;

    module('externals');

    QUnit.testDone(function () {
        Piwik.getTracker().unsetPageIsUnloading();
    })

    // Delete cookies to prevent cookie store from impacting tests
    deleteCookies();

    mockDateMethods();

    test("JSLint", function() {
        expect(1);

        stop();

        $.getScript("jslint/jslint.js", function(){

            var src = '<?php

            // Once we use JSHint instead of jslint, we could remove a few lines below,
            // to use instead the feature to disable jshint for the JSON2 block
//             /* jshint ignore:start */
//             // Code here will be linted with ignored by JSHint.
//             /* jshint ignore:end */


            function getLineCountJsLintStarted($src,$contentRemovedFromPos) {
                $contentRemoved = substr($src, 0, $contentRemovedFromPos);
                // the JS code contain \n within the JS code, but these are not new lines
                $contentRemovedWithoutBackslash = str_replace('\\\n', '', $contentRemoved);
                $countOfLinesRemoved = count(explode('\\n', $contentRemovedWithoutBackslash)) - 1;
                return $countOfLinesRemoved;
            }

            $src = file_get_contents('../../js/piwik.js');

            $src = strtr($src, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
            $contentRemovedFromPos = strpos($src, '/* startjslint */');
            $contentToJslint = substr($src, $contentRemovedFromPos);

            echo "$contentToJslint"; ?>';

            var result = JSLINT(src);
            ok( result, "JSLint validation: please check the browser console for the list of jslint errors." );
            if (console && console.log && !result) {
                var countOfLinesRemoved = <?php echo getLineCountJsLintStarted($src,$contentRemovedFromPos); ?>;

                // we fix the line numbers so they match to the line numbers in ../../js/piwik.js
                JSLINT.errors.forEach( function (item, index) {
                    if (item) {
                        item.line += countOfLinesRemoved;
                    }
                    console.log(item);
                });

                console.log('JSLINT errors', JSLINT.errors);
            }

            start();
        });

    });

    test("JSON", function() {
        expect(49);

        var tracker = Piwik.getTracker(), dummy;

        equal( typeof Piwik.JSON.stringify, 'function', 'JSON.stringify function' );
        equal( typeof Piwik.JSON.stringify(dummy), 'undefined', 'undefined' );

        equal( Piwik.JSON.stringify(null), 'null', 'null' );
        equal( Piwik.JSON.stringify(true), 'true', 'true' );
        equal( Piwik.JSON.stringify(false), 'false', 'false' );
        ok( Piwik.JSON.stringify(0) === '0', 'Number 0' );
        ok( Piwik.JSON.stringify(1) === '1', 'Number 1' );
        ok( Piwik.JSON.stringify(-1) === '-1', 'Number -1' );
        ok( Piwik.JSON.stringify(42) === '42', 'Number 42' );

        ok( Piwik.JSON.stringify(1.0) === '1.0'
            || Piwik.JSON.stringify(1.0) === '1', 'float 1.0' );

        equal( Piwik.JSON.stringify(1.1), '1.1', 'float 1.1' );
        equal( Piwik.JSON.stringify(""), '""', 'empty string' );
        equal( Piwik.JSON.stringify('"'), '"' + '\\' + '"' + '"', 'string "' );
        equal( Piwik.JSON.stringify('\\'), '"' + '\\\\' + '"', 'string \\' );

        equal( Piwik.JSON.stringify("1"), '"1"', 'string "1"' );
        equal( Piwik.JSON.stringify("ABC"), '"ABC"', 'string ABC' );
        equal( Piwik.JSON.stringify("\x40\x41\x42\x43"), '"@ABC"', '\\x hex string @ABC' );

        ok( Piwik.JSON.stringify("\u60a8\u597d") == '"您好"'
            || Piwik.JSON.stringify("\u60a8\u597d") == '"\\u60a8\\u597d"', '\\u Unicode string 您好' );

        ok( Piwik.JSON.stringify("ßéàêö您好") == '"ßéàêö您好"'
            || Piwik.JSON.stringify("ßéàêö您好") == '"\\u00df\\u00e9\\u00e0\\u00ea\\u00f6\\u60a8\\u597d"', 'string non-ASCII text' );

        equal( Piwik.JSON.stringify("20060228T08:00:00"), '"20060228T08:00:00"', 'string "20060228T08:00:00"' );

        var d = new Date();
        d.setTime(1240013340000);
        ok( Piwik.JSON.stringify(d) === '"2009-04-18T00:09:00Z"'
            || Piwik.JSON.stringify(d) === '"2009-04-18T00:09:00.000Z"', 'Date');

        equal( Piwik.JSON.stringify([1, 2, 3]), '[1,2,3]', 'Array of numbers' );
        equal( Piwik.JSON.stringify({'key' : 'value'}), '{"key":"value"}', 'Object (members)' );
        equal( Piwik.JSON.stringify(
            [ {'domains' : ['example.com', 'example.ca']},
            {'names' : ['Sean', 'Cathy'] } ]
        ), '[{"domains":["example.com","example.ca"]},{"names":["Sean","Cathy"]}]', 'Nested members' );

        equal( typeof eval('('+dummy+')'), 'undefined', 'eval undefined' );

        equal( typeof Piwik.JSON.parse, 'function', 'JSON.parse function' );

        // these throw a SyntaxError
//      equal( typeof Piwik.JSON.parse('undefined'), 'undefined', 'undefined' );
//      equal( typeof Piwik.JSON.parse(dummy), 'undefined', 'undefined' );
//      equal( Piwik.JSON.parse('undefined'), dummy, 'undefined' );
//      equal( Piwik.JSON.parse('undefined'), undefined, 'undefined' );

        strictEqual( Piwik.JSON.parse('null'), null, 'null' );
        strictEqual( Piwik.JSON.parse('true'), true, 'true' );
        strictEqual( Piwik.JSON.parse('false'), false, 'false' );

        equal( Piwik.JSON.parse('0'), 0, 'Number 0' );
        equal( Piwik.JSON.parse('1'), 1, 'Number 1' );
        equal( Piwik.JSON.parse('-1'), -1, 'Number -1' );
        equal( Piwik.JSON.parse('42'), 42, 'Number 42' );

        ok( Piwik.JSON.parse('1.0') === 1.0
            || Piwik.JSON.parse('1.0') === 1, 'float 1.0' );

        equal( Piwik.JSON.parse('1.1'), 1.1, 'float 1.1' );
        equal( Piwik.JSON.parse('""'), "", 'empty string' );
        equal( Piwik.JSON.parse('"' + '\\' + '"' + '"'), '"', 'string "' );
        equal( Piwik.JSON.parse('"\\\\"'), '\\', 'string \\' );

        equal( Piwik.JSON.parse('"1"'), "1", 'string "1"' );
        equal( Piwik.JSON.parse('"ABC"'), "ABC", 'string ABC' );
        equal( Piwik.JSON.parse('"@ABC"'), "\x40\x41\x42\x43", 'Hex string @ABC' );

        ok( Piwik.JSON.parse('"您好"') == "\u60a8\u597d"
            && Piwik.JSON.parse('"\\u60a8\\u597d"') == "您好", 'Unicode string 您好' );

        ok( Piwik.JSON.parse('"ßéàêö您好"') == "ßéàêö您好"
            && Piwik.JSON.parse('"\\u00df\\u00e9\\u00e0\\u00ea\\u00f6\\u60a8\\u597d"') == "ßéàêö您好", 'string non-ASCII text' );

        equal( Piwik.JSON.parse('"20060228T08:00:00"'), "20060228T08:00:00", 'string "20060228T08:00:00"' );

        // these aren't converted back to Date objects
        equal( Piwik.JSON.parse('"2009-04-18T00:09:00Z"'), "2009-04-18T00:09:00Z", 'string "2009-04-18T00:09:00Z"' );
        equal( Piwik.JSON.parse('"2009-04-18T00:09:00.000Z"'), "2009-04-18T00:09:00.000Z", 'string "2009-04-18T00:09:00.000Z"' );

        deepEqual( Piwik.JSON.parse('[1,2,3]'), [1, 2, 3], 'Array of numbers' );
        deepEqual( Piwik.JSON.parse('{"key":"value"}'), {'key' : 'value'}, 'Object (members)' );
        deepEqual( Piwik.JSON.parse('[{"domains":["example.com","example.ca"]},{"names":["Sean","Cathy"]}]'),
            [ {'domains' : ['example.com', 'example.ca']}, {'names' : ['Sean', 'Cathy'] } ], 'Nested members' );
    });

    module("core", {
        setup: function () {
            mockNowValue = null;
            Piwik.getTracker().clearTrackedContentImpressions();
        },
        teardown: function () {
            mockNowValue = null;
            $('#other #content').remove();
        }
    });

    test("Piwik plugin methods", function() {
        expect(33);

        // TESTS FOR retryMissedPluginCalls

        // these 2 calls should fail because they do not exist
        _paq.push(['MyCustomPlugin::myCustomStaticMethod']);
        _paq.push(['MyCustomPlugin::myCustomStaticMethod2']);
        _paq.push(['MyCustomPlugin.myCustomMethod']);

        // now we define these method
        var called = 0;
        var calledStatic = 0;
        var calledStatic2 = 0;
        Piwik.MyCustomPlugin = {myCustomStaticMethod: function () { calledStatic++; }};
        var asyncTrackers = Piwik.getAsyncTrackers();
        var i = 0;
        for (i; i < asyncTrackers.length; i++) {
            asyncTrackers[i].MyCustomPlugin = {myCustomMethod: function () { called++; }};
        }

        // now we retry those calls
        Piwik.retryMissedPluginCalls();

        strictEqual(1, called, "retryMissedPluginCalls, successfully executed non static method once it is defined");
        strictEqual(1, calledStatic, "retryMissedPluginCalls, successfully executed static method once it is defined");
        strictEqual(0, calledStatic2, "retryMissedPluginCalls, should not have executed not defined method");

        // defining another method
        Piwik.MyCustomPlugin.myCustomStaticMethod2 = function () { calledStatic2++; };

        // retrying again should not call the missed plugin calls again because they are now defined
        Piwik.retryMissedPluginCalls();

        strictEqual(1, called, "retryMissedPluginCalls, should not execute a resolved missed call again");
        strictEqual(1, calledStatic, "retryMissedPluginCalls, should not execute a resolved missed call again");
        strictEqual(1, calledStatic2, "retryMissedPluginCalls, successfully executed static method 2 once it is defined");

        // calling them now that they are defined increases the counter immediately
        _paq.push(['MyCustomPlugin::myCustomStaticMethod']);
        _paq.push(['MyCustomPlugin.myCustomMethod']);

        strictEqual(2, called, "executing static plugin method works directly if defined");
        strictEqual(2, calledStatic, "executing plugin method works directly if defined");
        strictEqual(1, calledStatic2, "a method is only executed when actually pushed");

        // TESTS FOR events
        var calledEvent1 = 0;
        var calledEvent1_1 = 0;
        var calledEvent2 = 0;
        var passedArgs = null;

        function callEvent1() { calledEvent1++; }
        function callEvent1_1() { calledEvent1_1++; }
        function callEvent2(arg1, arg2) { calledEvent2++; passedArgs = [arg1, arg2]; }

        Piwik.on('myEvent1', callEvent1);
        Piwik.on('myEvent2', callEvent2);

        Piwik.trigger('myEvent1', []);
        strictEqual(1, calledEvent1, "event, should trigger event and call handler callEvent1");

        Piwik.trigger('myEvent1', []);
        strictEqual(2, calledEvent1, "event, should trigger event whenever it is called and call handler callEvent1 again");
        strictEqual(0, calledEvent2, "event, should only execute event listeners that listen to that triggered event");

        Piwik.trigger('myEvent2', ['arg1', 'arg2']);
        strictEqual(2, calledEvent1, "event, should not have executed that event because it has different name");
        strictEqual(1, calledEvent2, "event, should have executed different handler this time");
        deepEqual(['arg1', 'arg2'], passedArgs, "event, should be possible to pass arguments to events");

        Piwik.on('myEvent1', callEvent1_1);

        Piwik.trigger('myEvent1', []);
        strictEqual(3, calledEvent1, "event, should call multiple event handlers when many listen to same event");
        strictEqual(1, calledEvent1_1, "event, should call multiple event handlers when many listen to same event");

        Piwik.off('myEvent1', callEvent1);

        Piwik.trigger('myEvent1', []);
        strictEqual(3, calledEvent1, "event, it is possible to remove an event listener and it will not be executed anymore");
        strictEqual(2, calledEvent1_1, "event, should still call other event listeners when others were removed");

        /**
         * TESTING DOM
         **/
        var loaded = false;
        var ready = false;
        var customEvent = false;

        strictEqual('object', typeof Piwik.DOM, "Piwik.DOM object is defined");
        strictEqual('function', typeof Piwik.DOM.onReady, "DOM.onReady method is defined");
        strictEqual('function', typeof Piwik.DOM.onLoad, "DOM.onLoad method is defined");
        strictEqual('function', typeof Piwik.DOM.addEventListener, "DOM.addEventListener method is defined");
        strictEqual('function', typeof Piwik.DOM.isNodeVisible, "DOM.isNodeVisible method is defined");
        strictEqual('function', typeof Piwik.DOM.isOrWasNodeVisible, "DOM.isOrWasNodeVisible method is defined");

        Piwik.DOM.onLoad(function () {
            loaded = true;
        });
        Piwik.DOM.onReady(function () {
            ready = true;
        });

        strictEqual(true, ready, "onReady, DOM should be ready");
        strictEqual(true, loaded, "event, DOM should be loaded");

        Piwik.DOM.addEventListener(_e('click7'), 'myCustomEvent', function () {
            customEvent = true;
        });
        triggerEvent(_e('click7'), 'myCustomEvent');

        strictEqual(true, customEvent, "DOM.addEventListener works");

        strictEqual('object', typeof Piwik.JSON, "Piwik.JSON object is defined");
        strictEqual('function', typeof Piwik.JSON.stringify, "JSON.stringify method is defined");
        strictEqual('function', typeof Piwik.JSON.parse, "JSON.parse method is defined");
        strictEqual('[]', Piwik.JSON.stringify([]));
        propEqual([], Piwik.JSON.parse('[]'));
    });

    test("Query", function() {
        var tracker = Piwik.getTracker();
        var query   = tracker.getQuery();
        var actual;


        actual = query.hasNodeCssClass();
        strictEqual(actual, false, "hasNodeCssClass, no element set");

        actual = query.hasNodeCssClass(_e('clickDiv'));
        strictEqual(actual, false, "hasNodeCssClass, no classname set");

        actual = query.hasNodeCssClass(_e('clickDiv'), 'anyClass');
        strictEqual(actual, false, "hasNodeCssClass, element has no class at all");

        actual = query.hasNodeCssClass(_e('click3'), 'anyClass');
        strictEqual(actual, false, "hasNodeCssClass, element has one classes and it does not match");

        actual = query.hasNodeCssClass(_e('click3'), 'clicktest');
        strictEqual(actual, true, "hasNodeCssClass, element has one classes and it matches");

        actual = query.hasNodeCssClass(_e('click7'), 'anyClass');
        strictEqual(actual, false, "hasNodeCssClass, element has many classes but not this one");

        actual = query.hasNodeCssClass(_e('click7'), 'piwik_download');
        strictEqual(actual, true, "hasNodeCssClass, element has many classes and it matches");


        actual = query.findNodesHavingCssClass();
        propEqual(actual, [], "findNodesHavingCssClass, no node set");

        actual = query.findNodesHavingCssClass(document.body);
        propEqual(actual, [], "findNodesHavingCssClass, no classname set");

        actual = query.findNodesHavingCssClass(document.body, 'piwik_ignore');
        propEqual(actual, [_e('click2')], "findNodesHavingCssClass, find matching within body");

        actual = query.findNodesHavingCssClass(_e('other'), 'piwik_ignore');
        propEqual(actual, [_e('click2')], "findNodesHavingCssClass, ffind matching within div");

        actual = query.findNodesHavingCssClass(_e('other'), 'piwik_download');
        propEqual(actual, [_e('click7')], "findNodesHavingCssClass, find matching within div different class");

        actual = query.findNodesHavingCssClass(_e('other'), 'clicktest');
        propEqual(actual, [_e('click1'), _e('click2'), _e('click3'), _e('click4'), _e('click5'), _e('click6'), _e('click7'), _e('click8'), _e('click9')], "findNodesHavingCssClass, find many matching within div");

        actual = query.findNodesHavingCssClass(_e('click7'), 'piwik_download');
        propEqual(actual, [], "findNodesHavingCssClass, should not find if passed node has class itself");

        actual = query.findNodesHavingCssClass(_e('clickDiv'), 'clicktest');
        if (_e('clickDiv').children) {
            ok(_e('clickDiv').children.length === 0, "clickDiv should not have any children");
        }
        propEqual(actual, [], "findNodesHavingCssClass, should not find anything");



        actual = query.findFirstNodeHavingClass();
        strictEqual(actual, undefined, "findFirstNodeHavingClass, no node set");

        actual = query.findFirstNodeHavingClass(document.body);
        strictEqual(actual, undefined, "findFirstNodeHavingClass, no classname set");

        actual = query.findFirstNodeHavingClass(document.body, 'notExistingClass');
        strictEqual(actual, undefined, "findFirstNodeHavingClass, no such classname exists");

        actual = query.findFirstNodeHavingClass(document.body, 'piwik_ignore');
        strictEqual(actual, _e('click2'), "findFirstNodeHavingClass, find matching within body");

        actual = query.findFirstNodeHavingClass(_e('other'), 'clicktest');
        strictEqual(actual, _e('click1'), "findFirstNodeHavingClass, find matching within node");

        actual = query.findFirstNodeHavingClass(_e('click1'), 'clicktest');
        strictEqual(actual, _e('click1'), "findFirstNodeHavingClass, passed node has class itself");



        actual = query.hasNodeAttribute();
        strictEqual(actual, false, "hasNodeAttribute, no element set");

        actual = query.hasNodeAttribute(_e('clickDiv'));
        strictEqual(actual, false, "hasNodeAttribute, no attribute set");

        actual = query.hasNodeAttribute(document.body, 'anyAttribute');
        strictEqual(actual, false, "hasNodeAttribute, element has no attribute at all");

        actual = query.hasNodeAttribute(_e('click2'), 'anyAttribute');
        strictEqual(actual, false, "hasNodeAttribute, element has attributes and it does not match");

        actual = query.hasNodeAttribute(_e('click2'), 'href');
        strictEqual(actual, true, "hasNodeAttribute, element has attributes and it does match");

        actual = query.hasNodeAttribute(_e('image1'), 'src');
        strictEqual(actual, true, "hasNodeAttribute, element has attributes and it does match other attribute");

        actual = query.hasNodeAttribute(_e('image2'), 'data-content-piece');
        strictEqual(actual, true, "hasNodeAttribute, element has attribute and no value");



        actual = query.hasNodeAttributeWithValue();
        strictEqual(actual, false, "hasNodeAttributeWithValue, no element set");

        actual = query.hasNodeAttributeWithValue(_e('clickDiv'));
        strictEqual(actual, false, "hasNodeAttributeWithValue, no attribute set");

        actual = query.hasNodeAttributeWithValue(document.body, 'anyAttribute');
        strictEqual(actual, false, "hasNodeAttributeWithValue, element has no attribute at all");

        actual = query.hasNodeAttributeWithValue(_e('click2'), 'anyAttribute');
        strictEqual(actual, false, "hasNodeAttributeWithValue, element has attributes but not this one");

        actual = query.hasNodeAttributeWithValue(_e('click2'), 'href');
        strictEqual(actual, true, "hasNodeAttributeWithValue, element has attribute and value");

        actual = query.hasNodeAttributeWithValue(_e('image1'), 'src');
        strictEqual(actual, false, "hasNodeAttributeWithValue, element has attribute but no value");

        actual = query.hasNodeAttributeWithValue(_e('image2'), 'data-content-piece');
        strictEqual(actual, false, "hasNodeAttributeWithValue, element has attribute but no value");


        actual = query.getAttributeValueFromNode();
        strictEqual(actual, undefined, "getAttributeValueFromNode, no element set");

        actual = query.getAttributeValueFromNode(_e('clickDiv'));
        strictEqual(actual, undefined, "getAttributeValueFromNode, no attribute set");

        actual = query.getAttributeValueFromNode(document.body, 'anyAttribute');
        strictEqual(actual, undefined, "getAttributeValueFromNode, element has no attribute at all");

        actual = query.getAttributeValueFromNode(_e('click2'), 'anyAttribute');
        strictEqual(actual, undefined, "getAttributeValueFromNode, element has attributes but not this one");

        actual = query.getAttributeValueFromNode(_e('click2'), 'href');
        strictEqual(actual, 'http://example.org', "getAttributeValueFromNode, element has attribute and value");

        actual = query.getAttributeValueFromNode(_e('image1'), 'src');
        strictEqual(actual, '', "getAttributeValueFromNode, element has attribute but no value");

        actual = query.getAttributeValueFromNode(_e('image2'), 'data-content-piece');
        strictEqual(actual, '', "getAttributeValueFromNode, element has attribute but no value");

        actual = query.getAttributeValueFromNode(_e('click2'), 'class');
        strictEqual(actual, 'piwik_ignore clicktest', "getAttributeValueFromNode, element has attribute class and value");



        actual = query.findNodesHavingAttribute();
        propEqual(actual, [], "findNodesHavingAttribute, no node set");

        actual = query.findNodesHavingAttribute(document.body);
        propEqual(actual, [], "findNodesHavingAttribute, no attribute set");

        actual = query.findNodesHavingAttribute(document.body, 'anyAttribute');
        propEqual(actual, [], "findNodesHavingAttribute, should not find any such attribute within body");

        actual = query.findNodesHavingAttribute(document.body, 'style');
        strictEqual(actual.length, 3, "findNodesHavingAttribute, should find a few");

        actual = query.findNodesHavingAttribute(_e('click1'), 'href');
        propEqual(actual, [], "findNodesHavingAttribute, should not find itself if the passed element has the attribute");

        actual = query.findNodesHavingAttribute(_e('clickDiv'), 'id');
        if (_e('clickDiv').children) {
            ok(_e('clickDiv').children.length === 0, "clickDiv should not have any children");
        }
        propEqual(actual, [], "findNodesHavingAttribute, this element does not have children");

        actual = query.findNodesHavingAttribute(document.body, 'href');
        ok(actual.length > 11, "findNodesHavingAttribute, should find many elements within body");

        actual = query.findNodesHavingAttribute(_e('other'), 'href');
        propEqual(actual, [_e('click1'), _e('click2'), _e('click3'), _e('click4'), _e('click5'), _e('click6'), _e('click7'), _e('click8'), _e('click9'), _e('click11')], "findNodesHavingAttribute, should find many elements within node");

        actual = query.findNodesHavingAttribute(_e('other'), 'anyAttribute');
        propEqual(actual, [], "findNodesHavingAttribute, should not find any such attribute within div");


// TODO it is a bit confusing that findNodesHavingAttribute/CssClass does not include the passed node in the search but findFirstNodeHavingAttribute/CssClass does
        actual = query.findFirstNodeHavingAttribute();
        strictEqual(actual, undefined, "findFirstNodeHavingAttribute, no node set");

        actual = query.findFirstNodeHavingAttribute(document.body);
        strictEqual(actual, undefined, "findFirstNodeHavingAttribute, no attribute set");

        actual = query.findFirstNodeHavingAttribute(document.body, 'anyAttribute');
        strictEqual(actual, undefined, "findFirstNodeHavingAttribute, should not find any such attribute within body");

        actual = query.findFirstNodeHavingAttribute(_e('click1'), 'href');
        strictEqual(actual, _e('click1'), "findFirstNodeHavingAttribute, element has the attribute itself and not a children");

        actual = query.findFirstNodeHavingAttribute(_e('clickDiv'), 'anyAttribute');
        strictEqual(actual, undefined, "findFirstNodeHavingAttribute, this element does not have children");

        actual = query.findFirstNodeHavingAttribute(document.body, 'href');
        strictEqual(actual, _e('firstLink'), "findFirstNodeHavingAttribute, should find first link within body");

        actual = query.findFirstNodeHavingAttribute(_e('other'), 'href');
        strictEqual(actual, _e('click1'), "findFirstNodeHavingAttribute, should find fist link within node");



        actual = query.findFirstNodeHavingAttributeWithValue();
        strictEqual(actual, undefined, "findFirstNodeHavingAttributeWithValue, no node set");

        actual = query.findFirstNodeHavingAttributeWithValue(document.body);
        strictEqual(actual, undefined, "findFirstNodeHavingAttributeWithValue, no attribute set");

        actual = query.findFirstNodeHavingAttributeWithValue(document.body, 'anyAttribute');
        strictEqual(actual, undefined, "findFirstNodeHavingAttributeWithValue, should not find any such attribute within body");

        actual = query.findFirstNodeHavingAttributeWithValue(_e('click2'), 'href');
        strictEqual(actual, _e('click2'), "findFirstNodeHavingAttributeWithValue, element has the attribute itself and not a children");

        actual = query.findFirstNodeHavingAttributeWithValue(_e('clickDiv'), 'anyAttribute');
        strictEqual(actual, undefined, "findFirstNodeHavingAttributeWithValue, this element does not have children");

        actual = query.findFirstNodeHavingAttributeWithValue(document.body, 'href');
        strictEqual(actual, _e('firstLink'), "findFirstNodeHavingAttributeWithValue, should find first link within body");

        actual = query.findFirstNodeHavingAttributeWithValue(document.body, 'src');

        strictEqual(actual, _e('image2'), "findFirstNodeHavingAttributeWithValue, should not return first image which has empty src attribute");



        actual = query.htmlCollectionToArray();
        propEqual(actual, [], "htmlCollectionToArray, should always return an array even if nothing given");

        actual = query.htmlCollectionToArray(5);
        propEqual(actual, [], "htmlCollectionToArray, should always return an array even if interger given"); // would still parse string to an array but we can live with that

        var htmlCollection = document.getElementsByTagName('a');
        actual = query.htmlCollectionToArray(htmlCollection);
        ok($.isArray(actual), 'htmlCollectionToArray, should convert to array');
        ok(actual.length === htmlCollection.length, 'htmlCollectionToArray should have same amount of elements as before');
        ok(actual.length > 10, 'htmlCollectionToArray, just make sure there are many a elements found. otherwise test is useless');
        ok(-1 !== indexOfArray(actual, _e('click1')), 'htmlCollectionToArray, random check to make sure it contains a link');


        actual = query.isLinkElement();
        strictEqual(actual, false, "isLinkElement, no element set");

        actual = query.isLinkElement(_e('div1'));
        strictEqual(actual, false, "isLinkElement, a div is not a link element");

        actual = query.isLinkElement(document.createTextNode('ff'));
        strictEqual(actual, false, "isLinkElement, a text node is not a link element");

        actual = query.isLinkElement(document.createComment('tt'));
        strictEqual(actual, false, "isLinkElement, a comment is not a link element");

        actual = query.isLinkElement(_e('area1'));
        strictEqual(actual, true, "isLinkElement, an area element is a link element");

        actual = query.isLinkElement(_e('click1'));
        strictEqual(actual, true, "isLinkElement, an a element is a link element");


        actual = query.find();
        propEqual(actual, [], "find, no selector passed should return an empty array");

        actual = query.find('[data-content-piece]');
        propEqual(actual, [_e('image2')], "find, should find elements by attribute");

        actual = query.find('.piwik_link');
        propEqual(actual, [_e('click5')], "find, should find elements by class");

        actual = query.find('#image1');
        propEqual(actual, [_e('image1')], "find, should find elements by id");

        actual = query.find('[href]');
        ok(actual.length > 10, "find, should find many elements by attribute");
        ok(-1 !== indexOfArray(actual, _e('click1')), 'find, random check to make sure it contains a link');

        actual = query.find('.clicktest');
        ok(actual.length === 9, "find, should find many elements by class");
        ok(-1 !== indexOfArray(actual, _e('click1')), 'find, random check to make sure it contains a link');



        actual = query.findMultiple();
        propEqual(actual, [], "findMultiple, no selectors passed should return an empty array");

        actual = query.findMultiple([]);
        propEqual(actual, [], "findMultiple, empty selectors passed should return an empty array");

        actual = query.findMultiple(['.piwik_link']);
        propEqual(actual, [_e('click5')], "findMultiple, only one selector passed");

        actual = query.findMultiple(['.piwik_link', '[data-content-piece]']);
        propEqual(actual, [_e('image2'), _e('click5')], "findMultiple, two selectors passed");

        actual = query.findMultiple(['.piwik_link', '[data-content-piece]', '#image2', '#div1']);
        propEqual(actual, [_e('div1'), _e('image2'), _e('click5')], "findMultiple, should make nodes unique in case we select the same multiple times");


        actual = query.findNodesByTagName();
        propEqual(actual, [], "findNodesByTagName, no element and no tag name set");

        actual = query.findNodesByTagName(document.body);
        propEqual(actual, [], "findNodesByTagName, no tag name set");

        actual = query.findNodesByTagName(document.body, 'notExistingOne');
        propEqual(actual, [], "findNodesByTagName, should not find any such element");

        actual = query.findNodesByTagName(document.body, 'a');
        ok($.isArray(actual), "findNodesByTagName, should always return an array");

        actual = query.findNodesByTagName(document.body, 'h1');
        propEqual(actual, [_e('qunit-header')], "findNodesByTagName, find exactly one");

        actual = query.findNodesByTagName(document.body, 'a');
        ok(actual.length > 10, "findNodesByTagName, find many, even nested ones");
        ok(indexOfArray(actual, _e('click1')), "findNodesByTagName, just a random test to make sure it actually contains a link");
    });

    test("contentFindContentBlock", function() {

        var tracker = Piwik.getTracker();
        var content = tracker.getContent();
        var actual, expected;

        actual = content.findContentNodes();
        propEqual(actual, [], "findContentNodes, should not find any content node when there is none");

        actual = content.findContentNodesWithinNode();
        propEqual(actual, [], "findContentNodesWithinNode, should not find any content node when no node passed");

        actual = content.findContentNodesWithinNode(_e('other'));
        ok(_e('other'), "if we do not get an element here test is not useful");
        propEqual(actual, [], "findContentNodesWithinNode, should not find any content node when there is none");

        actual = content.findParentContentNode(_e('click1'));
        ok(_e('click1'), "if we do not get an element here test is not useful");
        strictEqual(actual, undefined, "findParentContentNode, should not find any content node when there is none");



        setupContentTrackingFixture('findContentBlockTest');

        var isOneWithClass = _s('#isOneWithClass');
        var isOneWithAttr  = _s('#isOneWithAttribute');
        var isHrefUrl      = _s('[href="http://www.example.com"]');
        var containsOneWithAttr = _s('#containsOneWithAttribute [data-track-content]');

        expected = [containsOneWithAttr, isOneWithAttr, isHrefUrl, isOneWithClass];
        actual = content.findContentNodes();
        propEqual(actual, expected, "findContentNodes, should find all content blocks within the DOM");

        actual = content.findContentNodesWithinNode(_s(''));
        propEqual(actual, expected, "findContentNodesWithinNode, should find all content blocks within the DOM");

        actual = content.findContentNodesWithinNode(_s('#containsOneWithAttribute'));
        propEqual(actual, [containsOneWithAttr], "findContentNodesWithinNode, should find content blocks within a node");

        actual = content.findContentNodesWithinNode(isOneWithClass);
        propEqual(actual, [isOneWithClass], "findContentNodesWithinNode, should find one content block in the node itself");

        actual = content.findParentContentNode(_s('#isOneWithClass'));
        strictEqual(actual, isOneWithClass, "findParentContentNode, should find itself in case the passed node is a content block with class");

        actual = content.findParentContentNode(_s('#isOneWithAttribute'));
        strictEqual(actual, isOneWithAttr, "findParentContentNode, should find itself in case the passed node is a content block with attribute");

        actual = content.findParentContentNode(_s('#innerNode'));
        strictEqual(actual, isHrefUrl, "findParentContentNode, should find parent content block");
    });

    test("contentFindContentNodes", function() {
        function ex(testNumber) { // select node within content test scope
            $nodes = $('#contenttest #ex' + testNumber);
            if ($nodes.length) {
                return $nodes[0];
            } else {
                ok(false, 'selector was not found but should be "#contenttest #ex' + selector + '"')
            }
        }

        var tracker = Piwik.getTracker();
        var content = tracker.getContent();
        var actual;

        var unrelatedNode = _e('other');
        ok(unrelatedNode, 'Make sure this element exists');

        actual = content.findTargetNodeNoDefault();
        strictEqual(actual, undefined, "findTargetNodeNoDefault, should not find anything if no node set");

        actual = content.findTargetNode();
        strictEqual(actual, undefined, "findTargetNode, should not find anything if no node set");

        actual = content.findPieceNode();
        strictEqual(actual, undefined, "findPieceNode, should not find anything if no node set");



        setupContentTrackingFixture('findContentNodesTest');

        var example1 = ex(1);
        ok(example1, 'Make sure this element exists to verify setup');

        ok("test fall back to content block node");

        actual = content.findTargetNodeNoDefault(example1);
        strictEqual(actual, undefined, "findTargetNodeNoDefault, should return nothing as no target set");

        actual = content.findTargetNode(example1);
        strictEqual(actual, example1, "findTargetNode, should fall back to content block node as no target set");

        actual = content.findPieceNode(example1);
        strictEqual(actual, example1, "findPieceNode, should not find anything if no node set");



        ok("test actually detects the attributes within a content block");

        actual = content.findTargetNodeNoDefault(ex(3));
        ok(undefined !== $(actual).attr(content.CONTENT_TARGET_ATTR), "findTargetNodeNoDefault, should have the attribute");
        strictEqual(actual, ex('3 a'), "findTargetNodeNoDefault, should find actual target node via attribute");

        actual = content.findTargetNode(ex(3));
        ok(undefined !== $(actual).attr(content.CONTENT_TARGET_ATTR), "findTargetNode, should have the attribute");
        strictEqual(actual, ex('3 a'), "findTargetNode, should find actual target node via attribute");

        actual = content.findPieceNode(ex(3));
        ok(undefined !== $(actual).attr(content.CONTENT_PIECE_ATTR), "findPieceNode, should have the attribute");
        strictEqual(actual, ex('3 img'), "findPieceNode, should find actual target piece via attribute");



        ok("test actually detects the CSS class within a content block");

        actual = content.findTargetNodeNoDefault(ex(4));
        ok($(actual).hasClass(content.CONTENT_TARGET_CLASS), "findTargetNodeNoDefault, should have the CSS class");
        strictEqual(actual, ex('4 a'), "findTargetNodeNoDefault, should find actual target node via class");

        actual = content.findTargetNode(ex(4));
        ok($(actual).hasClass(content.CONTENT_TARGET_CLASS), "findTargetNode, should have the CSS class");
        strictEqual(actual, ex('4 a'), "findTargetNode, should find actual target node via class");

        actual = content.findPieceNode(ex(4));
        ok($(actual).hasClass(content.CONTENT_PIECE_CLASS), "findPieceNode, should have the CSS class");
        strictEqual(actual, ex('4 img'), "findPieceNode, should find actual target piece via class");



        ok("test actually attributes takes precendence over class");

        actual = content.findTargetNodeNoDefault(ex(5));
        ok(undefined !== $(actual).attr(content.CONTENT_TARGET_ATTR), "findTargetNodeNoDefault, should have the attribute");
        strictEqual(actual.textContent, 'Target with attribute', "findTargetNodeNoDefault, should igonre node with class and pick attribute node");

        actual = content.findTargetNode(ex(5));
        ok(undefined !== $(actual).attr(content.CONTENT_TARGET_ATTR), "findTargetNode, should have the attribute");
        strictEqual(actual.textContent, 'Target with attribute', "findTargetNode, should igonre node with class and pick attribute node");

        actual = content.findPieceNode(ex(5));
        ok(undefined !== $(actual).attr(content.CONTENT_PIECE_ATTR), "findPieceNode, should have the attribute");
        strictEqual(actual.textContent, 'Piece with attribute', "findPieceNode, should igonre node with class and pick attribute node");



        ok("make sure it picks always the first one with multiple nodes have same class or same attribute");

        actual = content.findTargetNode(ex(6));
        ok($(actual).hasClass(content.CONTENT_TARGET_CLASS), "findTargetNode, should have the CSS class");
        strictEqual(actual.textContent, 'Target with class1', "findTargetNode, should igonre node with class and pick attribute node");

        actual = content.findPieceNode(ex(6));
        ok($(actual).hasClass(content.CONTENT_PIECE_CLASS), "findPieceNode, should have the CSS class");
        strictEqual(actual.textContent, 'Piece with class1', "findPieceNode, should igonre node with class and pick attribute node");

        actual = content.findTargetNode(ex(7));
        ok(undefined !== $(actual).attr(content.CONTENT_TARGET_ATTR), "findTargetNode, should have the attribute");
        strictEqual(actual.textContent, 'Target with attribute1', "findTargetNode, should igonre node with class and pick attribute node");

        actual = content.findPieceNode(ex(7));
        ok(undefined !== $(actual).attr(content.CONTENT_PIECE_ATTR), "findPieceNode, should have the attribute");
        strictEqual(actual.textContent, 'Piece with attribute1', "findPieceNode, should igonre node with class and pick attribute node");
    });

    test("contentUtilities", function() {

        var tracker = Piwik.getTracker();
        var content = tracker.getContent();
        var query   = tracker.getQuery();
        content.setLocation(); // clear possible previous location
        var actual, expected;

        function assertTrimmed(value, expected, message)
        {
            strictEqual(content.trim(value), expected, message);
        }

        function assertRemoveDomainKeepsValueUntouched(value, message)
        {
            strictEqual(content.removeDomainIfIsInLink(value), value, message);
        }

        function assertIsSameDomain(url, message)
        {
            strictEqual(content.isSameDomain(url), true, message);
        }

        function assertIsNotSameDomain(url, message)
        {
            strictEqual(content.isSameDomain(url), false, 'isSameDomain, ' + message);
        }

        function assertDomainWillBeRemoved(url, expected, message)
        {
            strictEqual(content.removeDomainIfIsInLink(url), expected, message);
        }

        function assertBuildsAbsoluteUrl(url, expected, message)
        {
            strictEqual(content.toAbsoluteUrl(url), expected, message);
        }

        function assertImpressionRequestParams(name, piece, target, expected, message) {
            strictEqual(content.buildImpressionRequestParams(name, piece, target), expected + '&ca=1', message);
        }

        function assertInteractionRequestParams(interaction, name, piece, target, expected, message) {
            var actual = content.buildInteractionRequestParams(interaction, name, piece, target);
            if (actual) {
                expected = expected + '&ca=1';
            }
            strictEqual(actual, expected, message);
        }

        function assertShouldIgnoreInteraction(id, message) {
            var node = content.findTargetNode(_e(id));
            strictEqual(content.shouldIgnoreInteraction(node), true, message);
            ok($(node).hasClass(content.LEGACY_CONTENT_IGNOREINTERACTION_CLASS) || $(node).hasClass(content.CONTENT_IGNOREINTERACTION_CLASS) || undefined !== $(node).attr(content.CONTENT_IGNOREINTERACTION_ATTR), id + " needs to have either attribute or class");
        }

        function assertShouldNotIgnoreInteraction(id, message) {
            var node = content.findTargetNode(_e(id));
            strictEqual(content.shouldIgnoreInteraction(node), false, message);
        }

        function assertNodeAuthorizedToTriggerInteraction(contentNode, interactedNode, message) {
            strictEqual(tracker.isNodeAuthorizedToTriggerInteraction(_s(contentNode), _s(interactedNode)), true, message);
        }

        function assertNodeNotAuthorizedToTriggerInteraction(contentNode, interactedNode, message) {
            strictEqual(tracker.isNodeAuthorizedToTriggerInteraction(_s(contentNode), _s(interactedNode)), false, message);
        }

        function assertFoundMediaUrl(id, expected, message) {
            var node = content.findPieceNode(_e(id));
            strictEqual(content.findMediaUrlInNode(node), expected, message);
        }

        function assertIsUrlToCurrentDomain(url, message) {
            strictEqual(content.isUrlToCurrentDomain(url), true, message);
        }

        function assertNotUrlToCurrentDomain(url, message) {
            strictEqual(content.isUrlToCurrentDomain(url), false, message);
        }

        var locationAlias = $.extend({}, window.location);
        var origin = getOrigin();
        var host   = locationAlias.host;

        ok("test trim(text)");

        strictEqual(undefined, content.trim(), 'should not fail if nothing set / is undefined');
        assertTrimmed(null, null, 'should not trim if null');
        assertTrimmed(5, 5, 'should not trim a number');
        assertTrimmed('', '', 'should not change an empty string');
        assertTrimmed('   ', '', 'should remove all whitespace');
        assertTrimmed('   xxxx', 'xxxx', 'should remove left whitespace');
        assertTrimmed('   xxxx   ', 'xxxx', 'should remove left and right whitespace');
        assertTrimmed(" \t  xxxx   \t", 'xxxx', 'should remove tabs and whitespace');
        assertTrimmed('  xx    xx  ', 'xx    xx', 'should keep whitespace between text untouched');

        ok("test isSameDomain(url)");
        assertIsNotSameDomain(undefined, 'no url given');
        assertIsNotSameDomain(5, 'a number, not a url');
        assertIsNotSameDomain('foo bar', 'not a url');
        assertIsNotSameDomain('http://example.com', 'not same domain');
        assertIsNotSameDomain('https://www.example.com', 'not same domain and different protocol');
        assertIsNotSameDomain('http://www.example.com:8080', 'not same domain and different port');
        assertIsNotSameDomain('http://www.example.com/path/img.jpg', 'not same domain with path');

        assertIsSameDomain(origin, 'same protocol and same domain');
        assertIsSameDomain(origin + '/path/img.jpg', 'same protocol and same domain with path');
        assertIsSameDomain('https://' + host + '/path/img.jpg', 'different protocol is still same domain');
        assertIsSameDomain('http://' + host + ':8080/path/img.jpg', 'different port is still same domain');

        ok("test removeDomainIfIsInLink(url)");

        strictEqual(content.removeDomainIfIsInLink(), undefined, 'should not fail if nothing set / is undefined');
        assertRemoveDomainKeepsValueUntouched(null, 'should keep null untouched');
        assertRemoveDomainKeepsValueUntouched(5, 'should keep number untouched');
        assertRemoveDomainKeepsValueUntouched('', 'should keep empty string untouched');
        assertRemoveDomainKeepsValueUntouched('Any Text', 'should keep string untouched that is not a url');
        assertRemoveDomainKeepsValueUntouched('/path/img.jpg', 'should keep string untouched that looks like a path');
        assertRemoveDomainKeepsValueUntouched('ftp://path/img.jpg', 'should keep string untouched that looks like a path');
        assertRemoveDomainKeepsValueUntouched('http://www.example.com', 'should keep string untouched as it is different domain');
        assertRemoveDomainKeepsValueUntouched('http://www.example.com/', 'should keep string untouched as it is different domain');
        assertRemoveDomainKeepsValueUntouched('https://www.example.com/', 'should keep string untouched as it is different domain');
        assertRemoveDomainKeepsValueUntouched('http://www.example.com/path/img.jpg', 'should keep string untouched as it is different domain, this time with path');
        assertRemoveDomainKeepsValueUntouched('http://www.example.com:8080/path/img.jpg', 'should keep string untouched as it is different domain, this time with port');

        assertDomainWillBeRemoved(origin + '/path/img.jpg?x=y', '/path/img.jpg?x=y', 'should trim http domain with path that is the same as the current');
        assertDomainWillBeRemoved('https://' + host + '/path/img.jpg?x=y', '/path/img.jpg?x=y', 'should trim https domain with path that is the same as the current');
        assertDomainWillBeRemoved(origin, '/', 'should trim http domain without path that is the same as the current');
        assertDomainWillBeRemoved('https://' + host, '/', 'should trim https domain without path that is the same as the current');
        assertDomainWillBeRemoved('https://' + host + ':8080', '/', 'should trim https domain with port that is the same as the current');

        ok("test isUrlToCurrentDomain(url)");

        strictEqual(content.removeDomainIfIsInLink(), undefined, 'should not fail if nothing set / is undefined');
        assertNotUrlToCurrentDomain(null, ' null is not a urls');
        assertNotUrlToCurrentDomain(5, '5 is not a url');
        assertIsUrlToCurrentDomain('', 'empty string is same as current url so same domain');
        assertIsUrlToCurrentDomain('Any Text', 'relative url, same domain');
        assertIsUrlToCurrentDomain('/path/img.jpg', 'absolute url same domain');
        assertNotUrlToCurrentDomain('ftp://path/img.jpg', 'different protocol');
        assertNotUrlToCurrentDomain('http://www.example.com', 'different domain');
        assertNotUrlToCurrentDomain('http://www.example.com/', 'different domain with root path');
        assertNotUrlToCurrentDomain('https://www.example.com/', 'different domain and protocol');
        assertNotUrlToCurrentDomain('http://www.example.com/path/img.jpg', 'different domain, this time with path');
        assertNotUrlToCurrentDomain('http://www.example.com:8080/path/img.jpg', 'different domain, this time with port');

        assertIsUrlToCurrentDomain(origin + '/path/img.jpg?x=y', 'same domain with path');
        assertIsUrlToCurrentDomain(origin + '?x=y', 'same domain with question mark');
        assertNotUrlToCurrentDomain('https://' + host + '/path/img.jpg?x=y', 'different protocol and path is different url');
        assertIsUrlToCurrentDomain(origin, '/', 'same domain with root path');
        assertNotUrlToCurrentDomain('https://' + host, 'same domain but different protocol');
        assertNotUrlToCurrentDomain('https://' + host + ':5959', 'different protocol and port');
        assertNotUrlToCurrentDomain('http://' + host + ':5959', 'different protocol and port');

        ok("test toAbsoluteUrl(url) we need a lot of tests for this method as this will generate the redirect url");

        strictEqual(undefined, content.toAbsoluteUrl(), 'should not fail if nothing set / is undefined');
        assertBuildsAbsoluteUrl(null, null, 'null should be untouched');
        assertBuildsAbsoluteUrl(5, 5, 'number should be untouched');
        assertBuildsAbsoluteUrl('', locationAlias.href, 'an empty string should generate the same URL as it is currently');
        assertBuildsAbsoluteUrl('/', origin + '/', 'root path');
        assertBuildsAbsoluteUrl('/test', origin + '/test', 'absolute url');
        assertBuildsAbsoluteUrl('/test/', origin + '/test/', 'absolute url');
        assertBuildsAbsoluteUrl('?x=5', toAbsoluteUrl('?x=5'), 'absolute url');
        assertBuildsAbsoluteUrl('path', toAbsoluteUrl('path'), 'relative path');
        assertBuildsAbsoluteUrl('path/x?p=5', toAbsoluteUrl('path/x?p=5'), 'relative path');
        assertBuildsAbsoluteUrl('#test', toAbsoluteUrl('#test'), 'anchor url');
        assertBuildsAbsoluteUrl('//' + locationAlias.host + '/test/img.jpg', origin + '/test/img.jpg', 'inherit protocol url');
        assertBuildsAbsoluteUrl('mailto:test@example.com', 'mailto:test@example.com', 'mailto pseudo-protocol url');
        assertBuildsAbsoluteUrl('javascript:void 0', 'javascript:void 0', 'javascript pseudo-protocol url');
        assertBuildsAbsoluteUrl('tel:0123456789', 'tel:0123456789', 'tel pseudo-protocol url');
        assertBuildsAbsoluteUrl('anythinggggggggg:test', toAbsoluteUrl('anythinggggggggg:test'), 'we do not treat this one as pseudo-protocol url as there are too many characters before colon');
        assertBuildsAbsoluteUrl('k1dm:test', toAbsoluteUrl('k1dm:test'), 'we do not treat this one as pseudo-protocol url as it contains a number');

        locationAlias.pathname = '/test/';
        content.setLocation(locationAlias);
        assertBuildsAbsoluteUrl('?x=5', origin + '/test/?x=5', 'should add query param');
        assertBuildsAbsoluteUrl('link2', origin + '/test/link2', 'relative url in existing path');

        locationAlias.pathname = '/test';
        content.setLocation(locationAlias);
        assertBuildsAbsoluteUrl('?x=5', origin + '/test?x=5', 'should add query param');
        assertBuildsAbsoluteUrl('link2', origin + '/link2', 'relative url replaces other relative url');

        ok("test buildImpressionRequestParams(name, piece, target)");
        assertImpressionRequestParams('name', 'piece', 'target', 'c_n=name&c_p=piece&c_t=target', "all parameters set");
        assertImpressionRequestParams('name', 'piece', null, 'c_n=name&c_p=piece', "no target set");
        assertImpressionRequestParams('http://example.com.com', '/?x=1', '&target=1', 'c_n=http%3A%2F%2Fexample.com.com&c_p=%2F%3Fx%3D1&c_t=%26target%3D1', "should encode values");

        ok("test buildInteractionRequestParams(interaction, name, piece, target)");
        assertInteractionRequestParams(null, null, null, null, '', "nothing set");
        assertInteractionRequestParams('interaction', null, null, null, 'c_i=interaction', "only interaction set");
        assertInteractionRequestParams('interaction', 'name', null, null, 'c_i=interaction&c_n=name', "no piece and no target set");
        assertInteractionRequestParams('interaction', 'name', 'piece', null, 'c_i=interaction&c_n=name&c_p=piece', "no target set");
        assertInteractionRequestParams('interaction', 'name', 'piece', 'target', 'c_i=interaction&c_n=name&c_p=piece&c_t=target', "all parameters set");
        assertInteractionRequestParams(null, 'name', 'piece', null, 'c_n=name&c_p=piece', "only name and piece set");
        assertInteractionRequestParams('http://', 'http://example.com.com', '/?x=1', '&target=1', 'c_i=http%3A%2F%2F&c_n=http%3A%2F%2Fexample.com.com&c_p=%2F%3Fx%3D1&c_t=%26target%3D1', "should encode values");

        setupContentTrackingFixture('contentUtilities');

        ok("test shouldIgnoreInteraction(targetNode)");
        assertShouldIgnoreInteraction('ignoreInteraction1', 'should be ignored because of CSS class');
        assertShouldIgnoreInteraction('ignoreInteraction2', 'should be ignored because of Attribute');
        assertShouldIgnoreInteraction('ignoreInteraction3', 'should be ignored because of CSS class');
        assertShouldIgnoreInteraction('ignoreInteraction4', 'should be ignored because of Attribute');
        assertShouldNotIgnoreInteraction('notIgnoreInteraction1', 'should not be ignored');
        assertShouldNotIgnoreInteraction('notIgnoreInteraction2', 'should not be ignored as set in wrong element');


        ok("test isNodeAuthorizedToTriggerInteraction(targetNode)");
        strictEqual(tracker.isNodeAuthorizedToTriggerInteraction(), false, 'nothing set');
        strictEqual(tracker.isNodeAuthorizedToTriggerInteraction('#ignoreInteraction2'), false, 'no interacted node set');

        var notAuthIgnoreNode = '#ignoreInteraction2 a';
        assertNodeNotAuthorizedToTriggerInteraction(notAuthIgnoreNode, notAuthIgnoreNode, 'node has to be ignored');
        $(_s(notAuthIgnoreNode)).attr('data-content-ignoreinteraction', null);
        // node no longer ignored and it should be authorized!
        assertNodeAuthorizedToTriggerInteraction(notAuthIgnoreNode, notAuthIgnoreNode, 'node no longer has to be ignored');
        $(_s(notAuthIgnoreNode)).attr('data-content-ignoreinteraction', ''); // reset changed attribute

        assertNodeAuthorizedToTriggerInteraction('#authorized1', '#authorized1', 'interacted with target node which is content block');
        assertNodeAuthorizedToTriggerInteraction('#authorized1', '#authorized1_1', 'interacted with child of target node which is content block');
        assertNodeAuthorizedToTriggerInteraction('#authorized2', '#authorized2_1', 'interacted with target node');
        assertNodeAuthorizedToTriggerInteraction('#authorized2', '#authorized2_2', 'interacted with children of target node');
        assertNodeNotAuthorizedToTriggerInteraction('#authorized3', '#authorized3', 'interacted with content block but it is not target node');
        assertNodeNotAuthorizedToTriggerInteraction('#authorized3', '#authorized3_1', 'interacted with children of content block but not children of target node');
        assertNodeAuthorizedToTriggerInteraction('#authorized3', '#authorized3_2', 'interacted with target node to make sure auth3 is not ignored');


        ok("test setHrefAttribute(node, url)");
        var aElement = _e('aLinkToBeChanged');
        content.setHrefAttribute(); // should not fail if no arguments
        strictEqual(query.getAttributeValueFromNode(aElement, 'href'), 'http://www.example.com', 'setHrefAttribute, check initial link value');
        content.setHrefAttribute(aElement);
        content.setHrefAttribute(aElement, '');
        strictEqual(query.getAttributeValueFromNode(aElement, 'href'), 'http://www.example.com', 'setHrefAttribute, an empty URL should not be set');
        content.setHrefAttribute(aElement, '/test');
        strictEqual(query.getAttributeValueFromNode(aElement, 'href'), '/test', 'setHrefAttribute, link should be changed now');

        strictEqual(content.findMediaUrlInNode(), undefined, 'should not fail if no node passed');
        ok(_e('click1') && _e('mediaDiv'), 'make sure both nodes exist otherwise following two assertions to not test what we want');
        assertFoundMediaUrl('click1', undefined, 'should not find anything in a link as it is not a media');
        assertFoundMediaUrl('mediaDiv', undefined, 'should not find anything in a non media element even if it defines a src attribute');
        assertFoundMediaUrl('mediaImg', 'test/img.jpg', 'should find url of image');
        assertFoundMediaUrl('mediaVideo', 'movie.mp4', 'should find url of video, first one should be used');
        assertFoundMediaUrl('mediaAudio', 'audio.ogg', 'should find url of audio, first one should be used');
        assertFoundMediaUrl('mediaEmbed', 'embed.swf', 'should find url of embed element');
        assertFoundMediaUrl('mediaObjectSimple', 'objectSimple.swf', 'should find url of a simple object element');
        assertFoundMediaUrl('mediaObjectParam', 'movie_param1.swf', 'should find url of a simple object element');
        assertFoundMediaUrl('mediaObjectPdf', 'document.pdf', 'should find url of an object that contains non flash resources such as pdf');
        assertFoundMediaUrl('mediaObjectEmbed', 'document2.pdf', 'should fallback to an embed in an object');
    });

    test("contentVisibleNodeTests", function() {

        var tracker = Piwik.getTracker();
        var content = tracker.getContent();
        var actual;

        function _ex(testnumber) { // select node within content test scope
            return _s('#ex' + testnumber);
        }

        function assertContentNodeVisible(node, message)
        {
            scrollToTop(); // make sure content nodes are actually in view port

            if (!message) {
                message = '';
            }
            strictEqual(content.isNodeVisible(node), true, 'isNodeVisible, ' + message);
        }

        function assertContentNodeNotVisible(node, message)
        {
            scrollToTop(); // make sure content nodes are actually in view port

            if (!message) {
                message = '';
            }
            strictEqual(content.isNodeVisible(node), false, 'isNodeVisible, ' + message);
        }

        function assertInternalNodeVisible(node, message)
        {
            scrollToTop(); // make sure content nodes are actually in view port

            if (!message) {
                message = '';
            }
            strictEqual(tracker.internalIsNodeVisible(node), true, 'internalIsNodeVisible, ' + message);
        }

        function assertInternalNodeNotVisible(node, message)
        {
            scrollToTop(); // make sure content nodes are actually in view port

            if (!message) {
                message = '';
            }
            strictEqual(tracker.internalIsNodeVisible(node), false, 'internalNodeIsVisible, ' + message);
        }

        function assertNodeNotInViewport(node, message)
        {
            scrollToTop(); // make sure content nodes are actually in view port

            if (!message) {
                message = '';
            }
            strictEqual(content.isOrWasNodeInViewport(node), false, 'internalNodeNotVisible, ' + message);
        }

        function assertNodeIsInViewport(node, message)
        {
            scrollToTop(); // make sure content nodes are actually in view port

            if (!message) {
                message = '';
            }
            strictEqual(content.isOrWasNodeInViewport(node), true, 'internalIsNodeVisible, ' + message);

            window.scroll(0,200); // if we scroll done it was visible

            strictEqual(content.isOrWasNodeInViewport(node), true, 'internalIsNodeVisible, ' + message);
        }

        setupContentTrackingFixture('visibleNodes', document.body); // #contenttest is placed by default in #other but #other is not visible so all tests would return false.

        ok('test internalIsNodeVisible()');
        assertInternalNodeNotVisible(undefined, 'no node set, cannot be visible');
        assertInternalNodeNotVisible(_e('click1'), 'parent other is hidden');
        assertInternalNodeNotVisible(document.createElement('div'), 'element is not in DOM');
        assertInternalNodeVisible(_ex(1), 'node exists and should be visible');
        assertInternalNodeNotVisible(_ex(2), 'hidden via opacity');
        assertInternalNodeNotVisible(_ex(3), 'hidden via visibility');
        assertInternalNodeNotVisible(_ex(4), 'hidden via display');
        assertInternalNodeVisible(_ex(5), 'width is 0 but overflow can make it visible again?!?');
        assertInternalNodeVisible(_ex(6), 'height is 0 but overflow can make it visible again?!?');
        assertInternalNodeNotVisible(_ex(7), 'hidden via width:0, overflow is hidden');
        assertInternalNodeNotVisible(_ex(8), 'hidden via height:0, overflow is hidden');
        assertInternalNodeNotVisible(_ex(13), 'parent is hidden via opacity');
        assertInternalNodeNotVisible(_ex(14), 'parent is hidden via visibility');
        assertInternalNodeNotVisible(_ex(15), 'parent is hidden via display');
        assertInternalNodeNotVisible(_ex(16), 'parent is hidden via width:0, overflow is hidden');
        assertInternalNodeNotVisible(_ex(17), 'parent is hidden via height:0, overflow is hidden');

        assertNodeNotInViewport(_ex(18), 'element is not visible, ends directly at left:0px');

        assertInternalNodeVisible(_ex(19), 'element is visible by one px');
        assertNodeIsInViewport(_ex(19), 'element is visible by one px');

        assertNodeIsInViewport(_ex(20), 'element is position absolute and partially visible top');
        assertNodeIsInViewport(_ex(21), 'element is position absolute and partially visible left');
        assertNodeIsInViewport(_ex(22), 'element is position absolute and partially visible right');
        assertNodeIsInViewport(_ex(23), 'element is position absolute and partially visible bottom');
        assertNodeNotInViewport(_ex(24), 'element is position absolute and position too far top');
        assertNodeNotInViewport(_ex(25), 'element is position absolute and position too far left');
        assertNodeNotInViewport(_ex(26), 'element is position absolute and position too far right');
        assertNodeNotInViewport(_ex(27), 'element is position absolute and position too far bottom');

        assertNodeIsInViewport(_ex(28), 'element is position fixed and partially visible top');
        assertNodeIsInViewport(_ex(29), 'element is position fixed and partially visible left');
        assertNodeIsInViewport(_ex(30), 'element is position fixed and partially visible right');
        assertNodeIsInViewport(_ex(31), 'element is position fixed and partially visible bottom');
        assertNodeNotInViewport(_ex(32), 'element is position fixed and position too far top');
        assertNodeNotInViewport(_ex(33), 'element is position fixed and position too far left');

        assertNodeNotInViewport(_ex(34), 'element is position fixed and position too far right');
        assertNodeNotInViewport(_ex(35), 'element is position fixed and position too far bottom');


        assertInternalNodeVisible(_ex(37), 'element is within overflow scroll and it is visible');
        assertInternalNodeNotVisible(_ex(38), 'element is within overflow scroll but not visible');
        _ex(36).scrollTop = 35;_ex(36).scrolltop = 35; // scroll within div
        assertInternalNodeVisible(_ex(38), 'element is within overflow scroll but not visible');

        var nodesThatShouldBeInViewPort = [1,2,3,5,6,7,8,13,14,16,17];
        var index;
        for (index = 1; index < nodesThatShouldBeInViewPort.length; index++) {
            if (4 === index) {
                continue; // display:none will not be in view port
            }
            var exampleId = nodesThatShouldBeInViewPort[index];
            assertNodeIsInViewport(_ex(exampleId), 'example ' + exampleId + ' the nodes have to be in view port otherwise we might test something else than expected');
        }

        assertNodeNotInViewport(_ex(9), 'margin left position is so far left it cannot be visible');
        assertNodeNotInViewport(_ex(10), 'margin left position is so far right it cannot be visible');


        assertContentNodeNotVisible(undefined, 'no node set');
        assertContentNodeNotVisible(_ex(3), 'element is not visible but in viewport');
        assertContentNodeNotVisible(_ex(18), 'element is visible but not viewport');
        assertContentNodeNotVisible(_ex(4), 'element is neither visible nor in viewport');
        assertContentNodeVisible(_ex(19), 'element is visible and in viewport');
    });

    test("contentFindContentValues", function() {

        function _st(id) {
            return id && (''+id) === id ? _s('#' + id) : id;
        }

        function assertFoundContent(id, expectedName, expectedPiece, expectedTarget, message) {
            var node = _st(id)
            if (!message) {
                message = 'Id: ' + id;
            }

            strictEqual(content.findContentTarget(node), expectedTarget, 'findContentTarget, ' + message + ', expected ' + expectedTarget);
            strictEqual(content.findContentPiece(node), expectedPiece, 'findContentPiece, ' + message + ', expected ' + expectedPiece);
            strictEqual(content.findContentName(node), expectedName, 'findContentName, ' + message + ', expected ' + expectedName);
        }

        function buildContentStruct(name, piece, target) {
            return {
                name: name,
                piece: piece,
                target: target
            };
        }

        function assertBuiltContent(id, expectedName, expectedPiece, expectedTarget, message) {
            var node = _st(id);
            if (!message) {
                message = 'Id: ' + id;
            }

            var expected = buildContentStruct(expectedName, expectedPiece, expectedTarget);

            propEqual(content.buildContentBlock(node), expected, 'buildContentBlock, ' + message);
        }

        function assertCollectedContent(ids, expected, message) {
            var nodes = [];
            var index;
            for (index = 0; index < ids.length; index++) {
                nodes.push(_st(ids[index]));
            }

            if (!message) {
                message = 'Id: ' + id;
            }

            propEqual(content.collectContent(nodes), expected, 'collectContent  , ' + message);
        }

        var tracker = Piwik.getTracker();
        var content = tracker.getContent();
        content.setLocation();
        var actual;

        setupContentTrackingFixture('manyExamples');

        var origin = getOrigin();
        var host   = location.host;
        var path   = origin + location.pathname;

        assertFoundContent(undefined, undefined, undefined, undefined, 'No node set');
        assertFoundContent('ex1', toAbsolutePath('img-en.jpg'), toAbsoluteUrl('img-en.jpg'), undefined);
        assertFoundContent('ex2', toAbsolutePath('img-en.jpg'), toAbsoluteUrl('img-en.jpg'), undefined);
        assertFoundContent('ex3', 'img.jpg', 'img.jpg', 'http://www.example.com');
        assertFoundContent('ex4', toAbsolutePath('img-en.jpg'), toAbsoluteUrl('img-en.jpg'), 'http://www.example.com');
        assertFoundContent('ex5', toAbsolutePath('img-en.jpg'), toAbsoluteUrl('img-en.jpg'), 'http://www.example.com');
        assertFoundContent('ex6', undefined, undefined, 'http://www.example.com');
        assertFoundContent('ex7', undefined, undefined, 'http://www.example.com');
        assertFoundContent('ex8', 'My content', 'My content', 'http://www.example.com');
        assertFoundContent('ex9', 'Image1', toAbsoluteUrl('img-en.jpg'), undefined);
        assertFoundContent('ex10', 'http://www.example.com/path/img-en.jpg', 'http://www.example.com/path/img-en.jpg', undefined);
        assertFoundContent('ex11', undefined, undefined, 'http://www.example.com');
        assertFoundContent('ex12', 'Block Title', undefined, 'http://www.example.com');
        assertFoundContent('ex13', undefined, undefined, 'http://manual.example.com');
        assertFoundContent('ex14', undefined, undefined, undefined);
        assertFoundContent('ex15', undefined, undefined, 'http://attr.example.com');
        assertFoundContent('ex16', undefined, undefined, 'http://www.example.com');
        assertFoundContent('ex17', undefined, undefined, 'http://www.example.com');
        assertFoundContent('ex18', 'My Ad', 'http://www.example.com/path/xyz.jpg', origin + '/anylink');
        assertFoundContent('ex19', 'http://www.example.com/path/xyz.jpg', 'http://www.example.com/path/xyz.jpg', 'http://ad.example.com');

        // test removal of domain if url === current domain
        var newUrl = origin + '/path/xyz.jpg';
        $(_s('#ex19 img')).attr('src', newUrl);
        assertFoundContent('ex19', '/path/xyz.jpg', newUrl, 'http://ad.example.com', 'Should remove domain if the same as current');

        newUrl = 'http://' + host + '/path/xyz.jpg';
        $(_s('#ex19 img')).attr('src', newUrl);
        assertFoundContent('ex19', '/path/xyz.jpg', newUrl, 'http://ad.example.com', 'Should remove domain if the same as current');

        newUrl = 'https://' + host + '/path/xyz.jpg';
        $(_s('#ex19 img')).attr('src', newUrl);
        assertFoundContent('ex19', '/path/xyz.jpg', newUrl, 'http://ad.example.com', 'Should remove domain if the same as current');

        assertFoundContent('ex20', 'My Ad', undefined, 'http://ad.example.com');
        assertFoundContent('ex21', 'Block Title', undefined, 'http://www.example.com');
        assertFoundContent('ex22', 'Piece Title', undefined, 'http://www.example.com');
        assertFoundContent('ex23', 'Target Title', undefined, 'http://www.example.com');
        assertFoundContent('ex24', 'Piece Title', undefined, 'http://target.example.com');
        assertFoundContent('ex25', 'My Ad', 'http://www.example.com/path/xyz.jpg', origin + '/anylink');
        assertFoundContent('ex26', 'My Ad', undefined, 'http://fallback.example.com');
        assertFoundContent('ex27', 'My Ad', undefined, origin + '/test');
        assertFoundContent('ex28', 'My Ad', undefined, toAbsoluteUrl('test'));
        assertFoundContent('ex29', 'My Video', toAbsoluteUrl('movie.mp4'), 'videoplayer');
        assertFoundContent('ex30', toAbsolutePath('audio.ogg'), toAbsoluteUrl('audio.ogg'), 'audioplayer');
        assertFoundContent('ex31', '   name   ', '   pie ce  ', '  targ et  ', 'Should not trim');


        ok('test buildContentBlock(node)');
        strictEqual(content.buildContentBlock(), undefined, 'no node set');
        assertBuiltContent('ex31', 'name', 'pie ce', 'targ et', 'Should trim values');
        assertBuiltContent('ex30', toAbsolutePath('audio.ogg'), toAbsoluteUrl('audio.ogg'), 'audioplayer', 'All values set');
        assertBuiltContent(_e('div1'), 'Unknown', 'Unknown', '', 'It is not a content block, so should use defaults');
        assertBuiltContent('ex1', toAbsolutePath('img-en.jpg'), toAbsoluteUrl('img-en.jpg'), '', 'Should use default for target');
        assertBuiltContent('ex12', 'Block Title', 'Unknown', 'http://www.example.com', 'Should use default for piece');
        assertBuiltContent('ex15', 'Unknown', 'Unknown', 'http://attr.example.com', 'Should use default for name and piece');


        ok('test collectContent(node)');
        propEqual(content.collectContent(), [], 'no node set should still return array');

        var expected = [
            buildContentStruct('name', 'pie ce', 'targ et'),
            buildContentStruct(toAbsolutePath('audio.ogg'), toAbsoluteUrl('audio.ogg'), 'audioplayer'),
            buildContentStruct('Unknown', 'Unknown', ''),
            buildContentStruct(toAbsolutePath('img-en.jpg'), toAbsoluteUrl('img-en.jpg'), ''),
            buildContentStruct('Block Title', 'Unknown', 'http://www.example.com'),
            buildContentStruct('Unknown', 'Unknown', 'http://attr.example.com'),
        ];
        var ids = ['ex31', 'ex30', _e('div1'), 'ex1', 'ex12', 'ex15'];
        assertCollectedContent(ids, expected, 'should collect all content, make sure it trims values and it uses default values');
    });

    test("ContentTrackerInternals", function() {
        expect(111);
        var tracker = Piwik.getTracker();
        var actual, expected, trackerUrl;

        var impression = {name: 'name', piece: '5', target: 'target'};

        var origin = getOrigin();
        var originEncoded = window.encodeURIComponent(origin);

        function assertTrackingRequest(actual, expectedStartsWith, message)
        {
            if (!message) {
                message = '';
            } else {
                message += ', ';
            }

            strictEqual(actual.indexOf(expectedStartsWith), 0, message + " " +  actual + ' should start with ' + expectedStartsWith);

            var expectedString = '&ca=1&idsite=1&rec=1';
            strictEqual(actual.indexOf(expectedString), expectedStartsWith.length, 'did not find ' + expectedString + ' in ' + actual);
            // make sure it contains all those other tracking stuff directly afterwards so we can assume it did append
            // the other request stuff and we also make sure to compare the whole custom string as we check from
            // expectedStartsWith.length
        }

        ok('test buildContentImpressionRequest()');
        actual = tracker.buildContentImpressionRequest();
        assertTrackingRequest(actual, 'c_n=undefined&c_p=undefined', 'nothing set');
        actual = tracker.buildContentImpressionRequest('name', 'piece');
        assertTrackingRequest(actual, 'c_n=name&c_p=piece', 'only name and piece');
        actual = tracker.buildContentImpressionRequest('name', 'piece', 'target');
        assertTrackingRequest(actual, 'c_n=name&c_p=piece&c_t=target');
        actual = tracker.buildContentImpressionRequest('name://', 'x=5', '?x=5');
        assertTrackingRequest(actual, 'c_n=name%3A%2F%2F&c_p=x%3D5&c_t=%3Fx%3D5', 'should encode values');

        ok('test buildContentInteractionRequest()');
        actual = tracker.buildContentInteractionRequest();
        strictEqual(actual, undefined, 'nothing set should not build request');
        actual = tracker.buildContentInteractionRequest('interaction');
        assertTrackingRequest(actual, 'c_i=interaction');
        actual = tracker.buildContentInteractionRequest('interaction', 'name', 'piece');
        assertTrackingRequest(actual, 'c_i=interaction&c_n=name&c_p=piece');
        actual = tracker.buildContentInteractionRequest('interaction', 'name', 'piece', 'target');
        assertTrackingRequest(actual, 'c_i=interaction&c_n=name&c_p=piece&c_t=target', 'all params');
        actual = tracker.buildContentInteractionRequest('interaction://', 'name://', 'p?=iece', 'tar=get');
        assertTrackingRequest(actual, 'c_i=interaction%3A%2F%2F&c_n=name%3A%2F%2F&c_p=p%3F%3Diece&c_t=tar%3Dget', 'should encode');


        setupContentTrackingFixture('manyExamples');


        ok('test buildContentInteractionRequestNode()');
        actual = tracker.buildContentInteractionRequestNode();
        strictEqual(actual, undefined, 'nothing set should not build request');

        actual = tracker.buildContentInteractionRequestNode(_e('div1'));
        strictEqual(actual, undefined, 'does not contain a content block, should not build anything');

        actual   = tracker.buildContentInteractionRequestNode(_e('ex18'));
        expected = 'c_i=Unknown&c_n=My%20Ad&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t=' + originEncoded + '%2Fanylink';
        assertTrackingRequest(actual, expected, 'no interaction set should default to unknown and recognize all other values');

        actual   = tracker.buildContentInteractionRequestNode(_e('ex18'), 'CustomInteraction://');
        expected = 'c_i=CustomInteraction%3A%2F%2F&c_n=My%20Ad&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t=' + originEncoded + '%2Fanylink';
        assertTrackingRequest(actual, expected, 'custom interaction');

        actual   = tracker.buildContentInteractionRequestNode($('#ex18 a')[0], 'CustomInteraction://');
        expected = 'c_i=CustomInteraction%3A%2F%2F&c_n=My%20Ad&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t=' + originEncoded + '%2Fanylink';
        assertTrackingRequest(actual, expected, 'should automatically find parent and search for content from there');

        ok('test wasContentImpressionAlreadyTracked()');
        actual = tracker.wasContentImpressionAlreadyTracked(impression);
        strictEqual(actual, false, 'wasContentImpressionAlreadyTracked, content impression was not tracked before');
        tracker.buildContentImpressionsRequests([impression], []);
        actual = tracker.wasContentImpressionAlreadyTracked(impression);
        strictEqual(actual, true, 'wasContentImpressionAlreadyTracked, should be marked as already tracked now');
        actual = tracker.wasContentImpressionAlreadyTracked({name: 'name', piece: 5, target: 'target'});
        strictEqual(actual, false, 'wasContentImpressionAlreadyTracked, should compare with === equal parameter');
        tracker.trackPageView();
        actual = tracker.wasContentImpressionAlreadyTracked(impression);
        strictEqual(actual, false, 'wasContentImpressionAlreadyTracked, trackPageView should reset tracked impressions');

        setupContentTrackingFixture('trackerInternals');

        ok('test appendContentInteractionToRequestIfPossible()');
        ok(_e('notClickedTargetNode') && _e('ignoreInteraction2') && _e('ignoreInteraction1') && _e('click1') && _s('#ex103') && _s('#ex104'),
            'Make sure the nodes we are using for testing actually exist. Otherwise tests would be useless');
        actual = tracker.appendContentInteractionToRequestIfPossible();
        strictEqual(actual, undefined, 'appendContentInteractionToRequestIfPossible, nothing set');
        actual = tracker.appendContentInteractionToRequestIfPossible(_e('click1'));
        strictEqual(actual, undefined, 'appendContentInteractionToRequestIfPossible, no content block');
        actual = tracker.appendContentInteractionToRequestIfPossible(_e('ignoreInteraction1'));
        strictEqual(actual, undefined, 'appendContentInteractionToRequestIfPossible, contains block but should be ignored in target node');
        actual = tracker.appendContentInteractionToRequestIfPossible(_e('ignoreInteraction2'));
        strictEqual(actual, undefined, 'appendContentInteractionToRequestIfPossible, contains block but should be ignored in block node as no target node');
        actual = tracker.appendContentInteractionToRequestIfPossible(_e('notClickedTargetNode'));
        strictEqual(actual, undefined, 'appendContentInteractionToRequestIfPossible, not a node within target node was clicked');
        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex103'));
        strictEqual(actual, undefined, 'appendContentInteractionToRequestIfPossible, the content block node was clicked but it is not the target');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex104'));
        strictEqual(actual, 'c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&ca=1', 'appendContentInteractionToRequestIfPossible, the actual target node was clicked');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex104'), 'clicki');
        strictEqual(actual, 'c_i=clicki&c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&ca=1', 'appendContentInteractionToRequestIfPossible, with interaction');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex104_inner'));
        strictEqual(actual, 'c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&ca=1', 'appendContentInteractionToRequestIfPossible, block node is target node and any node within it was clicked which is good, we build a request');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex104_inner'));
        strictEqual(actual, 'c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&ca=1', 'appendContentInteractionToRequestIfPossible, a node within a target node was clicked which is googd');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex105_target'));
        strictEqual(actual, 'c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&c_t=http%3A%2F%2Fwww.example.com&ca=1', 'appendContentInteractionToRequestIfPossible, target node was clicked which is good');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex105_withinTarget'));
        strictEqual(actual, 'c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&c_t=http%3A%2F%2Fwww.example.com&ca=1', 'appendContentInteractionToRequestIfPossible, a node within target node was clicked which is googd');

        actual = tracker.appendContentInteractionToRequestIfPossible(_s('#ex104_inner'), 'click', 'fallbacktarget');
        strictEqual(actual, 'c_i=click&c_n=' + toEncodedAbsolutePath('img.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img.jpg') + '&c_t=fallbacktarget&ca=1', 'appendContentInteractionToRequestIfPossible, if no target found we can specify a default target');



        ok('test setupInteractionsTracking()');
        actual = tracker.setupInteractionsTracking();
        strictEqual(actual, undefined, 'setupInteractionsTracking, no nodes set');
        actual = tracker.setupInteractionsTracking([_s('#ex106'), _s('#ex107')]);
        strictEqual(_s('#ex106_target').contentInteractionTrackingSetupDone, true, 'setupInteractionsTracking, should add event to target node');
        strictEqual(_s('#ex107').contentInteractionTrackingSetupDone, true, 'setupInteractionsTracking, should add event to block node if no target node specified');


        ok('test trackContentImpressionClickInteraction()');

        trackerUrl = tracker.getTrackerUrl();
        tracker.setTrackerUrl('matomo.php');
        tracker.disableLinkTracking();

        ok(_s('#ignoreInteraction1') && _s('#ex108') && _s('#ex109'), 'make sure node exists otherwise test is useless');
        actual = (tracker.trackContentImpressionClickInteraction())();
        strictEqual(actual, undefined, 'trackContentImpressionClickInteraction, no target node set');
        actual = (tracker.trackContentImpressionClickInteraction(_s('#ignoreInteraction1')))({target: _s('#ignoreInteraction1')});
        strictEqual(actual, undefined, 'trackContentImpressionClickInteraction, no target node set');

        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex108')))({target: _s('#ex108')});
        assertTrackingRequest(actual, 'c_i=click&c_n=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t=http%3A%2F%2Fad.example.com', 'trackContentImpressionClickInteraction, is outlink but should use xhr as link tracking not enabled');
        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex109')))({target: _s('#ex109')});
        assertTrackingRequest(actual, 'c_i=click&c_n=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t='+originEncoded+'%2Ffile.pdf', 'trackContentImpressionClickInteraction, is internal download but should use href as link tracking not enabled');

        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex110')))({target: _s('#ex110')});
        assertTrackingRequest(actual, 'c_i=click&c_n=MyName&c_p=img.jpg&c_t=' + originEncoded + '%2Fexample', 'trackContentImpressionClickInteraction, should be tracked using redirect');

        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex112')))({target: _s('#ex112')});
        assertTrackingRequest(actual, 'c_i=click&c_n=img.jpg&c_p=img.jpg&c_t=' + toEncodedAbsoluteUrl('#example'), 'trackContentImpressionClickInteraction, a link that is an anchor should be tracked as XHR and no redirect');

        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex113_target')))({target: _s('#ex113_target')});
        assertTrackingRequest(actual, 'c_i=click&c_n=img.jpg&c_p=img.jpg', 'trackContentImpressionClickInteraction, if element is not A or AREA it should always use xhr');

        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex114')))({target: _s('#ex114')});
        assertTrackingRequest(actual, 'c_i=click&c_n=imgnohref.jpg&c_p=imgnohref.jpg&c_t=%2Ftest', 'trackContentImpressionClickInteraction, if element is an A or AREA element but has no href attribute it should always use xhr');

        tracker.enableLinkTracking();

        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex108')))({target: _s('#ex108')});
        strictEqual(actual, 'link', 'trackContentImpressionClickInteraction, should not track as is an outlink and link tracking enabled');
        $(_s('#ex109')).attr('href', '/file.pdf'); // reset download link as was replaced with matomo.php
        actual = (tracker.trackContentImpressionClickInteraction(_s('#ex109')))({target: _s('#ex109')});
        strictEqual(actual, 'download', 'trackContentImpressionClickInteraction, should not track as is a download and link tracking enabled');

        tracker.disableLinkTracking();
        tracker.setTrackerUrl(trackerUrl);


        ok('test buildContentImpressionsRequests()');
        ok(impression, 'we should have an impression');
        tracker.clearTrackedContentImpressions();

        actual = tracker.buildContentImpressionsRequests();
        propEqual(actual, [], 'buildContentImpressionsRequests, nothing set');
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'buildContentImpressionsRequests, tracked impressions should be empty');

        actual = tracker.buildContentImpressionsRequests([impression]);
        propEqual(tracker.getTrackedContentImpressions(), [impression], 'buildContentImpressionsRequests, should have marked content as tracked');

        actual = tracker.buildContentImpressionsRequests([impression]);
        propEqual(actual, [], 'buildContentImpressionsRequests, nothing tracked as supposed to be ignored');
        propEqual(tracker.getTrackedContentImpressions(), [impression], 'buildContentImpressionsRequests, impression should be ignored as it was already tracked before');

        tracker.trackSiteSearch()
        propEqual(tracker.getTrackedContentImpressions(), [], 'trackSiteSearch, resets tracked impressions');

        tracker.clearTrackedContentImpressions();
        _s('#ignoreInteraction1').contentInteractionTrackingSetupDone = false;
        tracker.buildContentImpressionsRequests([impression], [_s('#ex101')]);
        strictEqual(_s('#ignoreInteraction1').contentInteractionTrackingSetupDone, true, 'buildContentImpressionsRequests, should trigger setup of interaction tracking');

        tracker.clearTrackedContentImpressions();
        actual = tracker.buildContentImpressionsRequests([impression], [_s('#ex101')]);
        strictEqual(actual.length, 1, 'buildContentImpressionsRequests, should generate a request for one request');
        assertTrackingRequest(actual[0], 'c_n=name&c_p=5&c_t=target');

        tracker.clearTrackedContentImpressions();
        var impression2 = {name: 'name2', piece: 'piece2', target: 'http://www.example.com'};
        var impression3 = {name: 'name3', piece: 'piece3', target: 'Anything'};

        actual = tracker.buildContentImpressionsRequests([impression, impression, impression2, impression, impression3], [_s('#ex101')]);
        strictEqual(actual.length, 3, 'buildContentImpressionsRequests, should be only 3 requests as one impression was there twice and should be ignored once');
        assertTrackingRequest(actual[0], 'c_n=name&c_p=5&c_t=target');
        assertTrackingRequest(actual[1], 'c_n=name2&c_p=piece2&c_t=http%3A%2F%2Fwww.example.com');
        assertTrackingRequest(actual[2], 'c_n=name3&c_p=piece3&c_t=Anything');


        setupContentTrackingFixture('manyExamples');


        ok('test getContentImpressionsRequestsFromNodes()');
        actual = tracker.getContentImpressionsRequestsFromNodes();
        propEqual(actual, [], 'getContentImpressionsRequestsFromNodes, no nodes set');

        tracker.clearTrackedContentImpressions();
        actual = tracker.getContentImpressionsRequestsFromNodes([undefined, null]);
        propEqual(actual, [], 'getContentImpressionsRequestsFromNodes, no nodes set that are actually content nodes');

        tracker.clearTrackedContentImpressions();
        actual = tracker.getContentImpressionsRequestsFromNodes([_s('#ex1'), _s('#ex2')]);
        strictEqual(actual.length, 1, 'getContentImpressionsRequestsFromNodes, should ignore a duplicated node that has same content');
        assertTrackingRequest(actual[0], 'c_n=' + toEncodedAbsolutePath('img-en.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img-en.jpg'));

        tracker.clearTrackedContentImpressions();
        actual = tracker.getContentImpressionsRequestsFromNodes([_s('#ex1'), undefined, _s('#ex2'), _s('#ex8'), _s('#ex19')]);
        strictEqual(actual.length, 3, 'getContentImpressionsRequestsFromNodes, should only build requests for nodes that are content nodes');
        assertTrackingRequest(actual[0], 'c_n=' + toEncodedAbsolutePath('img-en.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img-en.jpg'));
        assertTrackingRequest(actual[1], 'c_n=My%20content&c_p=My%20content&c_t=http%3A%2F%2Fwww.example.com');
        assertTrackingRequest(actual[2], 'c_n=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t=http%3A%2F%2Fad.example.com');

        setupContentTrackingFixture('trackerInternals', document.body);

        ok('test getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet()');

        actual = tracker.getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet();
        propEqual(actual, [], 'getVisibleImpressions, no nodes set');

        _s('#ex115').scrollIntoView(true);
        tracker.clearTrackedContentImpressions();
        actual = tracker.getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet([_s('#ex116_hidden')]);
        propEqual(actual, [], 'getVisibleImpressions, if all are hidden should not return anything');

        _s('#ex115').scrollIntoView(true);
        tracker.clearTrackedContentImpressions();
        actual = tracker.getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet([_s('#ex115'),_s('#ex115'),  _s('#ex116_hidden')]);
        strictEqual(actual.length, 1, 'getVisibleImpressions, should not ignore the found requests but the visible ones, should not add the same one twice');
        assertTrackingRequest(actual[0], 'c_n=' + toEncodedAbsolutePath('img115.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img115.jpg') + '&c_t=http%3A%2F%2Fwww.example.com');

        _s('#ex115').scrollIntoView(true);
        tracker.clearTrackedContentImpressions();
        actual = tracker.getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet([_s('#ex116_hidden'), _s('#ex116_hidden'), _s('#ex115'),_s('#ex115')]);
        strictEqual(actual.length, 1, 'getVisibleImpressions, two hidden ones before a visible ones to make sure removing hidden content block from array works and does not ignore one');
        assertTrackingRequest(actual[0], 'c_n=' + toEncodedAbsolutePath('img115.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img115.jpg') + '&c_t=http%3A%2F%2Fwww.example.com');

        removeContentTrackingFixture();
    });

    test("Basic requirements", function() {
        expect(4);

        equal( typeof encodeURIComponent, 'function', 'encodeURIComponent' );
        ok( RegExp, "RegExp" );
        ok( Piwik, "Piwik" );
        ok( Matomo, "Matomo" );
    });

    test("Test API - addPlugin(), getTracker(), getHook(), and hook", function() {
        expect(6);

        ok( Piwik.addPlugin, "Piwik.addPlugin" );

        var tracker = Piwik.getTracker();

        equal( typeof tracker, 'object', "Piwik.getTracker()" );
        equal( typeof tracker.getHook, 'function', "test Tracker getHook" );
        equal( typeof tracker.hook, 'object', "test Tracker hook" );
        equal( typeof tracker.getHook('test'), 'object', "test Tracker getHook('test')" );
        equal( typeof tracker.hook.test, 'object', "test Tracker hook.test" );
    });

    test("API methods", function() {
        expect(125);

        equal( typeof Piwik.addPlugin, 'function', 'addPlugin' );
        equal( typeof Piwik.addPlugin, 'function', 'addTracker' );
        equal( typeof Piwik.getTracker, 'function', 'getTracker' );
        equal( typeof Piwik.getAsyncTracker, 'function', 'getAsyncTracker' );
        strictEqual( Piwik, Matomo, 'Piwik === Matomo' );

        var tracker;

        tracker = Piwik.getAsyncTracker();
        ok(tracker instanceof Object, 'getAsyncTracker');

        tracker = Piwik.getTracker();
        ok(tracker instanceof Object, 'getTracker');

        equal( typeof tracker.getVisitorId, 'function', 'getVisitorId' );
        equal( typeof tracker.getVisitorInfo, 'function', 'getVisitorInfo' );
        equal( typeof tracker.getAttributionInfo, 'function', 'getAttributionInfo' );
        equal( typeof tracker.getAttributionReferrerTimestamp, 'function', 'getAttributionReferrerTimestamp' );
        equal( typeof tracker.getAttributionReferrerUrl, 'function', 'getAttributionReferrerUrl' );
        equal( typeof tracker.getAttributionCampaignName, 'function', 'getAttributionCampaignName' );
        equal( typeof tracker.getAttributionCampaignKeyword, 'function', 'getAttributionCampaignKeyword' );
        equal( typeof tracker.setTrackerUrl, 'function', 'setTrackerUrl' );
        equal( typeof tracker.getPiwikUrl, 'function', 'getPiwikUrl' );
        equal( typeof tracker.getCurrentUrl, 'function', 'getCurrentUrl' );
        equal( typeof tracker.getRequest, 'function', 'getRequest' );
        equal( typeof tracker.addPlugin, 'function', 'addPlugin' );
        equal( typeof tracker.resetUserId, 'function', 'resetUserId' );
        equal( typeof tracker.setUserId, 'function', 'setUserId' );
        equal( typeof tracker.setSiteId, 'function', 'setSiteId' );
        equal( typeof tracker.setVisitorId, 'function', 'setVisitorId' );
        equal( typeof tracker.setCustomData, 'function', 'setCustomData' );
        equal( typeof tracker.getCustomData, 'function', 'getCustomData' );
        equal( typeof tracker.setCustomRequestProcessing, 'function', 'setCustomRequestProcessing' );
        equal( typeof tracker.setCustomDimension, 'function', 'setCustomDimension' );
        equal( typeof tracker.getCustomDimension, 'function', 'getCustomDimension' );
        equal( typeof tracker.deleteCustomDimension, 'function', 'deleteCustomDimension' );
        equal( typeof tracker.setCustomVariable, 'function', 'setCustomVariable' );
        equal( typeof tracker.getCustomVariable, 'function', 'getCustomVariable' );
        equal( typeof tracker.deleteCustomVariable, 'function', 'deleteCustomVariable' );
        equal( typeof tracker.deleteCustomVariables, 'function', 'deleteCustomVariables' );
        equal( typeof tracker.setLinkTrackingTimer, 'function', 'setLinkTrackingTimer' );
        equal( typeof tracker.getLinkTrackingTimer, 'function', 'getLinkTrackingTimer' );
        equal( typeof tracker.alwaysUseSendBeacon, 'function', 'alwaysUseSendBeacon' );
        equal( typeof tracker.disableAlwaysUseSendBeacon, 'function', 'disableAlwaysUseSendBeacon' );
        equal( typeof tracker.setDownloadExtensions, 'function', 'setDownloadExtensions' );
        equal( typeof tracker.addDownloadExtensions, 'function', 'addDownloadExtensions' );
        equal( typeof tracker.removeDownloadExtensions, 'function', 'removeDownloadExtensions' );
        equal( typeof tracker.setDomains, 'function', 'setDomains' );
        equal( typeof tracker.enableCrossDomainLinking, 'function', 'enableCrossDomainLinking' );
        equal( typeof tracker.disableCrossDomainLinking, 'function', 'disableCrossDomainLinking' );
        equal( typeof tracker.isCrossDomainLinkingEnabled, 'function', 'isCrossDomainLinkingEnabled' );
        equal( typeof tracker.setCrossDomainLinkingTimeout, 'function', 'isCrossDomainLinkingEnabled' );
        equal( typeof tracker.getCrossDomainLinkingUrlParameter, 'function', 'getCrossDomainLinkingUrlParameter');
        equal( typeof tracker.setIgnoreClasses, 'function', 'setIgnoreClasses' );
        equal( typeof tracker.setRequestMethod, 'function', 'setRequestMethod' );
        equal( typeof tracker.setRequestContentType, 'function', 'setRequestContentType' );
        equal( typeof tracker.setGenerationTimeMs, 'function', 'setGenerationTimeMs' );
        equal( typeof tracker.setReferrerUrl, 'function', 'setReferrerUrl' );
        equal( typeof tracker.setExcludedReferrers, 'function', 'setExcludedReferrers' );
        equal( typeof tracker.getExcludedReferrers, 'function', 'getExcludedReferrers' );
        equal( typeof tracker.setCustomUrl, 'function', 'setCustomUrl' );
        equal( typeof tracker.setDocumentTitle, 'function', 'setDocumentTitle' );
        equal( typeof tracker.setDownloadClasses, 'function', 'setDownloadClasses' );
        equal( typeof tracker.setLinkClasses, 'function', 'setLinkClasses' );
        equal( typeof tracker.setCampaignNameKey, 'function', 'setCampaignNameKey' );
        equal( typeof tracker.disablePerformanceTracking, 'function', 'disablePerformanceTracking' );
        equal( typeof tracker.setCampaignKeywordKey, 'function', 'setCampaignKeywordKey' );
        equal( typeof tracker.discardHashTag, 'function', 'discardHashTag' );
        equal( typeof tracker.setExcludedQueryParams, 'function', 'setExcludedQueryParams' );
        equal( typeof tracker.setCookieNamePrefix, 'function', 'setCookieNamePrefix' );
        equal( typeof tracker.setCookieDomain, 'function', 'setCookieDomain' );
        equal( typeof tracker.setCookiePath, 'function', 'setCookiePath' );
        equal( typeof tracker.setSessionCookie, 'function', 'setSessionCookie' );
        equal( typeof tracker.setSecureCookie, 'function', 'setSecureCookie' );
        equal( typeof tracker.setCookieSameSite, 'function', 'setCookieSameSite' );
        equal( typeof tracker.getCookie, 'function', 'getCookie' );
        equal( typeof tracker.hasCookies, 'function', 'hasCookies' );
        equal( typeof tracker.getCookiePath, 'function', 'getCookiePath' );
        equal( typeof tracker.getSessionCookieTimeout, 'function', 'getSessionCookieTimeout' );
        equal( typeof tracker.setVisitorCookieTimeout, 'function', 'setVisitorCookieTimeout' );
        equal( typeof tracker.setSessionCookieTimeout, 'function', 'setSessionCookieTimeout' );
        equal( typeof tracker.setReferralCookieTimeout, 'function', 'setReferralCookieTimeout' );
        equal( typeof tracker.setConversionAttributionFirstReferrer, 'function', 'setConversionAttributionFirstReferrer' );
        equal( typeof tracker.addListener, 'function', 'addListener' );
        equal( typeof tracker.enableLinkTracking, 'function', 'enableLinkTracking' );
        equal( typeof tracker.enableHeartBeatTimer, 'function', 'enableHeartBeatTimer' );
        equal( typeof tracker.disableHeartBeatTimer, 'function', 'disableHeartBeatTimer' );
        equal( typeof tracker.killFrame, 'function', 'killFrame' );
        equal( typeof tracker.redirectFile, 'function', 'redirectFile' );
        equal( typeof tracker.setCountPreRendered, 'function', 'setCountPreRendered' );
        equal( typeof tracker.trackGoal, 'function', 'trackGoal' );
        equal( typeof tracker.trackLink, 'function', 'trackLink' );
        equal( typeof tracker.getNumTrackedPageViews, 'function', 'getNumTrackedPageViews' );
        equal( typeof tracker.trackPageView, 'function', 'trackPageView' );
        equal( typeof tracker.trackRequest, 'function', 'trackRequest' );
        equal( typeof tracker.queueRequest, 'function', 'queueRequest' );
        equal( typeof tracker.disableQueueRequest, 'function', 'disableQueueRequest' );
        equal( typeof tracker.setRequestQueueInterval, 'function', 'setRequestQueueInterval' );
        equal( typeof tracker.disableCookies, 'function', 'disableCookies' );
        equal( typeof tracker.setCookieConsentGiven, 'function', 'setCookieConsentGiven' );
        equal( typeof tracker.areCookiesEnabled, 'function', 'areCookiesEnabled' );
        equal( typeof tracker.deleteCookies, 'function', 'deleteCookies' );
        equal( typeof tracker.requireCookieConsent, 'function', 'requireCookieConsent' );
        equal( typeof tracker.getRememberedCookieConsent, 'function', 'getRememberedCookieConsent' );
        equal( typeof tracker.rememberCookieConsentGiven, 'function', 'rememberCookieConsentGiven' );
        equal( typeof tracker.forgetCookieConsentGiven, 'function', 'forgetCookieConsentGiven' );
        // content
        equal( typeof tracker.trackAllContentImpressions, 'function', 'trackAllContentImpressions' );
        equal( typeof tracker.trackVisibleContentImpressions, 'function', 'trackVisibleContentImpressions' );
        equal( typeof tracker.trackContentImpression, 'function', 'trackContentImpression' );
        equal( typeof tracker.trackContentImpressionsWithinNode, 'function', 'trackContentImpressionsWithinNode' );
        equal( typeof tracker.trackContentInteraction, 'function', 'trackContentInteraction' );
        equal( typeof tracker.trackContentInteractionNode, 'function', 'trackContentInteractionNode' );
        equal( typeof tracker.logAllContentBlocksOnPage, 'function', 'logAllContentBlocksOnPage' );
        // ecommerce
        equal( typeof tracker.setEcommerceView, 'function', 'setEcommerceView' );
        equal( typeof tracker.getEcommerceItems, 'function', 'getEcommerceItems' );
        equal( typeof tracker.addEcommerceItem, 'function', 'addEcommerceItem' );
        equal( typeof tracker.removeEcommerceItem, 'function', 'removeEcommerceItem' );
        equal( typeof tracker.clearEcommerceCart, 'function', 'clearEcommerceCart' );
        equal( typeof tracker.trackEcommerceOrder, 'function', 'trackEcommerceOrder' );
        equal( typeof tracker.trackEcommerceCartUpdate, 'function', 'trackEcommerceCartUpdate' );
        // consent
        equal( typeof tracker.getRememberedConsent, 'function', 'getRememberedConsent' );
        equal( typeof tracker.hasRememberedConsent, 'function', 'hasRememberedConsent' );
        equal( typeof tracker.isConsentRequired, 'function', 'isConsentRequired' );
        equal( typeof tracker.requireConsent, 'function', 'requireConsent' );
        equal( typeof tracker.setConsentGiven, 'function', 'setConsentGiven' );
        equal( typeof tracker.rememberConsentGiven, 'function', 'rememberConsentGiven' );
        equal( typeof tracker.forgetConsentGiven, 'function', 'forgetConsentGiven' );
        // opt out (via consent)
        equal( typeof tracker.isUserOptedOut, 'function', 'isUserOptedOut' );
        equal( typeof tracker.optUserOut, 'function', 'optUserOut' );
        equal( typeof tracker.forgetUserOptOut, 'function', 'forgetUserOptOut' );
        // Campaign param consent
        equal( typeof tracker.disableCampaignParameters, 'function', 'disableCampaignParameters' );
    });

    module("API and internals", {
        setup: function () {
            mockNowValue = null;
        },
        teardown: function () {
            mockNowValue = null;
        },
    });

    test("Tracker is_a functions", function() {
        expect(29);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._isDefined, 'function', 'isDefined' );
        ok( tracker.hook.test._isDefined(tracker), 'isDefined true' );
        ok( tracker.hook.test._isDefined(tracker.hook), 'isDefined(obj.exists) true' );
        ok( !tracker.hook.test._isDefined(tracker.non_existant_property), 'isDefined(obj.missing) false' );

        equal( typeof tracker.hook.test._isFunction, 'function', 'isFunction' );
        ok( tracker.hook.test._isFunction(tracker.hook.test._isFunction), 'isFunction(isFunction)' );
        ok( tracker.hook.test._isFunction(function () { }), 'isFunction(function)' );

        equal( typeof tracker.hook.test._isObject, 'function', 'isObject' );
        ok( tracker.hook.test._isObject(null), 'isObject(null)' ); // null is an object!
        ok( tracker.hook.test._isObject(new Object), 'isObject(Object)' );
        ok( tracker.hook.test._isObject(window), 'isObject(window)' );
        ok( !tracker.hook.test._isObject('string'), 'isObject("string")' );
        ok( tracker.hook.test._isObject(new String), 'isObject(String)' ); // String is an object!

        equal( typeof tracker.hook.test._isString, 'function', 'isString' );
        ok( tracker.hook.test._isString(''), 'isString(emptyString)' );
        ok( tracker.hook.test._isString('abc'), 'isString("abc")' );
        ok( tracker.hook.test._isString('123'), 'isString("123")' );
        ok( !tracker.hook.test._isString(123), 'isString(123)' );
        ok( !tracker.hook.test._isString(null), 'isString(null)' );
        ok( !tracker.hook.test._isString(window), 'isString(window)' );
        ok( !tracker.hook.test._isString(function () { }), 'isString(function)' );
        ok( tracker.hook.test._isString(new String), 'isString(String)' ); // String is a string

        var arrayChunk = tracker.hook.test._arrayChunk;
        deepEqual([[]], arrayChunk([]), 'empty array, no chunk size' );
        deepEqual([[]], arrayChunk([], 50), 'empty array, with chunk size' );
        deepEqual([[5]], arrayChunk([5], 50), 'one item, much larger chunk size' );
        deepEqual([[5,10,15,20,25]], arrayChunk([5,10,15,20,25]), 'multiple items, no chunk size' );
        deepEqual([[5,10,15,20,25]], arrayChunk([5,10,15,20,25], 50), 'multiple items, much larger chunk size' );
        deepEqual([[5,10],[15,20],[25]], arrayChunk([5,10,15,20,25], 2), 'multiple items, small chunk size' );
        deepEqual([[5,10,15,20,25]], arrayChunk([5,10,15,20,25], 5), 'multiple items, equals chunk size' );
    });

    test("Default visitorId should be equal across Trackers", function() {
        expect(5);

        deleteCookies();

        var asyncTracker = Piwik.getAsyncTracker();
        var asyncVisitorId = asyncTracker.getVisitorId();
        equal(Piwik.getAsyncTracker().getSiteId(), asyncTracker.getSiteId(), 'async same site id');
        equal(Piwik.getAsyncTracker().getTrackerUrl(), asyncTracker.getTrackerUrl(), 'async same getTrackerUrl()');

        wait(2000);

        var delayedTracker = Piwik.getTracker();
        var delayedVisitorId = delayedTracker.getVisitorId();
        equal(Piwik.getAsyncTracker().getVisitorId(), delayedVisitorId, 'delayedVisitorId ' + delayedVisitorId + ' should be the same as ' + Piwik.getAsyncTracker().getVisitorId());

        var prefixTracker = Piwik.getTracker();
        prefixTracker.setCookieNamePrefix('_test_cookie_prefix');

        var prefixVisitorId = prefixTracker.getVisitorId();
        notEqual(Piwik.getAsyncTracker().getVisitorId(), prefixVisitorId, 'Visitor ID are different when using a different cookie prefix');

        var customTracker = Piwik.getTracker('customTrackerUrl', '71');
        var customVisitorId = customTracker.getVisitorId();
        notEqual(Piwik.getAsyncTracker().getVisitorId(), customVisitorId, 'Visitor ID are different on different websites');
    });

    test("Managing multiple trackers", function() {
        expect(25);

        var asyncTracker = Piwik.getAsyncTracker();
        var i, tracker;

        // TEST addTracker()

        var trackers = [
            {idSite: '71', url: 'customTrackerUrl', expectedIdSite: '71', expectedUrl: 'customTrackerUrl'},
            {idSite: 72, url: 'customTrackerUrl', expectedIdSite: 72, expectedUrl: 'customTrackerUrl'},
            {idSite: 72, url: 'anotherTrackerUrl', expectedIdSite: 72, expectedUrl: 'anotherTrackerUrl'},
            {idSite: 73, url: null, expectedIdSite: 73, expectedUrl: asyncTracker.getTrackerUrl()}
        ]

        // add Tracker returns created tracker instance
        for (i = 0; i < trackers.length; i++) {
            tracker = trackers[i];
            var createdTracker = asyncTracker.addTracker(tracker.url, tracker.idSite);
            equal(tracker.expectedIdSite, createdTracker.getSiteId(), 'addTracker() was created with correct idsite ' + tracker.expectedIdSite);
            equal(tracker.expectedUrl, createdTracker.getTrackerUrl(), 'addTracker() was created with correct piwikUrl ' + tracker.expectedUrl);
        }

        // TEST getAsyncTracker()

        // by default still returns first tracker
        var firstTracker = Piwik.getAsyncTracker();
        equal(firstTracker.getSiteId(), asyncTracker.getSiteId(), 'getAsyncTracker() async same site id');
        equal(firstTracker.getTrackerUrl(), asyncTracker.getTrackerUrl(), 'getAsyncTracker() async same getTrackerUrl()');
        equal(firstTracker, asyncTracker, 'getAsyncTracker() async same tracker instance');

        var trackerWithoutIdSite = asyncTracker.addTracker(tracker.url);
        ok(!!trackerWithoutIdSite, 'addTracker() without siteId can be called');

        // getting a specific tracker instance

        for (i = 0; i < trackers.length; i++) {
            tracker = trackers[i];
            var fetchedTracker = Piwik.getAsyncTracker(tracker.url, tracker.idSite);
            equal(tracker.expectedIdSite, fetchedTracker.getSiteId(), 'getAsyncTracker() correct site id ' + tracker.expectedIdSite);
            equal(tracker.expectedUrl, fetchedTracker.getTrackerUrl(), 'getAsyncTracker() correct tracker url ' + tracker.expectedUrl);
        }

        // getting an unknown instance
        equal(null, Piwik.getAsyncTracker('unknownUrl', 72), 'getAsyncTracker() piwikUrl not known');
        equal(null, Piwik.getAsyncTracker('customTrackerUrl', 999982), 'getAsyncTracker() piwikSiteId not known');

        var fetchedTracker = Piwik.getAsyncTracker('customTrackerUrl', '71');
        var createdTracker = fetchedTracker.addTracker(null, 55);
        equal('customTrackerUrl', createdTracker.getTrackerUrl(), 'addTracker() should be default use tracker url of current tracker, not first tracker');

        var createPiwikTracker = Piwik.addTracker('customTrackerUrl2', 59);
        equal(59, createPiwikTracker.getSiteId(), 'Piwik.addTracker() was created with correct idsite ' + createPiwikTracker.getSiteId());
        equal('customTrackerUrl2', createPiwikTracker.getTrackerUrl(), 'Piwik.addTracker() was created with correct piwikUrl ' + createPiwikTracker.getTrackerUrl());

        asyncTracker.removeAllAsyncTrackersButFirst();
    });

    test("AnalyticsTracker alias", function() {
        expect(1);

        var tracker = AnalyticsTracker.getTracker();
        equal( typeof tracker.hook.test._encode, 'function', 'encodeWrapper' );
    });

    test("Tracker encode, decode wrappers", function() {
        expect(6);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._encode, 'function', 'encodeWrapper' );
        equal( typeof tracker.hook.test._decode, 'function', 'decodeWrapper' );

        equal( tracker.hook.test._encode("&=?;/#"), '%26%3D%3F%3B%2F%23', 'encodeWrapper()' );
        equal( tracker.hook.test._decode("%26%3D%3F%3B%2F%23"), '&=?;/#', 'decodeWrapper()' );
        equal( tracker.hook.test._decode("mailto:%69%6e%66%6f@%65%78%61%6d%70%6c%65.%63%6f%6d"), 'mailto:info@example.com', 'decodeWrapper()' );
        equal( tracker.hook.test._decode(
            "http://example.org/2013/06/test.php?param[]=1&test2=%D8%A5%D9%86%D8%B4%D8%A7%D8%A1-%D9%86%D8%B3%D8%AE%D8%A9-%D9%85%D8%AE%D8%B5%D8%B5%D8%A9-%D9%86%D8%B8%D8%A7%D9%85-%D8%AA%D8%B4%D8%BA%D9%8A%D9%84-%D8%A3%D9%88%D8%A8%D9%86%D8%AA%D9%88-%D8%A8/"),
            "http://example.org/2013/06/test.php?param[]=1&test2=إنشاء-نسخة-مخصصة-نظام-تشغيل-أوبنتو-ب/",
            'decodeWrapper()'
        );
    });

    test("Tracker getHostName(), *UrlParameter(), urlFixup(), domainFixup(), titleFixup() and purify()", function() {
        expect(88);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._getHostName, 'function', 'getHostName' );
        equal( typeof tracker.hook.test._getUrlParameter, 'function', '_getUrlParameter' );
        equal( typeof tracker.hook.test._addUrlParameter, 'function', '_addUrlParameter' );
        equal( typeof tracker.hook.test._removeUrlParameter, 'function', '_addUrlParameter' );

        equal( tracker.hook.test._getHostName('http://example.com'), 'example.com', 'http://example.com');
        equal( tracker.hook.test._getHostName('http://example.com/'), 'example.com', 'http://example.com/');
        equal( tracker.hook.test._getHostName('http://example.com/index'), 'example.com', 'http://example.com/index');
        equal( tracker.hook.test._getHostName('http://example.com/index?q=xyz'), 'example.com', 'http://example.com/index?q=xyz');
        equal( tracker.hook.test._getHostName('http://example.com/?q=xyz'), 'example.com', 'http://example.com/?q=xyz');
        equal( tracker.hook.test._getHostName('http://example.com/?q=xyz#hash'), 'example.com', 'http://example.com/?q=xyz#hash');
        equal( tracker.hook.test._getHostName('http://example.com#hash'), 'example.com', 'http://example.com#hash');
        equal( tracker.hook.test._getHostName('http://example.com/#hash'), 'example.com', 'http://example.com/#hash');
        equal( tracker.hook.test._getHostName('http://example.com:80'), 'example.com', 'http://example.com:80');
        equal( tracker.hook.test._getHostName('http://example.com:80/'), 'example.com', 'http://example.com:80/');
        equal( tracker.hook.test._getHostName('https://example.com/'), 'example.com', 'https://example.com/');
        equal( tracker.hook.test._getHostName('http://user@example.com/'), 'example.com', 'http://user@example.com/');
        equal( tracker.hook.test._getHostName('http://user:password@example.com/'), 'example.com', 'http://user:password@example.com/');

        equal( tracker.hook.test._getUrlParameter('http://piwik.org/', 'q'), '', 'no query');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?q=test', 'q'), 'test', '?q');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?q=test#aq=not', 'q'), 'test', '?q');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2', 'q'), 'test2', '&q');
        equal( tracker.hook.test._getUrlParameter('https://piwik.org/?pk_campaign=qwerty2%25-', 'pk_campaign'), 'qwerty2%-', '&pk_campaign');

        // _getUrlParameter in hash tag
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#aq=not', 'q'), 'test2', '&q');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#aq=not', 'aq'), 'not', '#aq');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'bq'), 'yes', '#bq');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#pk_campaign=campaign', 'pk_campaign'), 'campaign', '#pk_campaign');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'q'), 'test2', '#q');

        // URL decoded
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?q=http%3a%2f%2flocalhost%2f%3fr%3d1%26q%3dfalse', 'q'), 'http://localhost/?r=1&q=false', 'url');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?q=http%3a%2f%2flocalhost%2f%3fr%3d1%26q%3dfalse&notq=not', 'q'), 'http://localhost/?r=1&q=false', 'url');

        // non existing parameters
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'bqq'), "", '#q');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'bq='), "", '#q');
        equal( tracker.hook.test._getUrlParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'sp='), "", '#q');

        // REMOVE URL PARAMETER
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/', 'q'), 'http://piwik.org/', 'removeUrlParameter, no query');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?', 'q'), 'http://piwik.org/?', 'removeUrlParameter, ? does not contain url param so will not modify url');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=test', 'q'), 'http://piwik.org/', 'removeUrlParameter, ?q');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=', 'q'), 'http://piwik.org/', 'removeUrlParameter, empty parameter');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=&test=34', 'q'), 'http://piwik.org/?test=34', 'removeUrlParameter, empty parameter with other parameter');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=test#aq=not', 'q'), 'http://piwik.org/#aq=not', 'removeUrlParameter, ?q');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?p=test1&q=test2', 'q'), 'http://piwik.org/?p=test1', 'removeUrlParameter, &q');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=test2&p=test1', 'q'), 'http://piwik.org/?p=test1', 'removeUrlParameter, ?q&');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=test2&p=test1', 'terewrer'), 'http://piwik.org/?q=test2&p=test1', 'removeUrlParameter, not existing parameter');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=test#aq=not', 'aq'), 'http://piwik.org/?q=test', 'removeUrlParameter, from hash if a query present');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/#?q=test2&p=test1', 'q'), 'http://piwik.org/#?p=test1', 'removeUrlParameter, from hash if no query present');
        equal( tracker.hook.test._removeUrlParameter('http://piwik.org/?q=ttttt#q=test2&p=test1', 'q'), 'http://piwik.org/#p=test1', 'removeUrlParameter, from hash and query');

        // ADD URL PARAMETER
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/', 'q', ''), 'http://piwik.org/?q=', 'addUrlParameter, add param with no value to url with no search and no hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/', 'q', 'test'), 'http://piwik.org/?q=test', 'addUrlParameter, add param with value to url with no search and no hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?', 'q', ''), 'http://piwik.org/?q=', 'addUrlParameter, add param with no value to url with search but no hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?', 'q', 'test'), 'http://piwik.org/?q=test', 'addUrlParameter, add param with value to url with search but no hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?#', 'q', ''), 'http://piwik.org/?q=#', 'addUrlParameter, add param with no value to url with empty search and empty hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?#', 'q', 'test'), 'http://piwik.org/?q=test#', 'addUrlParameter, add param with value to url with empty search and empty hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?#foobar', 'q', ''), 'http://piwik.org/?q=#foobar', 'addUrlParameter, add param with no value to url with empty search and hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?#foobar', 'q', 'test'), 'http://piwik.org/?q=test#foobar', 'addUrlParameter, add param with value to url with empty search and hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?x=y&baz=wow#foobar', 'q', ''), 'http://piwik.org/?x=y&baz=wow&q=#foobar', 'addUrlParameter, add param with no value to url with search and hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?x=y&baz=wow#foobar', 'pk_test', 'test'), 'http://piwik.org/?x=y&baz=wow&pk_test=test#foobar', 'addUrlParameter, add param with value to url with search and hash');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?x=y&baz=wow#foobar', 'pk_!?#url', 'http://piwik.org'), 'http://piwik.org/?x=y&baz=wow&pk_!%3F%23url=http%3A%2F%2Fpiwik.org#foobar', 'addUrlParameter, should escape / encode value');
        equal( tracker.hook.test._addUrlParameter('http://piwik.org/?x=y&baz=wow#foobar', 'baz', 'wow2'), 'http://piwik.org/?x=y&baz=wow&baz=wow2#foobar', 'addUrlParameter, adds same parameter again, if already present');

        equal( typeof tracker.hook.test._urlFixup, 'function', 'urlFixup' );

        deepEqual( tracker.hook.test._urlFixup( 'webcache.googleusercontent.com', 'http://webcache.googleusercontent.com/search?q=cache:CD2SncROLs4J:piwik.org/blog/2010/04/piwik-0-6-security-advisory/+piwik+security&cd=1&hl=en&ct=clnk', '' ),
                ['piwik.org', 'http://piwik.org/qa', ''], 'webcache.googleusercontent.com' );

        deepEqual( tracker.hook.test._urlFixup( 'cc.bingj.com', 'http://cc.bingj.com/cache.aspx?q=web+analytics&d=5020318678516316&mkt=en-CA&setlang=en-CA&w=6ea8ea88,ff6c44df', '' ),
                ['piwik.org', 'http://piwik.org/qa', ''], 'cc.bingj.com' );

        deepEqual( tracker.hook.test._urlFixup( '74.6.239.185', 'http://74.6.239.185/search/srpcache?ei=UTF-8&p=piwik&fr=yfp-t-964&fp_ip=ca&u=http://cc.bingj.com/cache.aspx?q=piwik&d=4770519086662477&mkt=en-US&setlang=en-US&w=f4bc05d8,8c8af2e3&icp=1&.intl=us&sig=PXmPDNqapxSQ.scsuhIpZA--', '' ),
                ['piwik.org', 'http://piwik.org/qa', ''], 'yahoo cache (1)' );

        deepEqual( tracker.hook.test._urlFixup( '74.6.239.84', 'http://74.6.239.84/search/srpcache?ei=UTF-8&p=web+analytics&fr=yfp-t-715&u=http://cc.bingj.com/cache.aspx?q=web+analytics&d=5020318680482405&mkt=en-CA&setlang=en-CA&w=a68d7af0,873cfeb0&icp=1&.intl=ca&sig=x6MgjtrDYvsxi8Zk2ZX.tw--', '' ),
                ['piwik.org', 'http://piwik.org/qa', ''], 'yahoo cache (2)' );

        deepEqual( tracker.hook.test._urlFixup( 'translate.googleusercontent.com', 'http://translate.googleusercontent.com/translate_c?hl=en&ie=UTF-8&sl=en&tl=fr&u=http://piwik.org/&prev=_t&rurl=translate.google.com&twu=1&usg=ALkJrhirI_ijXXT7Ja_aDGndEJbE7pJqpQ', '' ),
                ['piwik.org', 'http://piwik.org/', 'http://translate.googleusercontent.com/translate_c?hl=en&ie=UTF-8&sl=en&tl=fr&u=http://piwik.org/&prev=_t&rurl=translate.google.com&twu=1&usg=ALkJrhirI_ijXXT7Ja_aDGndEJbE7pJqpQ'], 'translate.googleusercontent.com' );

        equal( typeof tracker.hook.test._domainFixup, 'function', 'domainFixup' );

        equal( tracker.hook.test._domainFixup( 'localhost' ), 'localhost', 'domainFixup: localhost' );
        equal( tracker.hook.test._domainFixup( 'localhost.' ), 'localhost', 'domainFixup: localhost.' );
        equal( tracker.hook.test._domainFixup( 'localhost.localdomain' ), 'localhost.localdomain', 'domainFixup: localhost.localdomain' );
        equal( tracker.hook.test._domainFixup( 'localhost.localdomain.' ), 'localhost.localdomain', 'domainFixup: localhost.localdomain.' );
        equal( tracker.hook.test._domainFixup( '127.0.0.1' ), '127.0.0.1', 'domainFixup: 127.0.0.1' );
        equal( tracker.hook.test._domainFixup( 'www.example.com' ), 'www.example.com', 'domainFixup: www.example.com' );
        equal( tracker.hook.test._domainFixup( 'www.example.com.' ), 'www.example.com', 'domainFixup: www.example.com.' );
        equal( tracker.hook.test._domainFixup( '.example.com' ), '.example.com', 'domainFixup: .example.com' );
        equal( tracker.hook.test._domainFixup( '.example.com.' ), '.example.com', 'domainFixup: .example.com.' );
        equal( tracker.hook.test._domainFixup( '*.example.com' ), '.example.com', 'domainFixup: *.example.com' );
        equal( tracker.hook.test._domainFixup( '*.example.com.' ), '.example.com', 'domainFixup: *.example.com.' );

        equal( typeof tracker.hook.test._titleFixup, 'function', 'titleFixup' );
        equal( tracker.hook.test._titleFixup( 'hello' ), 'hello', 'hello string' );
        equal( tracker.hook.test._titleFixup( document.title ), 'matomo.js: Unit Tests', 'hello string' );

        equal( typeof tracker.hook.test._purify, 'function', 'purify' );

        equal( tracker.hook.test._purify('http://example.com'), 'http://example.com', 'http://example.com');
        equal( tracker.hook.test._purify('http://example.com#hash'), 'http://example.com#hash', 'http://example.com#hash');
        equal( tracker.hook.test._purify('http://example.com/?q=xyz#hash'), 'http://example.com/?q=xyz#hash', 'http://example.com/?q=xyz#hash');

        tracker.discardHashTag(true);

        equal( tracker.hook.test._purify('http://example.com'), 'http://example.com', 'http://example.com');
        equal( tracker.hook.test._purify('http://example.com#hash'), 'http://example.com', 'http://example.com#hash');
        equal( tracker.hook.test._purify('http://example.com/?q=xyz#hash'), 'http://example.com/?q=xyz', 'http://example.com/?q=xyz#hash');

        tracker.setExcludedQueryParams(['sid', 'test']);

        equal( tracker.hook.test._purify('http://example.com/?sid=12345&test5=1'), 'http://example.com/?test5=1', 'http://example.com/?sid=12345&test5=1');
        equal( tracker.hook.test._purify('http://example.com/?asid=12345&test=1'), 'http://example.com/?asid=12345', 'http://example.com/?asid=12345&test=1');
        equal( tracker.hook.test._purify('http://example.com/?sid=test#hash'), 'http://example.com/', 'http://example.com/?sid=test#hash');
        equal( tracker.hook.test._purify('http://example.com/?sid=test&sidtest=xyz#test'), 'http://example.com/?sidtest=xyz', 'http://example.com/?sid=test&sidtest=xyz#test');

    });

    // support for setCustomUrl( relativeURI )
    test("getProtocolScheme and resolveRelativeReference", function() {
        expect(28);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._getProtocolScheme, 'function', "getProtocolScheme" );

        ok( tracker.hook.test._getProtocolScheme('http://example.com') === 'http', 'http://' );
        ok( tracker.hook.test._getProtocolScheme('https://example.com') === 'https', 'https://' );
        ok( tracker.hook.test._getProtocolScheme('file://somefile.txt') === 'file', 'file://' );
        ok( tracker.hook.test._getProtocolScheme('mailto:somebody@example.com') === 'mailto', 'mailto:' );
        ok( tracker.hook.test._getProtocolScheme('tel:0123456789') === 'tel', 'tel:' );
        ok( tracker.hook.test._getProtocolScheme('javascript:alert(document.cookie)') === 'javascript', 'javascript:' );
        ok( tracker.hook.test._getProtocolScheme('') === null, 'empty string' );
        ok( tracker.hook.test._getProtocolScheme(':') === null, 'unspecified scheme' );
        ok( tracker.hook.test._getProtocolScheme('scheme') === null, 'missing colon' );

        equal( typeof tracker.hook.test._resolveRelativeReference, 'function', 'resolveRelativeReference' );

        var i, j, data = [
            // unsupported
//          ['http://example.com/index.php/pathinfo?query', 'test.php', 'http://example.com/test.php'],
//          ['http://example.com/subdir/index.php', '../test.php', 'http://example.com/test.php'],

            // already absolute
            ['http://example.com/', 'http://example.com', 'http://example.com'],
            ['http://example.com/', 'https://example.com/', 'https://example.com/'],
            ['http://example.com/', 'http://example.com/index', 'http://example.com/index'],

            // relative to root
            ['http://example.com/', '', 'http://example.com/'],
            ['http://example.com/', '/', 'http://example.com/'],
            ['http://example.com/', '/test.php', 'http://example.com/test.php'],
            ['http://example.com/index', '/test.php', 'http://example.com/test.php'],
            ['http://example.com/index?query=x', '/test.php', 'http://example.com/test.php'],
            ['http://example.com/index?query=x#hash', '/test.php', 'http://example.com/test.php'],
            ['http://example.com/?query', '/test.php', 'http://example.com/test.php'],
            ['http://example.com/#hash', '/test.php', 'http://example.com/test.php'],

            // relative to current document
            ['http://example.com/subdir/', 'test.php', 'http://example.com/subdir/test.php'],
            ['http://example.com/subdir/index', 'test.php', 'http://example.com/subdir/test.php'],
            ['http://example.com/subdir/index?query=x', 'test.php', 'http://example.com/subdir/test.php'],
            ['http://example.com/subdir/index?query=x#hash', 'test.php', 'http://example.com/subdir/test.php'],
            ['http://example.com/subdir/?query', 'test.php', 'http://example.com/subdir/test.php'],
            ['http://example.com/subdir/#hash', 'test.php', 'http://example.com/subdir/test.php']
        ];

        for (i = 0; i < data.length; i++) {
            j = data[i];
            equal( tracker.hook.test._resolveRelativeReference(j[0], j[1]), j[2], j[2] );
        }
    });

    test("Tracker setDomains(), isSiteHostName(), isSiteHostPath(), and getLinkIfShouldBeProcessed()", function() {
        expect(173);

        var tracker = Piwik.getTracker();
        var initialDomains = tracker.getDomains();
        var domainAlias = initialDomains[0];

        equal( typeof tracker.hook.test._isSiteHostName, 'function', "isSiteHostName" );
        equal( typeof tracker.hook.test._isSiteHostPath, 'function', "isSiteHostPath" );
        equal( typeof tracker.hook.test._getLinkIfShouldBeProcessed, 'function', "getLinkIfShouldBeProcessed" );

        var isSiteHostName = tracker.hook.test._isSiteHostName;
        var isSiteHostPath = tracker.hook.test._isSiteHostPath;
        var getLinkIfShouldBeProcessed = tracker.hook.test._getLinkIfShouldBeProcessed;

        // tracker.setDomain()

        // test wildcards
        tracker.setDomains( ['*.Example.com'] );
        propEqual(tracker.getDomains(), ["*.Example.com", domainAlias], 'should add domainAlias');

        tracker.setDomains( ['*.Example.com/'] );
        propEqual(tracker.getDomains(), ["*.Example.com/", domainAlias], 'should add domainAlias if domain has a slash as it is not a path');

        tracker.setDomains( ['*.Example.com/*'] );
        propEqual(tracker.getDomains(), ["*.Example.com/*", domainAlias], 'should add domainAlias if domain has /* as it is not a path');

        tracker.setDomains( '*.Example.org' );
        propEqual(tracker.getDomains(), ["*.Example.org", domainAlias], 'should handle a string');

        tracker.setDomains( ['*.Example.com/path'] );
        propEqual(tracker.getDomains(), ["*.Example.com/path"], 'if any other domain has path should not add domainAlias');

        tracker.setDomains( ['*.Example.com', '*.example.ORG'] );
        propEqual(tracker.getDomains(), ["*.Example.com", '*.example.ORG', domainAlias], 'should be able to set many domains');

        tracker.setDomains( [] );
        propEqual(tracker.getDomains(), [domainAlias], 'setting an empty array should reset the list');

        tracker.setDomains( ['*.Example.com', domainAlias + '/path', '*.example.ORG'] );
        propEqual(tracker.getDomains(), ['*.Example.com', domainAlias + '/path', '*.example.ORG'], 'if domain alias is already given should not add domainAlias');

        tracker.setDomains( ['.' + domainAlias + '/path'] );
        propEqual(tracker.getDomains(), ['.' + domainAlias + '/path'], 'if domain alias with subdomain is already given should not add domainAlias');

        /**
         * isSiteHostName ()
         */

        // test wildcards
        tracker.setDomains( ['*.Example.com'] );

        // skip test if testing on localhost
        ok( window.location.hostname != 'localhost' ? !tracker.hook.test._isSiteHostName('localhost') : true, '!isSiteHostName("localhost")' );

        ok( !isSiteHostName('google.com'), '!isSiteHostName("google.com")' );
        ok( isSiteHostName('example.com'), 'isSiteHostName("example.com")' );
        ok( isSiteHostName('www.example.com'), 'isSiteHostName("www.example.com")' );
        ok( isSiteHostName('www.sub.example.com'), 'isSiteHostName("www.sub.example.com")' );

        tracker.setDomains( 'dev.piwik.org' );
        ok( !isSiteHostName('piwik.org'), '!isSiteHostName("piwik.org")' );
        ok( isSiteHostName('dev.piwik.org'), 'isSiteHostName("dev.piwik.org")' );
        ok( !isSiteHostName('piwik.example.org'), '!isSiteHostName("piwik.example.org")');
        ok( !isSiteHostName('dev.piwik.org.com'), '!isSiteHostName("dev.piwik.org.com")');

        tracker.setDomains( '.piwik.org' );
        ok( isSiteHostName('piwik.org'), 'isSiteHostName("piwik.org")' );
        ok( isSiteHostName('dev.piwik.org'), 'isSiteHostName("dev.piwik.org")' );
        ok( !isSiteHostName('piwik.org.com'), '!isSiteHostName("piwik.org.com")');

        // domain wildcard should not affect behavior
        tracker.setDomains( '.piwik.net/*' );
        ok( isSiteHostName('piwik.net'), 'isSiteHostName("piwik.net")' );
        ok( isSiteHostName('dev.piwik.net'), 'isSiteHostName("dev.piwik.net")' );
        ok( !isSiteHostName('piwik.net.com'), '!isSiteHostName("piwik.net.com")');

        /**
         * isSiteHostPath ()
         */

        // various edge cases with wildcards or 'empty' paths
        var testCases = [
            ['piwik.org'],
            ['piwik.org/'],
            ['piwik.org/*'],
            ['piwik.org/*', 'piwik.org/foo' ],
            ['piwik.org/foo', 'piwik.org/*' ],
            ['piwik.org/foo', 'piwik.org/*', 'piwik.org/*/bar' ],
        ];
        for(var i in testCases) {
            domainTestCase = testCases[i];
            tracker.setDomains( domainTestCase );

            ok( isSiteHostPath('piwik.org', '/'), 'isSiteHostPath("piwik.org", "/") for ' +  domainTestCase );
            ok( isSiteHostPath('piwik.org', ''), 'isSiteHostPath("piwik.org", "") for ' +  domainTestCase );
            ok( isSiteHostPath('piwik.org', '*'), 'isSiteHostPath("piwik.org", "*") for ' +  domainTestCase);
            ok( isSiteHostPath('piwik.org', '/*'), 'isSiteHostPath("piwik.org", "/*") for ' +  domainTestCase);
            ok( isSiteHostPath('piwik.org', '/index'), 'isSiteHostPath("piwik.org", "/index") for ' +  domainTestCase);
        }


        // with path
        tracker.setDomains( '.piwik.org/path' );
        ok( isSiteHostPath('piwik.org', 'path'), 'isSiteHostPath("piwik.org", "path")' );
        ok( isSiteHostPath('piwik.org', '/path'), 'isSiteHostPath("piwik.org", "/path")' );
        ok( isSiteHostPath('piwik.org', '/path/'), 'isSiteHostPath("piwik.org", "/path/")' );
        ok( !isSiteHostPath('piwik.org', '/path.htm'), 'isSiteHostPath("piwik.org", "/path.htm")' );
        ok( isSiteHostPath('piwik.org', '/path/test'), 'isSiteHostPath("piwik.org", "/path/test)' );
        ok( isSiteHostPath('dev.piwik.org', '/path'), 'isSiteHostPath("dev.piwik.org", "/path")' );
        ok( !isSiteHostPath('piwik.com', ''), '!isSiteHostPath("piwik.com", "")');
        ok( !isSiteHostPath('piwik.org', '/'), 'isSiteHostPath("piwik.org", "/")' );
        ok( !isSiteHostPath('piwik.org', '/pat'), '!isSiteHostPath("piwik.org", "/pat")');
        ok( !isSiteHostPath('piwik.org', '.com'), '!isSiteHostPath("piwik.org", ".com")');
        ok( !isSiteHostPath('piwik.com', '/path'), '!isSiteHostPath("piwik.com", "/path")');
        ok( !isSiteHostPath('piwik.com', '/path/test'), '!isSiteHostPath("piwik.com", "/path/test")');
        ok( !isSiteHostPath('piwik.com', 'path/test'), '!isSiteHostPath("piwik.com", "/path/test")');
        ok( !isSiteHostPath('piwik.com', 'path/test/'), '!isSiteHostPath("piwik.com", "/path/test")');

        // no path
        var domains = ['.piwik.org', 'piwik.org', '*.piwik.org', '.piwik.org/'];
        for (var i in domains) {
            var domain = domains[i];
            tracker.setDomains( domain );
            ok( isSiteHostPath('piwik.org', '/path'), 'isSiteHostPath("piwik.org", "/path"), domain: ' + domain );
            ok( isSiteHostPath('piwik.org', '/path/'), 'isSiteHostPath("piwik.org", "/path/"), domain: ' + domain );
            ok( isSiteHostPath('piwik.org', '/path/test'), 'isSiteHostPath("piwik.org", "/path/test), domain: ' + domain );

            if (domain === 'piwik.org') {
                ok( !isSiteHostPath('dev.piwik.org', '/path'), 'isSiteHostPath("dev.piwik.org", "/path"), domain: ' + domain );
            } else {
                ok( isSiteHostPath('dev.piwik.org', '/path'), 'isSiteHostPath("dev.piwik.org", "/path"), domain: ' + domain );
            }
            ok( isSiteHostPath('piwik.org', '/pat'), '!isSiteHostPath("piwik.org", "/pat"), domain: ' + domain );
            ok( isSiteHostPath('piwik.org', '.com'), '!isSiteHostPath("piwik.org", ".com"), domain: ' + domain);
            ok( isSiteHostPath('piwik.org', '/foo'), '!isSiteHostPath("piwik.com", "/foo"), domain: ' + domain);
            ok( !isSiteHostPath('piwik.com', '/path'), '!isSiteHostPath("piwik.com", "/path"), domain: ' + domain);
            ok( !isSiteHostPath('piwik.com', '/path/test'), '!isSiteHostPath("piwik.com", "/path/test"), domain: ' + domain);
            ok( !isSiteHostPath('piwik.com', ''), '!isSiteHostPath("piwik.com", "/path/test"), domain: ' + domain);
        }

        // multiple paths / domains
        tracker.setDomains( ['piwik.org/path', 'piwik.org/foo', 'piwik.org/bar/baz', '.piwik.xyz/test'] );
        ok( isSiteHostPath('piwik.xyz', 'test/bar'), 'isSiteHostPath("piwik.xyz", "test/bar")' );
        ok( isSiteHostPath('piwik.xyz', '/test/bar'), 'isSiteHostPath("piwik.xyz", "/test/bar")' );
        ok( !isSiteHostPath('piwik.org', '/foobar/'), 'isSiteHostPath("piwik.org", "/foobar/")' );
        ok( !isSiteHostPath('piwik.org', 'foobar/'), 'isSiteHostPath("piwik.org", "foobar/")' );
        ok( !isSiteHostPath('piwik.org', 'foobar'), 'isSiteHostPath("piwik.org", "foobar")' );
        ok( isSiteHostPath('piwik.org', '/foo/bar'), 'isSiteHostPath("piwik.org", "/foo/bar")' );
        ok( isSiteHostPath('piwik.org', '/bar/baz/foo'), 'isSiteHostPath("piwik.org", "/bar/baz/foo/")' );
        ok( !isSiteHostPath('piwik.org', '/bar/ba'), 'isSiteHostPath("piwik.org", "/bar/ba")' );
        ok( isSiteHostPath('piwik.org', '/path/test'), 'isSiteHostPath("piwik.org", "/path/test")' );
        ok( isSiteHostPath('piwik.org', '/path/test.htm'), 'isSiteHostPath("piwik.org", "/path/test.htm")' );
        ok( isSiteHostPath('dev.piwik.xyz', '/test'), 'isSiteHostPath("dev.piwik.xyz", "/test")' );
        ok( !isSiteHostPath('dev.piwik.xyz', 'something/test.htm'), 'isSiteHostPath("dev.piwik.xyz", "something/test")' );
        ok( !isSiteHostPath('dev.piwik.xyz', '/'), 'isSiteHostPath("dev.piwik.xyz", "/")' );
        ok( !isSiteHostPath('dev.piwik.xyz', ''), 'isSiteHostPath("dev.piwik.xyz", "")' );
        ok( !isSiteHostPath('piwik.org', '/'), 'isSiteHostPath("piwik.org", "/")' );
        ok( !isSiteHostPath('piwik.xyz', '/'), 'isSiteHostPath("piwik.xyz", "/")' );
        ok( !isSiteHostPath('piwik.org', '/index.htm'), 'isSiteHostPath("piwik.org", "/index.htm")' );
        ok( !isSiteHostPath('piwik.org', '/anythingelse'), 'isSiteHostPath("piwik.org", "/anythingelse")' );
        ok( !isSiteHostPath('another.org', '/'), 'isSiteHostPath("another.org", "/")' );
        ok( !isSiteHostPath('another.org', '/anythingelse'), 'isSiteHostPath("another.org", "/anythingelse")' );


        // some subdirectories and some path wildcards
        tracker.setDomains( ['piwik.org/path', 'piwik.org/path2', 'piwik.org/index*'] );
        ok( !isSiteHostPath('piwik.org', '/another'), "isSiteHostPath('piwik.org', '/another')" );
        ok( !isSiteHostPath('piwik.org', '/anotherindex'), "isSiteHostPath('piwik.org', '/anotherindex')" );
        ok( !isSiteHostPath('piwik.org', '/path.html'), "isSiteHostPath('piwik.org', '/path.html')" );
        ok( isSiteHostPath('piwik.org', '/index'), "isSiteHostPath('piwik.org', '/index')" );
        ok( isSiteHostPath('piwik.org', '/index.htm'), "isSiteHostPath('piwik.org', '/index.htm')" );
        ok( isSiteHostPath('piwik.org', '/index_en.htm'), "isSiteHostPath('piwik.org', '/index_en.htm')" );
        ok( isSiteHostPath('piwik.org', '/index*page'), "isSiteHostPath('piwik.org', '/index*page')" );

        tracker.setDomains( ['piwik.org/index*', 'piwik.org'] );
        ok( isSiteHostPath('piwik.org', '/index*page'), "isSiteHostPath('piwik.org', '/index*page')" );
        ok( isSiteHostPath('piwik.org', ''), "isSiteHostPath('piwik.org', '')" );
        ok( isSiteHostPath('piwik.org', '/'), "isSiteHostPath('piwik.org', '/')" );

        // all is compared lower case
        tracker.setDomains( '.piwik.oRg/PaTh' );
        ok( isSiteHostPath('piwiK.org', '/pAth'), 'isSiteHostPath("piwik.org", "/path")' );
        ok( isSiteHostPath('piwik.org', '/patH/'), 'isSiteHostPath("piwik.org", "/path/")' );
        ok( isSiteHostPath('Piwik.ORG', '/PATH/TEST'), 'isSiteHostPath("piwik.org", "/path/test)' );

        /**
         * getLinkIfShouldBeProcessed ()
         */
        var getLinkIfShouldBeProcessed = tracker.hook.test._getLinkIfShouldBeProcessed;
        function createLink(url) {
            var link = document.createElement('a');
            link.href = url;
            return link;
        }

        tracker.setDomains( ['.piwik.org/path', '.piwik.org/foo', '.piwik.org/bar/baz', '.piwik.xyz/test'] );

        // they should not be detected as outlink as they match one of the domains
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/foo/bar')), 'getLinkIfShouldBeProcessed http://www.piwik.org/foo/bar matches .piwik.org/foo')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://piwik.org/foo/bar')), 'getLinkIfShouldBeProcessed http://piwik.org/foo/bar matches .piwik.org/foo')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('//piwik.org/foo/bar')), 'getLinkIfShouldBeProcessed no protcol but url starts with //')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/foo?x=1')), 'getLinkIfShouldBeProcessed url with query parameter should detect correct path')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/foo')), 'getLinkIfShouldBeProcessed path is same as allowed path')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/foo/')), 'getLinkIfShouldBeProcessed path is same as allowed path but with appended slash')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/bar/baz/')), 'getLinkIfShouldBeProcessed multiple directories with appended slash')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/bar/baz')), 'getLinkIfShouldBeProcessed multiple directories')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://WWW.PIWIK.ORG/BAR/BAZ')), 'getLinkIfShouldBeProcessed should test everything lowercase')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/bar/baz/x/y/z')), 'getLinkIfShouldBeProcessed many appended paths')
        equal(undefined, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/bar/baz?test=1&foo=bar')), 'getLinkIfShouldBeProcessed another test with query parameter and multiple directories')
        equal('link', getLinkIfShouldBeProcessed(createLink('piwik.org/foo/bar')).type, 'getLinkIfShouldBeProcessed missing protocol only domain given should be outlink as current domain not given in setDomains')
        propEqual({
                "href": "http://www.piwik.org/foo/download.apk",
                "type": "download"
        }, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/foo/download.apk')), 'getLinkIfShouldBeProcessed should detect download even if it is link to same domain')
        propEqual({
            "href": "http://www.piwik.org/foobar/download.apk",
            "type": "download"
        }, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/foobar/download.apk')), 'getLinkIfShouldBeProcessed should detect download even if it goes to different domain/path')
        propEqual({
            "href": "http://www.piwik.com/foobar/download.apk",
            "type": "download"
        }, getLinkIfShouldBeProcessed(createLink('http://www.piwik.com/foobar/download.apk')), 'getLinkIfShouldBeProcessed should detect download even if it goes to different domain')
        propEqual({
            "href": "http://www.piwik.xyz/foo/",
            "type": "link"
        }, getLinkIfShouldBeProcessed(createLink('http://www.piwik.xyz/foo/')), 'getLinkIfShouldBeProcessed path matches but domain not so outlink')
        propEqual({
            "href": "http://www.piwik.org/bar",
            "type": "link"
        }, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/bar')), 'getLinkIfShouldBeProcessed domain matches but path not so outlink')
        propEqual({
            "href": "http://www.piwik.org/footer",
            "type": "link"
        }, getLinkIfShouldBeProcessed(createLink('http://www.piwik.org/footer')), 'getLinkIfShouldBeProcessed http://www.piwik.org/footer and there is domain piwik.org/foo but it should be outlink as path is different')

        /**
         * Test that we don't set a cookie path automatically
         */
        tracker.setCookiePath(null);
        tracker.setDomains( ['.' + domainAlias + '/tests'] );
        equal(null, tracker.getCookiePath(), 'should not set a cookie path automatically');

        tracker.setCookiePath(null);
        tracker.setDomains( ['.' + domainAlias + '/tests/javascript'] );
        equal(null, tracker.getCookiePath(), 'should not set a cookie path automatically');

        tracker.setCookiePath('/path2');
        tracker.setDomains( ['.' + domainAlias + '/tests/javascript', '.' + domainAlias + '/tests'] );
        equal('/path2', tracker.getCookiePath(), 'should not set a cookie path automatically');

        tracker.setCookiePath(null);

        strictEqual(true, tracker.hasCookies());
        tracker.setSessionCookie('mytest', 'myvalue');
        equal('myvalue', tracker.getCookie('mytest'));
        strictEqual(null, tracker.getCookie('34343434343'), 'not existing cookie returns null');

        equal('Lax', tracker.getConfigCookieSameSite(), 'same site cookie value is Lax by default');
        tracker.setCookieSameSite(null);
        equal('Lax', tracker.getConfigCookieSameSite(), 'setCookieSameSite, wont unset the value when empty');
        tracker.setCookieSameSite('invalid');
        equal('Lax', tracker.getConfigCookieSameSite(), 'setCookieSameSite, wont unset the value when invlaid');
        tracker.setCookieSameSite('strict');
        equal('Strict', tracker.getConfigCookieSameSite(), 'setCookieSameSite can be used lower case');
        tracker.setCookieSameSite('LaX');
        equal('Lax', tracker.getConfigCookieSameSite(), 'setCookieSameSite can be used upper case');
    });

    test("Tracker CrossDomainLinking()", function() {
        expect(57);

        var tracker = Piwik.getTracker();

        var getVisitorIdFromUrl = tracker.hook.test._getVisitorIdFromUrl;
        var generateBrowserSpecificId = tracker.hook.test._generateBrowserSpecificId;
        var isSameCrossDomainDevice = tracker.hook.test._isSameCrossDomainDevice;
        var makeCrossDomainDeviceId = tracker.hook.test._makeCrossDomainDeviceId;
        var replaceHrefForCrossDomainLink = tracker.hook.test._replaceHrefForCrossDomainLink;
        var isLinkToDifferentDomainButSameMatomoWebsite = tracker.hook.test._isLinkToDifferentDomainButSameMatomoWebsite;

        strictEqual(false, tracker.isCrossDomainLinkingEnabled(), 'function', "isCrossDomainLinkingEnabled is disabled by default" );

        tracker.enableCrossDomainLinking();
        strictEqual(true, tracker.isCrossDomainLinkingEnabled(), 'function', "enableCrossDomainLinking enables cross domain linking" );

        tracker.disableCrossDomainLinking();
        strictEqual(false, tracker.isCrossDomainLinkingEnabled(), 'function', "disableCrossDomainLinking disables cross domain linking" );

        strictEqual(16, makeCrossDomainDeviceId().length, 'function', "makeCrossDomainDeviceId, should return a 16 character id" );
        strictEqual(6, generateBrowserSpecificId().length, 'function', "generateBrowserSpecificId, should return a 6 character id" );

        var currentTimestamp = Math.floor((new Date().getTime()) / 1000);
        var browserId = generateBrowserSpecificId();

        strictEqual(true, isSameCrossDomainDevice(String(currentTimestamp) + browserId), "isSameCrossDomainDevice, should return true if browserId is the same and timestamp within 45 seconds" );
        strictEqual(true, isSameCrossDomainDevice(String(currentTimestamp - 100) + browserId), "isSameCrossDomainDevice, should return true if browserId is the same and timestamp within 45 seconds" );
        strictEqual(false, isSameCrossDomainDevice(String(currentTimestamp - 183) + browserId), "isSameCrossDomainDevice, should return false if browserId is the same but timestamp is 181+ seconds old" );
        strictEqual(false, isSameCrossDomainDevice(String(currentTimestamp + 2) + browserId), "isSameCrossDomainDevice, should return false if browserId is the same but timestamp was only generated later" );
        strictEqual(false, isSameCrossDomainDevice(String(currentTimestamp)), "isSameCrossDomainDevice, should return false if no device ID given" );
        strictEqual(false, isSameCrossDomainDevice(browserId), "isSameCrossDomainDevice, should return false if no timestamp given" );
        strictEqual(false, isSameCrossDomainDevice(String(currentTimestamp) + '12345a'), "isSameCrossDomainDevice, should return false if timestamp is valid but browserId is not the same" );

        function makeUrlWithVisitorId(withId, timestamp, browserId)
        {
            var url = 'http://www.example.com/?';

            if ('boolean' === typeof withId) {
                url += 'pk_vid=900d0d1eb6714aa4';
            } else if (withId) {
                url += 'pk_vid=' + withId;
            }

            if (timestamp && browserId) {
                if (!withId) {
                    url+= '&pk_vid=';
                }

                url += String(timestamp) + browserId
            }

            return url;
        }

        function makeVisitorIdFromUrl(withId, timestamp, browserId)
        {
            var url = makeUrlWithVisitorId(withId, timestamp, browserId);

            return getVisitorIdFromUrl(url);
        }

        strictEqual('', makeVisitorIdFromUrl(true, currentTimestamp, browserId), "getVisitorIdFromUrl, should not return the visitorId if timestamp and browser ID is valid but cross domain is disabled" );
        tracker.enableCrossDomainLinking();
        strictEqual('900d0d1eb6714aa4', makeVisitorIdFromUrl(true, currentTimestamp, browserId), "getVisitorIdFromUrl, should return the visitorId if timestamp and browser ID is valid and cross domain is enabled" );
        strictEqual('900d0d1eb6714aa3', makeVisitorIdFromUrl('900d0d1eb6714aa3', currentTimestamp, browserId), "getVisitorIdFromUrl, should return the visitorId if timestamp and browser ID is valid and cross domain is enabled" );
        strictEqual('', makeVisitorIdFromUrl('900d0d1eb6714aa;', currentTimestamp, browserId), "getVisitorIdFromUrl, should not return the visitorId if everything is valid but visitorId contains invalid character" );
        strictEqual('', makeVisitorIdFromUrl('900d0d1eb6714a', currentTimestamp, browserId), "getVisitorIdFromUrl, should not return the visitorId if everything is valid but visitorId contains not enough characters" );
        strictEqual('', makeVisitorIdFromUrl(false, currentTimestamp, browserId), "getVisitorIdFromUrl, should return empty string if timestamp and browser ID is valid but no visitorId in url" );
        strictEqual('', makeVisitorIdFromUrl(true, currentTimestamp, 'foobar'), "getVisitorIdFromUrl, should return empty string if visitorId is given but browser ID is not valid" );
        strictEqual('', makeVisitorIdFromUrl(true, currentTimestamp, 'fooba'), "getVisitorIdFromUrl, should return empty string if pk_vid has not 32 character but 31" );
        strictEqual('', makeVisitorIdFromUrl(true, currentTimestamp, '!test,'), "getVisitorIdFromUrl, should return empty string if pk_vid has invalid characters" );
        strictEqual('', makeVisitorIdFromUrl(true, currentTimestamp + 158, browserId), "getVisitorIdFromUrl, should return empty string if visitorId and browser ID is valid but timestamp is in future" );
        strictEqual('', makeVisitorIdFromUrl(true, currentTimestamp - 188, browserId), "getVisitorIdFromUrl, should return empty string if visitorId and browser ID is valid but timestamp was too long ago" );
        strictEqual('900d0d1eb6714aa4', makeVisitorIdFromUrl(true, currentTimestamp - 20, browserId), "getVisitorIdFromUrl, should return the visitorId if browser ID is valid and timestamp is only 20 seconds old" );

        function makeReplaceHrefForCrossDomainLink(url) {
            var a = document.createElement('a');
            if (url !== null) {
                a.setAttribute('href', url);
            }
            replaceHrefForCrossDomainLink(a);
            return a.getAttribute('href');
        }

        strictEqual(null, makeReplaceHrefForCrossDomainLink(null), 'replaceHrefForCrossDomainLink, should not change URL if nothing is set');
        strictEqual('', makeReplaceHrefForCrossDomainLink(''), 'replaceHrefForCrossDomainLink, should not change URL if nothing is set');
        strictEqual(tracker.getTrackerUrl(), makeReplaceHrefForCrossDomainLink(tracker.getTrackerUrl()), 'replaceHrefForCrossDomainLink, should not change URL if href is a tracker URL');
        strictEqual(tracker.getTrackerUrl(), makeReplaceHrefForCrossDomainLink(tracker.getTrackerUrl()), 'replaceHrefForCrossDomainLink, should not change URL if href is a tracker URL');

            tracker.setVisitorId('082ea0f319e784f6');
            var replacedUrl = makeReplaceHrefForCrossDomainLink('http://www.example.com/');
            ok(replacedUrl.indexOf('http://www.example.com/?pk_vid=082ea0f319e784f6') === 0, 'replaceHrefForCrossDomainLink, should set parameters if a URL is given');
        ok(replacedUrl.indexOf(browserId) > 20, 'replaceHrefForCrossDomainLink, should set browserId if a url is given');

        replacedUrl = makeReplaceHrefForCrossDomainLink(makeUrlWithVisitorId(true, currentTimestamp, 'foobar'));
            ok(replacedUrl.indexOf('http://www.example.com/?pk_vid=082ea0f319e784f6') === 0, 'replaceHrefForCrossDomainLink, should replace parameters if a URL is given');
        ok(replacedUrl.indexOf(browserId) > 20, 'replaceHrefForCrossDomainLink, should replace browserId if a URL is given');


        function makeIsLinkToDifferentDomainButSamePiwikWebsite(url) {
            var a = document.createElement('a');
            if (url !== null) {
                a.setAttribute('href', url);
            }
            return isLinkToDifferentDomainButSameMatomoWebsite(a);
        }
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite(null), 'isLinkToDifferentDomainButSamePiwikWebsite, should not return anything if no href is set');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite(''), 'isLinkToDifferentDomainButSamePiwikWebsite, should not return anything if empty href is set');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('?x=1'), 'isLinkToDifferentDomainButSamePiwikWebsite, if local url starting with query');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('/test'), 'isLinkToDifferentDomainButSamePiwikWebsite, if relative url starting with slash');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('test/test.php'), 'isLinkToDifferentDomainButSamePiwikWebsite, if relative url starting with file');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('//www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, if outlink starting with // but not going to same website');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('http://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, if outlink starting with http:// but not going to same website');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('https://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, if outlink starting with https:// but not going to same website');

        tracker.setDomains(['www.example.org']);
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('//www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, www.example.org, same website, different domain, if outlink starting with // but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('http://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, www.example.org, same website, different domain, if outlink starting with http:// but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('https://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, www.example.org, same website, different domain, if outlink starting with https:// but not going to same website');

        tracker.setDomains(['example.org']);
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('//www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, example.org, not same website, different domain, if outlink starting with // but not going to same website');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('http://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, example.org, not same website, different domain, if outlink starting with http:// but not going to same website');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('https://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, example.org, not same website, different domain, if outlink starting with https:// but not going to same website');

        tracker.setDomains(['*.example.org']);
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('//www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, *.example.org, same website, different domain, if outlink starting with // but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('http://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, *.example.org, same website, different domain, if outlink starting with http:// but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('https://www.example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, *.example.org, same website, different domain, if outlink starting with https:// but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('//example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, *.example.org, same website, different domain, if outlink starting with // but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('http://example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, *.example.org, same website, different domain, if outlink starting with http:// but not going to same website');
        strictEqual(true, makeIsLinkToDifferentDomainButSamePiwikWebsite('https://example.org'), 'isLinkToDifferentDomainButSamePiwikWebsite, *.example.org, same website, different domain, if outlink starting with https:// but not going to same website');

        tracker.setDomains([document.domain]);
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('//' + document.domain), 'isLinkToDifferentDomainButSamePiwikWebsite, same website but also same domain => no need to add visitorIdUrl, if outlink starting with // but not going to same website');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('http://' + document.domain), 'isLinkToDifferentDomainButSamePiwikWebsite, same website but also same domain => no need to add visitorIdUrl, if outlink starting with http:// but not going to same website');
        strictEqual(false, makeIsLinkToDifferentDomainButSamePiwikWebsite('https://' + document.domain), 'isLinkToDifferentDomainButSamePiwikWebsite, same website but also same domain => no need to add visitorIdUrl, if outlink starting with https:// but not going to same website');

        // getCrossDomainLinkingUrlParameter() tests
        mockNowValue = 1520391713308;
        browserId = generateBrowserSpecificId();
            var expectedCrossDomainParam = 'pk_vid=082ea0f319e784f6' + Math.floor(mockNowValue / 1000) + browserId;
            equal(expectedCrossDomainParam, tracker.getCrossDomainLinkingUrlParameter(), "the cross domain parameter was not as expected");

        // sanity check (test that getCrossDomainLinkingUrlParameter() uses the same value as makeReplaceHrefForCrossDomainLink)
        equal('http://www.example.com?' + expectedCrossDomainParam, makeReplaceHrefForCrossDomainLink('http://www.example.com'));
    });

    test("Tracker getClassesRegExp()", function() {
        expect(5);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._getClassesRegExp, 'function', "getClassesRegExp" );

        var download = tracker.hook.test._getClassesRegExp([], 'download');
        ok( download.test('piwik_download'), 'piwik_download (default)' );

        ok( download.test('matomo_download'), 'matomo_download (default)' );

        var outlink = tracker.hook.test._getClassesRegExp([], 'link');
        ok( outlink.test('piwik_link'), 'piwik_link (default)' );
        ok( outlink.test('matomo_link'), 'matomo_link (default)' );

    });

    test("Tracker setIgnoreClasses() and getClassesRegExp(ignore)", function() {
        expect(14);

        var tracker = Piwik.getTracker();

        var ignore = tracker.hook.test._getClassesRegExp([], 'ignore');
        ok( ignore.test('piwik_ignore'), '[1] piwik_ignore' );
        ok( !ignore.test('pk_ignore'), '[1] !pk_ignore' );
        ok( !ignore.test('apiwik_ignore'), '!apiwik_ignore' );
        ok( !ignore.test('piwik_ignorez'), '!piwik_ignorez' );
        ok( ignore.test('abc piwik_ignore xyz'), 'abc piwik_ignore xyz' );

        tracker.setIgnoreClasses( 'my_download' );
        ignore = tracker.hook.test._getClassesRegExp(['my_download'], 'ignore');
        ok( ignore.test('piwik_ignore'), '[2] piwik_ignore' );
        ok( !ignore.test('pk_ignore'), '[2] !pk_ignore' );
        ok( ignore.test('my_download'), 'my_download' );
        ok( ignore.test('abc piwik_ignore xyz'), 'abc piwik_ignore xyz' );
        ok( ignore.test('abc my_download xyz'), 'abc my_download xyz' );

        tracker.setIgnoreClasses( ['my_download', 'my_outlink'] );
        ignore = tracker.hook.test._getClassesRegExp(['my_download','my_outlink'], 'ignore');
        ok( ignore.test('piwik_ignore'), '[3] piwik_ignore' );
        ok( !ignore.test('pk_ignore'), '[3] !pk_ignore' );
        ok( ignore.test('my_download'), 'my_download' );
        ok( ignore.test('my_outlink'), 'my_outlink' );
    });

    test("Tracker hasCookies(), getCookie(), setCookie()", function() {
        expect(2);

        var tracker = Piwik.getTracker();

        ok( tracker.hook.test._hasCookies() == '1', 'hasCookies()' );

        var cookieName = '_pk_test_harness' + Math.random(),
            expectedValue = String(Math.random());
        tracker.hook.test._setCookie( cookieName, expectedValue );
        equal( tracker.hook.test._getCookie( cookieName ), expectedValue, 'getCookie(), setCookie()' );
    });

    test("Tracker getCookieName() contains website ID", function() {
        expect(4);

        var tracker = Piwik.getTracker();
        tracker.setTrackerUrl("matomo.php");

        tracker.setSiteId(1);
        cookieName = tracker.hook.test._getCookieName('testing');
        ok( cookieName.indexOf('testing.1.') != -1);
        ok( cookieName.indexOf('testing.2.') == -1);

        tracker.setSiteId(2);
        cookieName = tracker.hook.test._getCookieName('testing-another');
        ok( cookieName.indexOf('testing-another.2.') != -1);
        ok( cookieName.indexOf('testing-another.1.') == -1);

    });

    test("Tracker getLinkTrackingTimer() setLinkTrackingTimer", function() {
        expect(2);

        var tracker = Piwik.getTracker();

        equal(500, tracker.getLinkTrackingTimer(), 'getLinkTrackingTimer, default is 500');
        tracker.setLinkTrackingTimer(1091);
        equal(1091, tracker.getLinkTrackingTimer(), 'setLinkTrackingTimer, changed value');
        tracker.setLinkTrackingTimer(500);
    });

    test("Tracker setDownloadExtensions(), addDownloadExtensions(), setDownloadClasses(), setLinkClasses(), and getLinkType()", function() {
        expect(72);

        var tracker = Piwik.getTracker();

        function runTests(messagePrefix) {

            equal( typeof tracker.hook.test._getLinkType, 'function', 'getLinkType' );

            equal( tracker.hook.test._getLinkType('something', 'goofy.html', false, false), 'link', messagePrefix + 'implicit link' );
            equal( tracker.hook.test._getLinkType('something', 'goofy.pdf', false, false), 'download', messagePrefix + 'external PDF files are downloads' );
            equal( tracker.hook.test._getLinkType('something', 'goofy.pdf', true, false), 'download', messagePrefix + 'local PDF are downloads' );
            equal( tracker.hook.test._getLinkType('something', 'goofy-with-dash.pdf', true, false), 'download', messagePrefix + 'local PDF are downloads' );

            equal( tracker.hook.test._getLinkType('piwik_download', 'piwiktest.ext', true, false), 'download', messagePrefix + 'piwik_download' );
            equal( tracker.hook.test._getLinkType('abc piwik_download xyz', 'piwiktest.ext', true, false), 'download', messagePrefix + 'abc piwik_download xyz' );
            equal( tracker.hook.test._getLinkType('piwik_link', 'piwiktest.asp', true, false), 'link', messagePrefix+ 'piwik_link' );
            equal( tracker.hook.test._getLinkType('abc piwik_link xyz', 'piwiktest.asp', true, false), 'link', messagePrefix + 'abc piwik_link xyz' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true, false), 'download', messagePrefix + 'download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.ext', true, false), 0, messagePrefix + '[1] link (default)' );

            equal( tracker.hook.test._getLinkType('something', 'file.zip', true, false), 'download', messagePrefix + 'download file.zip' );
            equal( tracker.hook.test._getLinkType('something', 'index.php?name=file.zip#anchor', true, false), 'download', messagePrefix + 'download file.zip (anchor)' );
            equal( tracker.hook.test._getLinkType('something', 'index.php?name=file.zip&redirect=yes', true, false), 'download', messagePrefix + 'download file.zip (is param)' );
            equal( tracker.hook.test._getLinkType('something', 'file.zip?mirror=true', true, false), 'download', messagePrefix + 'download file.zip (with param)' );

            tracker.setDownloadExtensions('pk');
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true, false), 'download', messagePrefix + '[1] .pk == download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true, false), 0, messagePrefix + '.txt =! download extension' );

            tracker.addDownloadExtensions('xyz');
            tracker.addDownloadExtensions(['abc','zz']);
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true, false), 'download', messagePrefix + '[2] .pk == download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.xyz', true, false), 'download', messagePrefix + '.xyz == download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.abc', true, false), 'download', messagePrefix + '.abc == download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.zz', true, false), 'download', messagePrefix + '.zz == download extension' );

            tracker.removeDownloadExtensions(['xyz','pk']);
            tracker.removeDownloadExtensions('zz');
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true, false), 0, messagePrefix + '[2] .pk =! download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.xyz', true, false), 0, messagePrefix + '.xyz =! download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.abc', true, false), 'download', messagePrefix + '.abc == download extension' );
            equal( tracker.hook.test._getLinkType('something', 'piwiktest.zz', true, false), 0, messagePrefix + '.zz =! download extension' );

            tracker.setDownloadClasses(['a', 'b']);
            equal( tracker.hook.test._getLinkType('abc piwik_download', 'piwiktest.ext', true, false), 'download', messagePrefix + 'download (default)' );
            equal( tracker.hook.test._getLinkType('abc a', 'piwiktest.ext', true, false), 'download', messagePrefix + 'download (a)' );
            equal( tracker.hook.test._getLinkType('b abc', 'piwiktest.ext', true, false), 'download', messagePrefix + 'download (b)' );

            tracker.setLinkClasses(['c', 'd']);
            equal( tracker.hook.test._getLinkType('abc piwik_link', 'piwiktest.ext', true, false), 'link', messagePrefix + 'link (default)' );
            equal( tracker.hook.test._getLinkType('abc c', 'piwiktest.ext', true, false), 'link', messagePrefix + 'link (c)' );
            equal( tracker.hook.test._getLinkType('d abc', 'piwiktest.ext', true, false), 'link', messagePrefix + 'link (d)' );

            // links containing a download attribute are always downloads
            equal( tracker.hook.test._getLinkType('test', 'index.html', false, true), 'download', 'download attribute' );
            equal( tracker.hook.test._getLinkType('piwik_link', 'index.html', true, true), 'link', messagePrefix + ' download attribute, but link class' );
            equal( tracker.hook.test._getLinkType('piwik_download', 'test.pdf', true, true), 'download', messagePrefix + ' download attribute' );
        }

        var trackerUrl = tracker.getTrackerUrl();
        var downloadExtensions = tracker.getConfigDownloadExtensions();
        tracker.setTrackerUrl('');
        tracker.setDownloadClasses([]);
        tracker.setLinkClasses([]);

        equal( tracker.hook.test._getLinkType('something', 'matomo.php', false), 'link', 'an empty tracker url should not match configtrackerurl' );

        runTests('without tracker url, ');

        tracker.setTrackerUrl('matomo.php');
        tracker.setDownloadClasses([]);
        tracker.setLinkClasses([]);
        tracker.setDownloadExtensions(downloadExtensions);

        runTests('with tracker url, ');

        equal( tracker.hook.test._getLinkType('something', 'matomo.php', true, false), 0, 'matches tracker url and should never return any tracker Url' );
        equal( tracker.hook.test._getLinkType('something', 'matomo.php?redirecturl=http://example.com/test.pdf', true, false), 0, 'should not match download as is config tracker url' );
        equal( tracker.hook.test._getLinkType('something', 'matomo.php?redirecturl=http://example.com/', true, false), 0, 'should not match link as is config tracker url' );

        tracker.setTrackerUrl(trackerUrl);
    });

    function getVisitorIdFromCookie(tracker) {
        visitorCookieName = tracker.hook.test._getCookieName('id');
        visitorCookieValue = tracker.hook.test._getCookie(visitorCookieName);
        return visitorCookieValue ? visitorCookieValue.split('.')[0] : '';
    }

    test("User ID and Visitor UUID", function() {
        expect(28);
        deleteCookies();

        var userIdString = 'userid@mydomain.org';

        var tracker = Piwik.getTracker();

        // Force the cookie to be created....
        var visitorId = tracker.getVisitorId();
        tracker.trackPageView();

        // Check cookie was created
        ok(getVisitorIdFromCookie(tracker).length == 16, "Visitor ID from cookie should be 16 chars, got: " + getVisitorIdFromCookie(tracker));
        equal(getVisitorIdFromCookie(tracker), visitorId, "Visitor ID from cookie is the same as Visitor ID in object");
        equal(tracker.getVisitorId(), visitorId, "After tracking an action and updating the ID cookie, the visitor ID is still the same.");

        // Visitor ID is by default set to a UUID fingerprint
        var hashUserId = tracker.hook.test._sha1(userIdString).slice(0, 16);
        notEqual(hashUserId, tracker.getVisitorId(), "Visitor ID " + tracker.getVisitorId() + " is not yet the hash of User ID " + hashUserId);
        notEqual("", tracker.getVisitorId(), "Visitor ID is not empty");
        ok( tracker.getVisitorId().length === 16, "Visitor ID is 16 chars string");

        // Check that Visitor ID is the same when requested multiple times
        var visitorId = tracker.getVisitorId();
        equal(visitorId, tracker.getVisitorId(), "Visitor ID is the same when called multiple times");

        tracker.setVisitorId('invalid'); // invalid characters
        tracker.setVisitorId('012345abc'); // too short
        tracker.setVisitorId('');
        equal(visitorId, tracker.getVisitorId(), "Visitor ID is not updated when invalid");

        // Check that setting an empty user id will not change the visitor ID
        var userId = '';
        equal(userId, tracker.getUserId(), "by default user ID is set to empty string");
        tracker.setUserId(userId);
        equal(userId, tracker.getUserId(), "after setting to empty string, user id is still empty");
        equal(getVisitorIdFromCookie(tracker), tracker.getVisitorId(), "visitor id in cookie was not yet changed after setting empty user id");
        tracker.trackPageView("Track some data to write the cookies...");
        equal(visitorId, tracker.getVisitorId(), "visitor id was not changed after setting empty user id and tracking an action");
        equal(getVisitorIdFromCookie(tracker), tracker.getVisitorId(), "visitor id in cookie was not changed");


        // Building another 'tracker2' object so we can compare behavior to 'tracker'
        var tracker2 = Piwik.getTracker();
        equal(tracker.getVisitorId(), tracker2.getVisitorId(), "Visitor ID " + tracker.getVisitorId() + " is the same as Visitor ID 2 " + tracker2.getVisitorId());
        notEqual("", tracker2.getVisitorId(), "Visitor ID 2 is not empty");
        tracker2.setCookieNamePrefix("differentNamespace");
        notEqual("", tracker2.getVisitorId(), "Visitor ID 2 is not empty");
        notEqual(tracker.getVisitorId(), tracker2.getVisitorId(), "Setting a new namespace forces Visitor ID " + tracker.getVisitorId() + " to be different from Visitor ID 2 " + tracker2.getVisitorId());

            // Set the same Visitor IDs in both trackers
            tracker2.setVisitorId(tracker.getVisitorId());

        // set userId works with a number
        tracker.setUserId(5939383);
        equal(5939383, tracker.getUserId(), "getUserId() returns numeric User Id");

        // Set User ID and verify it was set
        tracker.setUserId(userIdString);
        equal(userIdString, tracker.getUserId(), "getUserId() returns User Id");
            notEqual(tracker.hook.test._sha1(userIdString).slice(0, 16), tracker.getVisitorId(), "Visitor ID is not the sha1 of User ID (it used to be)");
            equal(tracker.getVisitorId(), tracker2.getVisitorId(), "After setting a User ID, Visitor ID does not change");

            // Set the User ID and verify nothing's changed
            tracker2.setUserId(userIdString);
            equal(tracker.getVisitorId(), tracker2.getVisitorId(), "After setting the same User ID, Visitor ID are the same");

        // Check that calling trackPageView does not change the visitor ID
        var visitorId = tracker.getVisitorId();
        tracker.trackPageView();
        equal(getVisitorIdFromCookie(tracker), visitorId, "Visitor ID from cookie is the same as Visitor ID in object ("+ visitorId +"), but got: " + getVisitorIdFromCookie(tracker));

        // Verify that when resetting the User ID, it also changes the Visitor ID
        tracker.resetUserId();
        equal(tracker.getUserId(), '', "after reset, user ID is set to empty string");
        ok(tracker.getVisitorId().length == 16, "after resetting user id, visitor ID should still be 16 chars, got: " + tracker.getVisitorId());
            equal(tracker.getVisitorId(), visitorId, "after resetting user id, visitor ID should be the same ("+ tracker.getVisitorId() +")");
        tracker.trackPageView("Track some data to write the cookies...");
        ok(getVisitorIdFromCookie(tracker).length == 16, "after resetting user id, visitor ID from cookie should still be 16 chars, got: " + getVisitorIdFromCookie(tracker));
            equal(getVisitorIdFromCookie(tracker), visitorId, "after resetting user id, visitor ID from cookie should be the same ("+ getVisitorIdFromCookie(tracker) +")");

    });

    test("utf8_encode(), sha1()", function() {
        expect(6);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._utf8_encode, 'function', 'utf8_encode' );
        equal( tracker.hook.test._utf8_encode('hello world'), '<?php echo utf8_encode("hello world"); ?>', 'utf8_encode("hello world")' );
        equal( tracker.hook.test._utf8_encode('Gesamtgröße'), '<?php echo utf8_encode("Gesamtgröße"); ?>', 'utf8_encode("Gesamtgröße")' );
        equal( tracker.hook.test._utf8_encode('您好'), '<?php echo utf8_encode("您好"); ?>', 'utf8_encode("您好")' );

        equal( typeof tracker.hook.test._sha1, 'function', 'sha1' );
        equal( tracker.hook.test._sha1('hello world'), '<?php echo sha1("hello world"); ?>', 'sha1("hello world")' );
    });

    test("getRequest()", function() {
        expect(2);

        var tracker = Piwik.getTracker('hostname', 4);

        tracker.setCustomData("key is X", "value is Y");
        var requestString = tracker.getRequest('hello=world');
        equal( requestString.indexOf('hello=world&idsite=4&rec=1&r='), 0, "Request string " + requestString);

        ok( -1 !== tracker.getRequest('hello=world').indexOf('send_image=0'), 'should disable sending image response');
    });

    test("POST requests are sent with cookies", function() {
        expect(5);

        var tracker = Piwik.getTracker();
        // tracker.disableBrowserFeatureDetection(); // avoid client hint queue
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        tracker.setCustomData({ "token": '---' });
        tracker.setRequestMethod('POST');
        ok(tracker.isUsingAlwaysUseSendBeacon());
        tracker.setRequestMethod('GeT');
        ok(!tracker.isUsingAlwaysUseSendBeacon());

        tracker.setRequestMethod('POST');
        tracker.disableAlwaysUseSendBeacon(); // disable send beacon to force sending a xhr
        var callbackCalled = false;
        tracker.trackPageView('withCredentialsTest', null, function (event) {
            callbackCalled = true;
            ok(event.success, 'succeeded');
            ok(event.xhr && event.xhr.withCredentials, 'withCredentials is true');
        });

        stop();
        setTimeout(function() {
            ok(callbackCalled, 'called the callback');

            start();
        }, 2000);
    });

    // support for setCustomRequestProcessing( customRequestContentProcessingLogic )
    test("Tracker setCustomRequestProcessing() and getRequest()", function() {
        expect(4);

        var tracker = Piwik.getTracker("trackerUrl", "42");

        tracker.setCustomRequestProcessing(function(request){
          var pairs = request.split('&');
          var result = {};
          pairs.forEach(function(pair) {
            pair = pair.split('=');
            result[pair[0]] = decodeURIComponent(pair[1] || '');
          });
          return JSON.stringify(result);
        });

        var json = JSON.parse(tracker.getRequest('hello=world'));
        equal( json.hello, 'world');
        equal( json.idsite, '42' );
        equal( json.rec, 1);
        ok( json.r.length > 0 );
    });

    // support for addPlugin( pluginName, pluginObj )
    test("Tracker addPlugin() and getRequest()", function() {
        expect(12);

        var tracker = Piwik.getTracker();

        var litp = (function() {
          var lastInteractionType = "";
          function ecommerce() { lastInteractionType = "ecommerce"; }
          function event() { lastInteractionType = "event"; }
          function goal() { lastInteractionType = "goal"; }
          function link() { lastInteractionType = "link"; }
          function load() { lastInteractionType = "load"; }
          function log() { lastInteractionType = "log"; }
          function ping() { lastInteractionType = "ping"; return "&dummy=1"; }
          function sitesearch() { lastInteractionType = "sitesearch"; }
          function unload() { lastInteractionType = "unload"; }
          function getLastInteractionType() { return lastInteractionType; }

          return {
            ecommerce: ecommerce,
            event : event,
            goal : goal,
            link : link,
            load : load,
            log : log,
            ping: ping,
            sitesearch : sitesearch,
            unload : unload,
            getLastInteractionType: getLastInteractionType
          };
        })();

        tracker.addPlugin("interactionTypePlugin", litp);

        ok(litp.getLastInteractionType() !== 'ecommerce');
        tracker.trackEcommerceOrder("ORDER ID YES", 666.66);
        ok(litp.getLastInteractionType() === 'ecommerce');

        ok(litp.getLastInteractionType() !== 'event');
        tracker.trackEvent("Event Category", "Event Action");
        ok(litp.getLastInteractionType() === 'event');

        ok(litp.getLastInteractionType() !== 'goal');
        tracker.trackGoal(42);
        ok(litp.getLastInteractionType() === 'goal');

        ok(litp.getLastInteractionType() !== 'link');
        tracker.trackLink("http://example.ca", "link");
        ok(litp.getLastInteractionType() === 'link');

        ok(litp.getLastInteractionType() !== 'log');
        tracker.trackPageView();
        ok(litp.getLastInteractionType() === 'log');

        ok(litp.getLastInteractionType() !== 'sitesearch');
        tracker.trackSiteSearch("search Keyword");
        ok(litp.getLastInteractionType() === 'sitesearch');
    });

    test("prefixPropertyName()", function() {
        expect(3);

        var tracker = Piwik.getTracker();

        equal( typeof tracker.hook.test._prefixPropertyName, 'function', 'prefixPropertyName' );
        equal( tracker.hook.test._prefixPropertyName('', 'hidden'), 'hidden', 'no prefix' );
        equal( tracker.hook.test._prefixPropertyName('webkit', 'hidden'), 'webkitHidden', 'webkit prefix' );
    });

    test("Generate error messages when calling an undefined API method", function() {
        expect(2);

        // temporarily reset the console error logger so our errors don't show up in the console log while running tests.
        var console = {};
        var errorCallBack = console.error;
        window.console.error = function() {};

        // Calling undefined methods should generate an error
        function callNonExistingMethod() {
            _paq.push(['NonExistingFunction should error and display the error in the console']);
        }
        function callNonExistingMethodWithParameter() {
            _paq.push(['NonExistingFunction should not error', 'this is a parameter']);
        }

        throws( callNonExistingMethod, /was not found in "_paq" variable/, 'Expected to raise an error when calling an undefined method.');
        throws( callNonExistingMethodWithParameter, /was not found in "_paq" variable/, 'Expected to raise an error when calling an undefined method with parameters.');

        window.console.error = errorCallBack;
    });

    test("Overlay URL Normalizer", function() {
        expect(23);

        var test = function(testCases) {
            for (var i = 0; i < testCases.length; i++) {
                var observed = Piwik_Overlay_UrlNormalizer.normalize(testCases[i][0]);
                var expected = testCases[i][1];
                equal(observed, expected, testCases[i][0]);
            }
        };

        Piwik_Overlay_UrlNormalizer.initialize();
        Piwik_Overlay_UrlNormalizer.setExcludedParameters(['excluded1', 'excluded2', 'excluded3']);

        Piwik_Overlay_UrlNormalizer.setBaseHref(false);

        Piwik_Overlay_UrlNormalizer.setCurrentDomain('example.com');
        Piwik_Overlay_UrlNormalizer.setCurrentUrl('https://www.example.com/current/test.html?asdfasdf');

        test([
            [
                'relative/path/',
                'example.com/current/relative/path/'
            ], [
                'http://www.example2.com/path/foo.html',
                'example2.com/path/foo.html'
            ]
        ]);

        Piwik_Overlay_UrlNormalizer.setCurrentDomain('www.example3.com');
        Piwik_Overlay_UrlNormalizer.setCurrentUrl('http://example3.com/current/folder/');

        test([[
            'relative.html',
            'example3.com/current/folder/relative.html'
        ]]);

        Piwik_Overlay_UrlNormalizer.setBaseHref('http://example.com/base/');

        test([
            [
                'http://www.example2.com/my/test/path.html?id=2&excluded2=foo#MyAnchor',
                'example2.com/my/test/path.html?id=2#MyAnchor'
            ], [
                '/my/test/foo/../path.html?excluded1=foo&excluded2=foo&excluded3=foo',
                'example3.com/my/test/path.html'
            ], [
                'path/./test//test///foo.bar?excluded2=foo&id=3',
                'example.com/base/path/test/test/foo.bar?id=3'
            ], [
                'path/./test//test///foo.bar?excluded2=foo#Anchor',
                'example.com/base/path/test/test/foo.bar#Anchor'
            ], [
                'https://example2.com//test.html?id=3&excluded1=foo&bar=baz#asdf',
                'example2.com/test.html?id=3&bar=baz#asdf'
            ], [
                '#',
                ''
            ], [
                '#Anchor',
                ''
            ], [
                '/',
                'example3.com/'
            ]
        ]);


        var tracker = Piwik.getTracker();

        // test getPiwikUrlForOverlay
        var getMatomoUrlForOverlay = tracker.hook.test._getMatomoUrlForOverlay;

        equal( typeof getMatomoUrlForOverlay, 'function', 'getMatomoUrlForOverlay' );
        equal( getMatomoUrlForOverlay('http://www.example.com/js/tracker.php?version=232323'), 'http://www.example.com/', 'with query and js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/tracker.php?version=232323'), 'http://www.example.com/', 'with query and no js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/js/tracker.php'), 'http://www.example.com/', 'no query, custom tracker and js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/tracker.php'), 'http://www.example.com/', 'no query, custom tracker and no js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/js/matomo.php'), 'http://www.example.com/', 'with matomo.php and no js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/matomo.php'), 'http://www.example.com/', 'with matomo.php and no js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/master/js/matomo.php'), 'http://www.example.com/master/', 'installed in custom folder and js folder' );
        equal( getMatomoUrlForOverlay('http://www.example.com/master/matomo.php'), 'http://www.example.com/master/', 'installed in custom folder and no js folder' );
        equal( getMatomoUrlForOverlay('/matomo.php'), toAbsoluteUrl('/'), 'only matomo.php with leading slash' );
        equal( getMatomoUrlForOverlay('matomo.php'), toAbsoluteUrl(''), 'only matomo.php' );
        equal( getMatomoUrlForOverlay('/matomo.php?version=1234'), toAbsoluteUrl('/'), 'only matomo.php with leading slash with query' );
    });

    function generateAnIframeInDocument() {
        // Generate an iframe, and call the method inside the iframe to check it returns true
        var hostAndPath = $(location).attr('pathname');
        var iframe = document.createElement('iframe');
        iframe.id = "iframeTesting";
        iframe.style = "display : none";
        var html = '\
            <html><body> \
            <scr' + 'ipt src="' + hostAndPath + '../../js/piwik.js?rand=<?php echo $cacheBuster; ?>" type="text/javascript"></sc' + 'ript> \
            <scr' + 'ipt src="' + hostAndPath + 'matomotest.js" type="text/javascript"></sc' + 'ript> \
            <scr' + 'ipt src="' + hostAndPath + '../../node_modules/jquery/dist/jquery.min.js" type="text/javascript"></sc' + 'ript> \
            <scr' + 'ipt type="text/javascript"> \
            window.onload = function() { \
                $(document).ready(function () { \
                    window.iframeIsLoaded = true; \
                    window.isInsideIframe = function () { \
                        var tracker = Piwik.getTracker(); \
                        return tracker.hook.test._isInsideAnIframe(); \
                    }; \
                });\
            }; \
            window.iframeIsLoaded = false; \
            \
            </sc' + 'ript> \
            </body></html>\
        ';

        document.body.appendChild(iframe);
        iframe.contentWindow.document.open();
        iframe.contentWindow.document.write(html);
        iframe.contentWindow.document.close();

    };

    test("isInsideAnIframe", function() {

        expect(6);
        var tracker = Piwik.getTracker();
        var isInsideAnIframe = tracker.hook.test._isInsideAnIframe;
        equal( typeof isInsideAnIframe, 'function', 'isInsideAnIframe' );
        equal( isInsideAnIframe(), false, 'these tests are not running inside an iframe, got: ' + isInsideAnIframe());
        equal( !isInsideAnIframe(), true, 'these tests are not running inside an iframe');
        equal( document.getElementById("iframeTesting"), undefined, 'the iframe is not loaded yet...');

        generateAnIframeInDocument();


        stop();
        setTimeout(function() {
            equal( document.getElementById("iframeTesting").contentWindow.iframeIsLoaded, true, 'the iframe is loaded now!');
            equal( document.getElementById("iframeTesting").contentWindow.isInsideIframe(), true, 'inside an iframe, isInsideAnIframe() returns true');

            start();

        }, 4000); // wait for iframe to load

    });

    <?php
if ($mysql) {
    ?>

    module("request", {
        setup: function () {
            mockNowValue = null;
            ok(true, "request.setup");

            deleteCookies();
            wait(500);
            ok(document.cookie === "", "deleteCookies");
        },
        teardown: function () {
            mockNowValue = null;
            ok(true, "request.teardown");
        }
    });

    test("tracking with sendBeacon", function() {
        expect(11);

        var tracker = Piwik.getTracker();
        tracker.disableBrowserFeatureDetection(); // avoid client hint queue
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        tracker.setCustomData({ "token" : getAlwaysUseSendBeaconToken() });
        tracker.alwaysUseSendBeacon();

        var shortTitle = 'CustomShortTitleTest';
        var longTitle = "CustomLongTitleTest" + (Array(2500).join('f'));
        tracker.trackPageView(shortTitle);
        var callbackCalled = false;
        tracker.trackPageView(longTitle, null, function (event) {
            callbackCalled = true;
            ok(event.success, 'succeeded');
            ok(event.request.indexOf('action_name=') === 0, 'contains request');
        });

        tracker.queueRequest('action_name=Queue1'); // these 2 will be sent as bulk request
        tracker.queueRequest('action_name=Queue2');

        stop();
        setTimeout(function() {
            ok(callbackCalled, 'called the callback');

            var xhr = makeXhr();
            xhr.open("GET", "matomo.php?requests=" + getAlwaysUseSendBeaconToken(), false);
            xhr.send(null);
            var results = xhr.responseText;
            var m = /<span\>([0-9]+)\<\/span\>/.exec(results);
            equal( m ? m[1] : 0, "4", "count tracking events" );

            ok(results.indexOf('matomo.php?action_name=' + shortTitle + '&') >= 0, "trackPageView() sends small request");
            ok(results.indexOf('matomo.php?action_name=' + longTitle + '&') >= 0, "trackPageView() sends long request");
            ok(results.indexOf('matomo.php?action_name=Queue1&') >= 0, "queueRequest() sends bulk request 1");
            ok(results.indexOf('matomo.php?action_name=Queue2&') >= 0, "queueRequest() sends bulk request 2");

            start();
        }, 6000);
    });

    test("referrer attribution without tracking", function() {
        expect(6);

        // Check visitor referrer attribution cookie is set on unload without a tracking request
        var tracker = Piwik.getTracker();
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        tracker.setReferrerUrl('http://www.google.fr/?query=test');
        equal( tracker.getAttributionReferrerUrl(), "", "getAttributionReferrerUrl() is empty on load")
        tracker.hook.test._beforeUnloadHandler();
        equal( tracker.getAttributionReferrerUrl(), 'http://www.google.fr/?query=test', "getAttributionReferrerUrl() is set after unload")

        var tracker2 = Piwik.getTracker();
        tracker2.setTrackerUrl("matomo.php");
        tracker2.setSiteId(1);
        tracker2.setReferrerUrl('');
        equal( tracker2.getAttributionReferrerUrl(), 'http://www.google.fr/?query=test', "getAttributionReferrerUrl() should be read from cookie in new tracker")
    });

    test("referrer ignore list", function() {
        expect(25);

        var testCases = [
            ['no exclusion', 'https://www.google.fr/?query=test', '', false],
            ['host exclusion matches', 'https://www.google.fr/?query=test', 'www.google.fr', true],
            ['host exclusion matches (www ignored)', 'https://google.fr/?query=test', 'www.google.fr', true],
            ['host exclusion matches (www ignored)', 'https://www.google.fr/?query=test', 'google.fr', true],
            ['host exclusion not matching', 'https://www.google.de/?query=test', 'www.google.fr', false],
            ['wildcard subdomain exclusion matches', 'https://www.google.fr/?query=test', '*.google.fr', true],
            ['paypal.com is excluded by default', 'https://www.paypal.com/proceed/payment/', '', true],
            ['host with path exclusion matches', 'https://www.paypal.com/proceed/payment/', 'www.paypal.com/proceed/', true],
            ['host with path exclusion not matching', 'https://www.paypal.com/proceed/payment/', 'www.paypal.com/proceed/shipping', false],
            ['host with wild card path exclusion matches', 'https://www.paypal.com/proceed/payment/', 'www.paypal.com/proceed*', true],
            ['host with wild card path exclusion matches again', 'https://www.paypal.com/proceed-my-payment/', 'www.paypal.com/proceed*', true],
        ];

        for (var i=0; i < testCases.length; i++) {
            var testName = testCases[i][0];
            var referrerUrl = testCases[i][1];
            var excludedReferrer = testCases[i][2];
            var result = testCases[i][3];
            var expectedExcludedReferrer = [];

            var tracker = Piwik.getTracker();
            tracker.setTrackerUrl("matomo.php");
            tracker.setSiteId(1);
            tracker.setReferrerUrl(referrerUrl);
            if (excludedReferrer) {
                tracker.setExcludedReferrers(excludedReferrer);
                expectedExcludedReferrer = tracker.hook.test._isString(excludedReferrer) ? [excludedReferrer] : excludedReferrer;
            } else {
                expectedExcludedReferrer = ['.paypal.com'];
            }
            deepEqual(tracker.getExcludedReferrers(), expectedExcludedReferrer, testName + " - check getExcludedReferrers()");
            deepEqual(tracker.hook.test._isReferrerExcluded(referrerUrl), result, testName + " - check isReferrerExcluded()");
        }
    });

    test("tracking", function() {
        expect(185);

        // Prevent Opera and HtmlUnit from performing the default action (i.e., load the href URL)
        var stopEvent = function (evt) {
                evt = evt || window.event;

//              evt.cancelBubble = true;
                evt.returnValue = false;

                if (evt.preventDefault)
                    evt.preventDefault();
//              if (evt.stopPropagation)
//                  evt.stopPropagation();

//              return false;
            };

        var tracker = Piwik.getTracker();
        tracker.disableBrowserFeatureDetection(); // avoid client hint queue
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);

        strictEqual(0, tracker.getNumTrackedPageViews(), 'getNumTrackedPageViews, is zero by default');

        var piwikUrl = location.href;
        if (piwikUrl.indexOf('?') > 0) {
            piwikUrl = piwikUrl.slice(0, piwikUrl.indexOf('?'));
        }
        equal(tracker.getPiwikUrl(), piwikUrl, "getPiwikUrl, relative tracker url" );

        tracker.setTrackerUrl("http://apache.piwik/matomo.php");
        equal(tracker.getPiwikUrl(), 'http://apache.piwik/', "getPiwikUrl, in root directory" );

        tracker.setTrackerUrl("http://apache.piwik/tracker.php");
        equal(tracker.getPiwikUrl(), 'http://apache.piwik/', "getPiwikUrl, with different file name" );

        tracker.setTrackerUrl("http://apache.piwik/tests/javascript/matomo.php?x=1");
        equal(tracker.getPiwikUrl(), 'http://apache.piwik/tests/javascript/', "getPiwikUrl, with path and query" );

        tracker.setTrackerUrl("http://apache.piwik/js/matomo.php?x=1");
        equal(tracker.getPiwikUrl(), 'http://apache.piwik/', "getPiwikUrl, when using unminified piwik.js" );

        tracker.setTrackerUrl("matomo.php");

        var thirteenMonths  = 1000 * 60 * 60 * 24 * 393;
        strictEqual(thirteenMonths, tracker.getConfigVisitorCookieTimeout(), 'default visitor timeout should be 13 months');

        var actualTimeout   = tracker.getRemainingVisitorCookieTimeout();
        var isAbout13Months = (thirteenMonths + 1000) > actualTimeout && ((thirteenMonths - 6000) < actualTimeout);
        ok(isAbout13Months, 'remaining cookieTimeout should be about the deault tiemout of 13 months (' + thirteenMonths + ') but is ' + actualTimeout);

        equal(tracker.getCurrentUrl(), location.href, "getCurrentUrl, when no custom url set" );

        var visitorIdStart = tracker.getVisitorId();
        // need to wait at least 1 second so that the cookie would be different, if it wasnt persisted
        wait(2000);
        var visitorIdStart2 = tracker.getVisitorId();
        ok( visitorIdStart == visitorIdStart2, "getVisitorId() same when called twice with more than 1 second delay");
        var customUrl = "http://localhost.localdomain/?utm_campaign=YEAH&utm_term=RIGHT!";
        tracker.setCustomUrl(customUrl);

        equal(tracker.getCurrentUrl(), customUrl, "getCurrentUrl, when custom url set" );

        tracker.setCustomData({ "token" : getToken() });
        var data = tracker.getCustomData();
        ok( getToken() != "" && data.token == data["token"] && data.token == getToken(), "setCustomData() , getCustomData()" );

        // Custom variables with integer/float values
        tracker.setCustomVariable(1, 1, 2, "visit");
        deepEqual( tracker.getCustomVariable(1, "visit"), ["1", "2"], "setCustomVariable() with integer name/value" );
        tracker.setCustomVariable(1, 1, 0, "visit");
        deepEqual( tracker.getCustomVariable(1, "visit"), ["1", "0"], "setCustomVariable() with integer name/value" );
        tracker.setCustomVariable(2, 1.05, 2.11, "visit");
        deepEqual( tracker.getCustomVariable(2, "visit"), ["1.05", "2.11"], "setCustomVariable() with integer name/value" );

        // custom variables with undefined names or values
        tracker.setCustomVariable(5);// setting a custom variable with no name and no value should not error
        deepEqual( tracker.getCustomVariable(5), false, "getting a custom variable with no name nor value" );
        deepEqual( tracker.getCustomVariable(55), false, "getting a custom variable with no name nor value" );
        tracker.setCustomVariable(5, "new name");
        deepEqual( tracker.getCustomVariable(5), ["new name", ""], "getting a custom variable with no value" );
        tracker.deleteCustomVariable(5);

        equal(tracker.getCustomDimension(94), null, "if no custom dimension for this index is specified should return null");
        equal(tracker.getCustomDimension(-1), null, "if custom dimension index is invalid should return null");
        equal(tracker.getCustomDimension('not valid'), null, "if custom dimension index is invalid should return null");
        tracker.setCustomDimension(1, 5);
        equal(tracker.getCustomDimension(1), "5", "set custom dimension should convert any value to a string" );
        tracker.setCustomDimension(1, "my custom value with éàç&*() EOL");
        equal(tracker.getCustomDimension(1), "my custom value with éàç&*() EOL", "should get stored custom dimension value" );
        tracker.setCustomDimension(2, undefined);
        equal(tracker.getCustomDimension(2), "", "setCustomDimension should convert undefined to an empty string" );

        tracker.setCustomDimension(3, 'my third value');
        equal(tracker.getCustomDimension(3), "my third value", "deleteCustomDimension verify a value is set for this dimension" );
        tracker.deleteCustomDimension(3);
        equal(tracker.getCustomDimension(3), null, "deleteCustomDimension verify value was removed" );

        tracker.setCustomVariable(1, "new visit1", 'val1', 'visit');
        tracker.setCustomVariable(5, "new visit5", 'val5');
        tracker.setCustomVariable(2, "new page2", 'pageval2', 'page');
        tracker.setCustomVariable(5, "new page5", 'pageval5', 'page');
        tracker.setCustomVariable(3, "new event3", 'eventval3', 'event');
        tracker.setCustomVariable(5, "new event5", 'eventval5', 'event');

        deepEqual( tracker.getCustomVariable(1), ["new visit1", "val1"], "deleteCustomVariables, getting a custom variable" );
        deepEqual( tracker.getCustomVariable(5, 'visit'), ["new visit5", "val5"], "deleteCustomVariables, getting a custom variable" );
        deepEqual( tracker.getCustomVariable(2, 'page'), ["new page2", "pageval2"], "deleteCustomVariables, getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(5, 'page'), ["new page5", "pageval5"], "deleteCustomVariables, getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(3, 'event'), ["new event3", "eventval3"], "deleteCustomVariables, getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(5, 'event'), ["new event5", "eventval5"], "deleteCustomVariables, getting a page custom variable" );

        tracker.deleteCustomVariables('visit');

        deepEqual( tracker.getCustomVariable(1), false, "deleteCustomVariables, unsets visit variables only" );
        deepEqual( tracker.getCustomVariable(5, 'visit'), false, "deleteCustomVariables, unsets visit variables only" );
        deepEqual( tracker.getCustomVariable(2, 'page'), ["new page2", "pageval2"], "getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(5, 'page'), ["new page5", "pageval5"], "getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(3, 'event'), ["new event3", "eventval3"], "getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(5, 'event'), ["new event5", "eventval5"], "getting a page custom variable" );

        tracker.deleteCustomVariables('page');

        deepEqual( tracker.getCustomVariable(2, 'page'), false, "deleteCustomVariables, unsets visit variables only" );
        deepEqual( tracker.getCustomVariable(5, 'page'), false, "deleteCustomVariables, unsets page variables only" );
        deepEqual( tracker.getCustomVariable(3, 'event'), ["new event3", "eventval3"], "getting a page custom variable" );
        deepEqual( tracker.getCustomVariable(5, 'event'), ["new event5", "eventval5"], "getting a page custom variable" );

        tracker.deleteCustomVariables('event');

        deepEqual( tracker.getCustomVariable(3, 'event'), false, "deleteCustomVariables, unsets event variables only" );
        deepEqual( tracker.getCustomVariable(5, 'event'), false, "deleteCustomVariables, unsets event variables only" );

        tracker.setDocumentTitle("PiwikTest");

        var referrerUrl = "http://referrer.example.com/page/sub?query=test&test2=test3";
        tracker.setReferrerUrl(referrerUrl);

        referrerTimestamp = Math.round(new Date().getTime() / 1000);
        tracker.trackPageView();

        strictEqual(1, tracker.getNumTrackedPageViews(), 'getNumTrackedPageViews, should increase num pageview counter');

        tracker.setExcludedReferrers('ignored.referrer.url')
        tracker.setReferrerUrl('http://ignored.referrer.url/path/page?query=string');
        tracker.trackPageView();

        strictEqual(2, tracker.getNumTrackedPageViews(), 'getNumTrackedPageViews, should increase num pageview counter');

        tracker.setReferrerUrl('http://another.referrer.url/path/page');
        tracker.setCustomUrl(customUrl  + '&ignore_referrer=1');
        tracker.trackPageView();

        tracker.setReferrerUrl(referrerUrl);
        tracker.setCustomUrl(customUrl);

        strictEqual(3, tracker.getNumTrackedPageViews(), 'getNumTrackedPageViews, should increase num pageview counter');

        var idPageview = tracker.getConfigIdPageView();
        ok(/([0-9a-zA-Z]){6}/.test(idPageview), 'trackPageview, should generate a random pageview id');

        equal(tracker.getCustomDimension(1), "my custom value with éàç&*() EOL", "custom dimensions should not be cleared after a tracked pageview");
        equal(tracker.getCustomDimension(2), "", "custom dimensions should not be cleared after a tracked pageview");

        tracker.trackPageView("CustomTitleTest", {dimension2: 'my new value', dimension5: 'another dimension'});
        strictEqual(4, tracker.getNumTrackedPageViews(), 'getNumTrackedPageViews, should increase num pageview counter');

        var idPageviewCustomTitle = tracker.getConfigIdPageView();
        ok(idPageviewCustomTitle != idPageview, 'trackPageview, should generate a new random pageview id whenever it is called');
        ok(/([0-9a-zA-Z]){6}/.test(idPageviewCustomTitle), 'trackPageview, new generated random pageview id should be 16 char a-Z0-9 as well');

        var customUrlShouldNotChangeCampaign = "http://localhost.localdomain/?utm_campaign=NONONONONONONO&utm_term=PLEASE NO!";
        tracker.setCustomUrl(customUrl);

        tracker.trackPageView();

        var trackLinkCallbackFired = false;
        var trackLinkCallback = function (event) {
            ok(event.success, 'successful event callback');
            ok(event.request.indexOf('link=http') === 0, 'contains request')

            trackLinkCallbackFired = true;
        };
        tracker.trackLink("http://example.ca", "link", { "token" : getToken() }, trackLinkCallback);

        // async tracker proxy
        _paq.push(["trackLink", "http://example.fr/async.zip", "download",  { "token" : getToken() }]);

        // push function
        _paq.push([ function(t) {
            tracker.trackLink("http://example.de", "link", { "token" : t });
        }, getToken() ]);

        tracker.setRequestMethod("POST");
        tracker.trackGoal(42, 69, { "token" : getToken(), "boy" : "Michael", "girl" : "Mandy"});

        piwik_log("CompatibilityLayer", 1, "matomo.php", { "token" : getToken() });

        tracker.hook.test._addEventListener(_e("click8"), "click", stopEvent);
        triggerEvent(_e("click8"), 'click');

        tracker.enableLinkTracking(true);

        tracker.setRequestMethod("GET");
        var buttons = new Array("click1", "click2", "click3", "click4", "click5", "click6", "click7", "click11");
        for (var i=0; i < buttons.length; i++) {
            tracker.hook.test._addEventListener(_e(buttons[i]), "click", stopEvent);
            triggerEvent(_e(buttons[i]), 'click');
        }

        triggerEvent(_e('click7'), 'contextmenu');

        triggerEvent(_e('click7'), 'mousedown', 1);
        triggerEvent(_e('click7'), 'mouseup', 1); // middleclick

        var xhr = makeXhr();

        var clickDiv = _e("clickDiv"),
            anchor = document.createElement("a");

        anchor.id = "click10";
        anchor.href = "http://example.us";
        clickDiv.innerHTML = "";
        clickDiv.appendChild(anchor);
        tracker.addListener(anchor);
        tracker.hook.test._addEventListener(anchor, "click", stopEvent);
        triggerEvent(_e('click10'), 'click');

        var visitorId1, visitorId2;

        _paq.push([ function() {
            visitorId1 = Piwik.getAsyncTracker().getVisitorId();
        }]);
        visitorId2 = tracker.getVisitorId();
        ok( visitorId1 && visitorId1 != "" && visitorId2 && visitorId2 != "" && (visitorId1 == visitorId2), "getVisitorId()" + visitorId1 + " VS " + visitorId2 );

        var visitorInfo1, visitorInfo2;

        // Visitor INFO + Attribution INFO tests
        tracker.setReferrerUrl(referrerUrl);
        _paq.push([ function() {
            visitorInfo1 = Piwik.getAsyncTracker().getVisitorInfo();
            attributionInfo1 = Piwik.getAsyncTracker().getAttributionInfo();
            referrer1 = Piwik.getAsyncTracker().getAttributionReferrerUrl();
        }]);
        visitorInfo2 = tracker.getVisitorInfo();
        ok( visitorInfo1 && visitorInfo2 && visitorInfo1.length == visitorInfo2.length, "getVisitorInfo() " );
        for (var i = 0; i < 6; i++) {
            ok( visitorInfo1[i] == visitorInfo2[i], "(loadVisitorId())["+i+"]" );
        }

        attributionInfo2 = tracker.getAttributionInfo();
        ok( attributionInfo1 && attributionInfo2 && attributionInfo1.length == attributionInfo2.length, "getAttributionInfo()" );
        referrer2 = tracker.getAttributionReferrerUrl();
        equal( referrer2, referrerUrl, "getAttributionReferrerUrl()" );
        equal( referrer1, referrerUrl, "async getAttributionReferrerUrl()" );
        referrerTimestamp2 = tracker.getAttributionReferrerTimestamp();
        equal( referrerTimestamp2, referrerTimestamp, "tracker.getAttributionReferrerTimestamp()" );
        campaignName2 = tracker.getAttributionCampaignName();
        campaignKeyword2 = tracker.getAttributionCampaignKeyword();
        equal( campaignName2, "YEAH", "getAttributionCampaignName()");
        equal( campaignKeyword2, "RIGHT!", "getAttributionCampaignKeyword()");

        // Test visitor ID at the start is the same at the end
        var visitorIdEnd = tracker.getVisitorId();
        ok( visitorIdStart == visitorIdEnd, "tracker.getVisitorId() same at the start and end of process");

        // Tracker custom request
        var requestQueue = tracker.getRequestQueue();
        strictEqual(true, requestQueue.enabled);
        strictEqual(0, requestQueue.requests.length, "has not any request queued yet");

        tracker.trackRequest('myFoo=bar&baz=1');
        tracker.queueRequest('myQueue=bar&queue=1');
        tracker.queueRequest('myQueue=bar&queue=2');
        tracker.queueRequest('myQueue=bar&queue=3');

        requestQueue = tracker.getRequestQueue();
        equal(3, requestQueue.requests.length, "has added only the queued requests to the queue");

        tracker.disableQueueRequest();
        strictEqual(false, requestQueue.enabled);

        tracker.queueRequest('myQueueDisabled=bar&queue=4');
        requestQueue = tracker.getRequestQueue();
        equal(3, requestQueue.requests.length, "does not increase number of queued requests but send it directly");
        requestQueue.enabled = true;

        var fullQueueRequest = tracker.getRequest('myQueue=bar&queue=5');
        tracker.trackPageView('is full request');
        tracker.queueRequest(fullQueueRequest, true);

        // Custom variables
        tracker.storeCustomVariablesInCookie();
        tracker.setCookieNamePrefix("PREFIX");
        tracker.setCustomVariable(1, "cookiename", "cookievalue");
        deepEqual( tracker.getCustomVariable(1), ["cookiename", "cookievalue"], "setCustomVariable(cvarExists), getCustomVariable()" );
        tracker.setCustomVariable(2, "cookiename2", "cookievalue2", "visit");
        deepEqual( tracker.getCustomVariable(2), ["cookiename2", "cookievalue2"], "setCustomVariable(cvarExists), getCustomVariable()" );
        deepEqual( tracker.getCustomVariable(2, "visit"), ["cookiename2", "cookievalue2"], "setCustomVariable(cvarExists), getCustomVariable()" );
        deepEqual( tracker.getCustomVariable(2, 2), ["cookiename2", "cookievalue2"], "GA compability - setCustomVariable(cvarExists), getCustomVariable()" );
        tracker.setCustomVariable(2, "cookiename2PAGE", "cookievalue2PAGE", "page");
        deepEqual( tracker.getCustomVariable(2, "page"), ["cookiename2PAGE", "cookievalue2PAGE"], "setCustomVariable(cvarExists), getCustomVariable()" );
        deepEqual( tracker.getCustomVariable(2, 3), ["cookiename2PAGE", "cookievalue2PAGE"], "GA compability - setCustomVariable(cvarExists), getCustomVariable()" );
        tracker.setCustomVariable(2, "cookiename2EVENT", "cookievalue2EVENT", "event");
        deepEqual( tracker.getCustomVariable(2, "event"), ["cookiename2EVENT", "cookievalue2EVENT"], "CustomVariable and event scope" );

        tracker.trackPageView("SaveCustomVariableCookie");

        // test Site Search
        tracker.trackSiteSearch("No result keyword éà", "Search cat", 0);
        tracker.trackSiteSearch("Keyword with 10 results", false, 10);
        tracker.trackSiteSearch("search Keyword");

        // Testing Custom events
        tracker.setCustomVariable(1, "cvarEventName", "cvarEventValue", "event");
        tracker.trackEvent("Event Category", "Event Action");
        tracker.trackEvent("Event Category2", "Event Action2", "Event Name2");
        tracker.trackEvent("Event Category3", "Event Action3", "Event Name3", 3.333);

        //Ecommerce views
        tracker.setEcommerceView( "SKUMultiple", false, ["CATEGORY1","CATEGORY2"] );
        tracker.trackPageView("MultipleCategories");

        var tracker2 = Piwik.getTracker();
        tracker2.setTrackerUrl("matomo.php");
        tracker2.setSiteId(1);
        tracker2.storeCustomVariablesInCookie();
        tracker2.setCustomData({ "token" : getToken() });
        tracker2.setCookieNamePrefix("PREFIX");
        deepEqual( tracker2.getCustomVariable(1), ["cookiename", "cookievalue"], "getCustomVariable(cvarExists) from cookie" );
        ok( /PREFIX/.test( document.cookie ), "setCookieNamePrefix()" );

        tracker2.deleteCustomVariable(1);
        //console.log(tracker2.getCustomVariable(1));
        ok( tracker2.getCustomVariable(1) === false, "VISIT deleteCustomVariable(), getCustomVariable() === false" );
        tracker2.deleteCustomVariable(2, "page");
        //console.log(tracker2.getCustomVariable(2, "page"));
        ok( tracker2.getCustomVariable(2, "page") === false, "PAGE deleteCustomVariable(), getCustomVariable() === false" );
        tracker2.trackPageView("DeleteCustomVariableCookie");

        var tracker3 = Piwik.getTracker();
        tracker3.setTrackerUrl("matomo.php");
        tracker3.setSiteId(1);
        tracker3.setCustomData({ "token" : getToken() });
        tracker3.setCookieNamePrefix("PREFIX");
        ok( tracker3.getCustomVariable(1) === false, "getCustomVariable(cvarDeleted) from cookie  === false" );

        // Ecommerce Views
        tracker3.setEcommerceView( "SKU", "NAME HERE", "CATEGORY HERE" );
        tracker3.trackPageView("EcommerceView");

        tracker3.deleteCustomVariables('page');

        //Ecommerce tests
        tracker3.addEcommerceItem("SKU PRODUCT", "PRODUCT NAME", "PRODUCT CATEGORY", 11.1111, 2);
        tracker3.addEcommerceItem("SKU TO REMOVE");
        tracker3.addEcommerceItem("SKU PRODUCT", "random", "random PRODUCT CATEGORY", 11.1111, 2);
        tracker3.addEcommerceItem("SKU ONLY SKU", "", "", "", "");
        tracker3.addEcommerceItem("SKU ONLY NAME", "PRODUCT NAME 2", "", "");
        tracker3.addEcommerceItem("SKU NO PRICE NO QUANTITY", "PRODUCT NAME 3", "CATEGORY", "", "" );
        tracker3.addEcommerceItem("SKU ONLY" );
        tracker3.removeEcommerceItem("SKU TO REMOVE");

        var cart = tracker3.getEcommerceItems();
        deepEqual(cart, {
            "SKU NO PRICE NO QUANTITY": [
                "SKU NO PRICE NO QUANTITY",
                "PRODUCT NAME 3",
                "CATEGORY",
                "",
                ""
            ],
            "SKU ONLY": [
                "SKU ONLY",
                null,
                null,
                null,
                null
            ],
            "SKU ONLY NAME": [
                "SKU ONLY NAME",
                "PRODUCT NAME 2",
                "",
                "",
                null
            ],
            "SKU ONLY SKU": [
                "SKU ONLY SKU",
                "",
                "",
                "",
                ""
            ],
            "SKU PRODUCT": [
                "SKU PRODUCT",
                "random",
                "random PRODUCT CATEGORY",
                11.1111,
                2
            ]
        });

        // test that changing the cart object does not change the internal ecommerceItems var
        cart["SKU PRODUCT"][3] = 5;

        tracker3.trackEcommerceCartUpdate( 555.55 );

        tracker3.trackEcommerceOrder( "ORDER ID YES", 666.66, 333, 222, 111, 1 );

        tracker3.addEcommerceItem("SKU TO REMOVE 1");
        tracker3.addEcommerceItem("SKU TO REMOVE 2");
        tracker3.addEcommerceItem("SKU TO REMOVE 3");
        tracker3.clearEcommerceCart();

        tracker3.addEcommerceItem(12345, 544, 34343, 34, 1);
        cart = tracker3.getEcommerceItems();
        deepEqual(cart, {
            '12345': [
                '12345',
                544,
                34343,
                34,
                1
            ]
        });
        tracker3.removeEcommerceItem(12345);
        cart = tracker3.getEcommerceItems();
        deepEqual(cart, {}, 'removed numeric item');

        tracker3.clearEcommerceCart();

        // the same order tracked once more, should have no items
        tracker3.trackEcommerceOrder( "ORDER WITHOUT ANY ITEM", 777, 444, 222, 111, 1 );

        // do not track
        tracker3.setDoNotTrack(false);

        // User ID
        var userIdString = 'userid@mydomain.org';
        tracker3.setUserId(userIdString);

        // Append tracking url parameter
        tracker3.appendToTrackingUrl("appended=1&appended2=value");

        // Track pageview
        tracker3.trackPageView("DoTrack");

        var userIdNum = 12345;
        tracker3.setUserId(userIdNum);
        tracker3.trackPageView('AnotherTrack');

        // Firefox 9: navigator.doNotTrack is read-only
        navigator.doNotTrack = "yes";
        if (navigator.doNotTrack === "yes")
        {
            tracker3.setDoNotTrack(true);
            tracker3.trackPageView("DoNotTrack");
        }

        // Testing JavaScriptErrorTracking START
        var oldOnError = window.onerror;

        var customOnErrorInvoked = false;
        window.onerror = function (message, url, line, column, error) {
            customOnErrorInvoked = true;

            equal(message, 'Uncaught Error: The message', 'message forwarded to custom onerror handler');
            equal(url, 'http://piwik.org/path/to/file.js?cb=34343', 'url forwarded to custom onerror handler');
            equal(line, 44, 'line forwarded to custom onerror handler');
            equal(column, 12, 'column forwarded to custom onerror handler');
            ok(error instanceof Error, 'error forwarded to custom onerror handler');
        };

        tracker.enableJSErrorTracking();
        window.onerror('Uncaught Error: The message', 'http://piwik.org/path/to/file.js?cb=34343', 44, 12, new Error('The message'));
        ok(customOnErrorInvoked, "Custom onerror handler was called as expected");

        equal(tracker.getJavascriptErrors().length, 1, "1 error was added to array");
        window.onerror('Uncaught Error: The message', 'http://piwik.org/path/to/file.js?cb=34343', 44, 12, new Error('The message'));
        equal(tracker.getJavascriptErrors().length, 1, "Same error ignored second time");

        //  check setPagePerformanceTiming function
        tracker.setPagePerformanceTiming(35, 103);
        tracker.trackPageView('ShouldHave_pf_35_103');
        tracker.trackPageView('ShouldNotHave_pf_35_103');
        tracker.setPagePerformanceTiming(12345);
        tracker.trackPageView('ShouldHave_pf_12345');
        tracker.trackPageView('ShouldNotHave_pf_12345');
        tracker.setPagePerformanceTiming(432, 578, 111);
        tracker.trackPageView('ShouldHave_pf_432_578_111');
        tracker.trackPageView('ShouldNotHave_pf_432_578_111');
        tracker.setPagePerformanceTiming();
        tracker.trackPageView('ShouldHave_pf_NO');
        tracker.trackPageView('ShouldNotHave_pf_NO');
        tracker.setPagePerformanceTiming(1, 2, 3, 4, 5, 6, 7, 8);
        tracker.trackPageView('ShouldHave_pf_1_2_3_4_5_6_7_8');
        tracker.trackPageView('ShouldNotHave_pf_1_2_3_4_5_6_7_8');
        //  /check setPagePerformanceTiming function


        // delete existing onerror handler and setup tracking again
        window.onerror = customOnErrorInvoked = false;
        tracker2.enableJSErrorTracking();

        window.onerror('Second Error: With less data', 'http://piwik.org/path/to/file.js?cb=3kfkf', 45);
        ok(!customOnErrorInvoked, "Custom onerror handler was ignored as expected");
        equal(tracker2.getJavascriptErrors().length, 1, "Other tracker has 1 error");

        window.onerror('Third Error: With less data', 'http://piwik.org/path/to/file.js?cb=3kfkf', 45);
        equal(tracker2.getJavascriptErrors().length, 2, "Different error not ignored");

        window.onerror = oldOnError;
        // Testing JavaScriptErrorTracking END

        // Tracking file protocol
        tracker.setCustomUrl('file://Downloads/File.pdf');
        tracker.trackPageView('FileProtocolShouldNotBeTracked');

        tracker.enableFileTracking();
        tracker.setCustomUrl('file://Downloads/AnotherFile.pdf');
        tracker.trackPageView('FileProtocolShouldBeTrackedWhenEnabled');

        // add tracker
        _paq.push(["addTracker", null, 13]);
        var createdNewTracker = Piwik.getAsyncTracker(null, 13);
        equal(13, createdNewTracker.getSiteId(), "addTracker() was actually added");

        createdNewTracker.setCustomData({ "token" : getToken() });
        _paq.push(['trackPageView', 'twoTrackers']);

        stop();
        setTimeout(function() {
            tracker.removeAllAsyncTrackersButFirst();
            xhr.open("GET", "matomo.php?requests=" + getToken(), false);
            xhr.send(null);
            results = xhr.responseText;
            var countTrackingEvents = /<span\>([0-9]+)\<\/span\>/.exec(results);
            ok (countTrackingEvents, "countTrackingEvents is set");
            if(countTrackingEvents) {
                equal( countTrackingEvents[1], "59", "count tracking events" );
            }

            // firing callback
            ok( trackLinkCallbackFired, "trackLink() callback fired" );

            // tracking requests
            ok( /PiwikTest/.test( results ), "trackPageView(), setDocumentTitle()" );
            ok( results.indexOf("tests/javascript/matomo.php?action_name=Asynchronous%20Tracker%20ONE&idsite=1&rec=1") >= 0 , "async trackPageView() called before setTrackerUrl() should work" );
            ok( /Asynchronous%20tracking%20TWO/.test( results ), "async trackPageView() called after another trackPageView()" );
            ok( /CustomTitleTest/.test( results ), "trackPageView(customTitle)" );
            ok( results.indexOf('&pv_id=' + idPageview) !== -1, "trackPageView, configPageId should be sent along requests" );
            ok( results.indexOf('&pv_id=' + idPageviewCustomTitle) !== -1, "trackPageView, idPageviewCustomTitle should be sent along requests when a new is generated" );
            ok( ! /click.example.com/.test( results ), "click: ignore href=javascript" );
            ok( /example.ca/.test( results ), "trackLink()" );
            ok( /example.fr/.test( results ), "async trackLink()" );
            ok( /example.de/.test( results ), "push function" );
            ok( /example.us/.test( results ), "addListener()" );

            ok( /example.net/.test( results ), "setRequestMethod(GET), click: implicit outlink (by outbound URL)" );
            ok( /example.co.nz/.test( results ), "setRequestMethod(GET), click: outlink with iso-8859-1 encoding" );
            ok( /example.html/.test( results ), "click: explicit outlink" );
            ok( /example.pdf/.test( results ), "click: implicit download (by file extension)" );
            ok( /example.word/.test( results ), "click: explicit download" );

            ok( ! /example.exe/.test( results ), "enableLinkTracking()" );
            ok( ! /example.php/.test( results ), "click: ignored example.php" );
            ok( ! /example.org/.test( results ), "click: ignored example.org" );
            ok( /idgoal=42.*?revenue=69.*?Michael.*?Mandy/.test( results ), "setRequestMethod(POST), trackGoal()" );
            ok( /CompatibilityLayer/.test( results ), "piwik_log(): compatibility layer" );
            ok( /localhost.localdomain/.test( results ), "setCustomUrl()" );
            ok( /referrer.example.com/.test( results ), "setReferrerUrl()" );
            ok( ! /ignored.referrer.url/.test( results ), "ignored referrer url isn't sent with request" );
            ok( ! /another.referrer.url/.test( results ), "referrer ignored with url parameter isn't sent with request" );
            ok( /cookiename/.test( results ) && /cookievalue/.test( results ), "tracking request contains custom variable" );
            ok( /DeleteCustomVariableCookie/.test( results ), "tracking request deleting custom variable" );
            ok( /DoTrack/.test( results ), "setDoNotTrack(false)" );
            ok( ! /DoNotTrack/.test( results ), "setDoNotTrack(true)" );

            // custom tracking request
            ok( /myFoo=bar&baz=1&idsite=1/.test( results ), "trackRequest sends custom parameters");
            ok( /myQueue=bar&queue=1/.test( results ), "queueRequest sends queued requests");
            ok( /myQueue=bar&queue=2/.test( results ), "queueRequest sends queued requests");
            ok( /myQueue=bar&queue=3/.test( results ), "queueRequest sends queued requests");
            ok( /myQueueDisabled=bar&queue=4/.test( results ), "queueRequest sends queued requests when disabled directly");

            ok( results.indexOf(fullQueueRequest + '&uadata=%7B%7D</span>') !== -1, "queueRequest does not duplicate params if isFullRequest is used queued");

            // Test Custom variables
            ok( /SaveCustomVariableCookie.*&cvar=%7B%222%22%3A%5B%22cookiename2PAGE%22%2C%22cookievalue2PAGE%22%5D%7D.*&_cvar=%7B%221%22%3A%5B%22cookiename%22%2C%22cookievalue%22%5D%2C%222%22%3A%5B%22cookiename2%22%2C%22cookievalue2%22%5D%7D/.test(results), "test custom vars are set");

            // Test CustomDimension (persistent ones across requests)
            ok( /dimension1=my%20custom%20value%20with%20%C3%A9%C3%A0%C3%A7%26\*\(\)%20EOL&dimension2=&/.test(results), "test custom dimensions are set");

            // send along a page view and ony valid for this pageview (dimension 2 overwrites another one)
            ok( /dimension2=my%20new%20value&dimension5=another%20dimension&dimension1=my%.*&data=%7B%22token/.test( results ), "trackPageView(customTitle, customData)" );

            // Test campaign parameters set
            ok( /&_rcn=YEAH&_rck=RIGHT!/.test( results), "Test campaign parameters found");
            ok( /&_ref=http%3A%2F%2Freferrer.example.com%2Fpage%2Fsub%3Fquery%3Dtest%26test2%3Dtest3/.test( results), "Test cookie Ref URL found ");

            // Test site search
            ok( /search=No%20result%20keyword%20%C3%A9%C3%A0&search_cat=Search%20cat&search_count=0&idsite=1/.test(results), "site search, cat, 0 result ");
            ok( /search=Keyword%20with%2010%20results&search_count=10&idsite=1/.test(results), "site search, no cat, 10 results ");
            ok( /search=search%20Keyword&idsite=1/.test(results), "site search, no cat, no results count ");

            // Test events
            ok( /(e_c=Event%20Category&e_a=Event%20Action&ca=1&idsite=1).*(&e_cvar=%7B%221%22%3A%5B%22cvarEventName%22%2C%22cvarEventValue%22%5D%2C%222%22%3A%5B%22cookiename2EVENT%22%2C%22cookievalue2EVENT%22%5D%7D)/.test(results), "event Category + Action + Custom Variable");
            ok( /e_c=Event%20Category2&e_a=Event%20Action2&e_n=Event%20Name2&ca=1&idsite=1/.test(results), "event Category + Action + Name");
            ok( /e_c=Event%20Category3&e_a=Event%20Action3&e_n=Event%20Name3&e_v=3.333&ca=1&idsite=1/.test(results), "event Category + Action + Name + Value");

            // ecommerce view
            ok( /(EcommerceView).*(&_pkc=CATEGORY%20HERE&_pks=SKU&_pkn=NAME)/.test(results), "ecommerce view");

            // ecommerce view multiple categories
            ok( /(MultipleCategories).*(&_pkc=%5B%22CATEGORY1%22%2C%22CATEGORY2%22%5D&_pks=SKUMultiple&_pkn=)/.test(results), "ecommerce view multiple categories");

            // Ecommerce order
            ok( /idgoal=0&ec_id=ORDER%20ID%20YES&revenue=666.66&ec_st=333&ec_tx=222&ec_sh=111&ec_dt=1&ec_items=%5B%5B%22SKU%20PRODUCT%22%2C%22random%22%2C%22random%20PRODUCT%20CATEGORY%22%2C11.1111%2C2%5D%2C%5B%22SKU%20ONLY%20SKU%22%2C%22%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%20NAME%22%2C%22PRODUCT%20NAME%202%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20NO%20PRICE%20NO%20QUANTITY%22%2C%22PRODUCT%20NAME%203%22%2C%22CATEGORY%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%22%2C%22%22%2C%22%22%2C0%2C1%5D%5D/.test( results ), "logEcommerceOrder() with items" );

            // Cart update
            ok( /idgoal=0&revenue=555.55&ec_items=%5B%5B%22SKU%20PRODUCT%22%2C%22random%22%2C%22random%20PRODUCT%20CATEGORY%22%2C11.1111%2C2%5D%2C%5B%22SKU%20ONLY%20SKU%22%2C%22%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%20NAME%22%2C%22PRODUCT%20NAME%202%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20NO%20PRICE%20NO%20QUANTITY%22%2C%22PRODUCT%20NAME%203%22%2C%22CATEGORY%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%22%2C%22%22%2C%22%22%2C0%2C1%5D%5D/.test( results ), "logEcommerceCartUpdate() with items" );

            // Ecommerce order recorded twice, but each order empties the cart/list of items, so this order is empty of items
            ok( /idgoal=0&ec_id=ORDER%20WITHOUT%20ANY%20ITEM&revenue=777&ec_st=444&ec_tx=222&ec_sh=111&ec_dt=1&ec_items=%5B%5D/.test( results ), "logEcommerceOrder() called twice, second time has no item" );

            // parameters inserted by plugin hooks
            ok( /testlog/.test( results ), "plugin hook log" );
            ok( /testlink/.test( results ), "plugin hook link" );
            ok( /testgoal/.test( results ), "plugin hook goal" );

            // Testing the Tracking URL append
            ok( /&appended=1&appended2=value/.test( results ), "appendToTrackingUrl(query) function");

            // Testing the User ID setter
            ok( /&uid=userid%40mydomain.org/.test( results ), "setUserId(userId) function");

            // Testing the JavaScript Error Tracking
            ok( /e_c=JavaScript%20Errors&e_a=http%3A%2F%2Fpiwik.org%2Fpath%2Fto%2Ffile.js%3Fcb%3D34343%3A44%3A12&e_n=Uncaught%20Error%3A%20The%20message&ca=1&idsite=1/.test( results ), "enableJSErrorTracking() function with predefined onerror event");
            ok( /e_c=JavaScript%20Errors&e_a=http%3A%2F%2Fpiwik.org%2Fpath%2Fto%2Ffile.js%3Fcb%3D3kfkf%3A45&e_n=Second%20Error%3A%20With%20less%20data&ca=1&idsite=1/.test( results ), "enableJSErrorTracking() function without predefined onerror event and less parameters");

            ok( /matomo.php\?action_name=twoTrackers&idsite=1&/.test( results ), "addTracker() trackPageView() sends request to both Piwik instances");
            ok( /matomo.php\?action_name=twoTrackers&idsite=13&/.test( results ), "addTracker() trackPageView() sends request to both Piwik instances");

            //  check setPagePerformanceTiming function
            // pf_net: networkTimeInMs,
            // pf_srv: serverTimeInMs,
            // pf_tfr: transferTimeInMs,
            // pf_dm1: domProcessingTimeInMs,
            // pf_dm2: domCompletionTimeInMs,
            // pf_onl: onloadTimeInMs
            ok( /ShouldHave_pf_35_103.*pf_net=35&pf_srv=103/.test(results), 'setPagePerformanceTiming sets "pf_net=35&pf_srv=103" in request');
            ok( ! /ShouldNotHave_pf_35_103.*pf_net=35&pf_srv=103/.test(results), 'setPagePerformanceTiming does NOT set "pf_net=35&pf_srv=103" in subsequent request');
            ok( /ShouldHave_pf_12345.*pf_net=12345/.test(results), 'setPagePerformanceTiming sets "pf_net=12345" in request');
            ok( ! /ShouldNotHave_pf_12345.*pf_net=12345/.test(results), 'setPagePerformanceTiming does NOT set "pf_net=12345" in subsequent request');
            ok( /ShouldHave_pf_432_578_111.*pf_net=432&pf_srv=578&pf_tfr=111/.test(results), 'setPagePerformanceTiming sets "pf_net=432&pf_srv=578&pf_tfr=111" in request');
            ok( ! /ShouldNotHave_pf_432_578_111.*pf_net=432&pf_srv=578&pf_tfr=111/.test(results), 'setPagePerformanceTiming does NOT set "pf_net=432&pf_srv=578&pf_tfr=111" in subsequent request');
            ok( ! /ShouldHave_pf_NO.*pf_net=/.test(results), 'setPagePerformanceTiming sets nothing in request, but default does');
            ok( ! /ShouldNotHave_pf_NO.*pf_net=/.test(results), 'setPagePerformanceTiming sets nothing in subsequent request');
            ok( /ShouldHave_pf_1_2_3_4_5_6_7_8.*pf_net=1&pf_srv=2&pf_tfr=3&pf_dm1=4&pf_dm2=5&pf_onl=6/.test(results), 'setPagePerformanceTiming only sets 6 parameters in request');
            ok( ! /ShouldNotHave_pf_1_2_3_4_5_6_7_8.*pf_net=1&pf_srv=2&pf_tfr=3&pf_dm1=4&pf_dm2=5&pf_onl=6/.test(results), 'setPagePerformanceTiming only sets 6 parameters in request');
            //  /check setPagePerformanceTiming function

            ok( ! /action_name=FileProtocolShouldNotBeTracked/.test(results), 'file protocol should not be tracked by default');
            ok( /action_name=FileProtocolShouldBeTrackedWhenEnabled/.test(results), 'file protocol should be tracked when enabled');
            start();
        }, 5000);
    });

    // heartbeat tests
    test("trackingHeartBeat", function () {
        expect(13);

        var tokenBase = getHeartbeatToken();

        var tracker = Piwik.getTracker();
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        tracker.enableHeartBeatTimer(3);

        stop();
        Q.delay(1).then(function () {
            // test ping heart beat not set up until an initial request tracked
            tracker.setCustomData('token', 1 + tokenBase);

            return Q.delay(3500);
        }).then(function () {
            // test ping not sent on initial page load, and sent if inactive for N secs.
            tracker.setCustomData('token', 2 + tokenBase);
            tracker.trackPageView('whatever'); // normal request sent here
        }).then(function () {
            triggerEvent(window, 'focus');

            return Q.delay(4000); // ping request not sent after this
        }).then(function () {
            // test ping not sent after N secs, if tracking request sent in the mean time
            tracker.setCustomData('token', 3 + tokenBase);

            tracker.trackPageView('whatever2'); // normal request sent here
            // heart beat will trigger in 2 secs, then reset to 1 sec later, since tracker request
            // was sent 2 secs ago
        }).then(function () {
            return Q.delay(2100); // ping request NOT sent here
        }).then(function () {
            // test ping not sent N secs after second tracking request if inactive.
            tracker.setCustomData('token', 4 + tokenBase);

            return Q.delay(4100); // ping request not sent here
        }).then(function () {
            // test ping sent once after window blur event triggered (ie tab switch).
            tracker.setCustomData('token', 5 + tokenBase);

            triggerEvent(window, 'blur');

            return Q.delay(4000); // ping request sent here because of blur
        }).then(function () {
            // test ping not sent on focus
            tracker.setCustomData('token', 6 + tokenBase);

            triggerEvent(window, 'focus'); // no ping request sent here

            tracker.disableHeartBeatTimer(); // flatline

            return Q.delay(1000); // for a ping request to get sent if there was one
        }).then(function () {
            // test ping not sent on focus
            tracker.enableHeartBeatTimer();
            tracker.setCustomData('token', 7 + tokenBase);

            return Q.delay(6000); // should not send a tracking request because of visit standard length reached
        }).then(function () {
            var token;

            var requests = fetchTrackedRequests(token = 1 + tokenBase, true);
            equal(requests.length, 0, "[token = 1] no requests sent before initial non-ping request sent");

            requests = fetchTrackedRequests(token = 2 + tokenBase, true);
            ok(/action_name=whatever/.test(requests[0]) && !(/ping=1/.test(requests[0])), "[token = 2] first request is page view not ping");
            equal(requests.length, 1, "[token = 2] only 1 requests sent for normal ping");

            requests = fetchTrackedRequests(token = 3 + tokenBase, true);
            ok(/action_name=whatever2/.test(requests[0]) && !(/ping=1/.test(requests[0])), "[token = 3] first request is page view not ping");
            equal(requests.length, 1, "[token = 3] no ping request sent if other request sent in meantime");

            requests = fetchTrackedRequests(token = 4 + tokenBase, true);
            equal(requests.length, 0, "[token = 4] no ping request sent if no other activity");

            requests = fetchTrackedRequests(token = 5 + tokenBase, true);
            ok(/ping=1/.test(requests[0]), "[token = 5] ping request sent on blur");
            equal(requests.length, 1, "[token = 5] one request is sent if window is blurred");

            requests = fetchTrackedRequests(token = 6 + tokenBase, true);
            equal(requests.length, 0, "[token = 6] no ping request is sent after window regains focus");

            requests = fetchTrackedRequests(token = 7 + tokenBase, true);
            equal(requests.length, 0, "[token = 7] no ping request because of visit standard length");

            start();
        });
    });

    test("trackingContent", function() {
        expect(83);

        function assertTrackingRequest(actual, expectedStartsWith, message)
        {
            if (!message) {
                message = '';
            } else {
                message += ', ';
            }

            expectedStartsWith = '<span>' + toAbsolutePath('matomo.php') + '?' + expectedStartsWith;

            strictEqual(actual.indexOf(expectedStartsWith), 0, message +  actual + ' should start with ' + expectedStartsWith);
            strictEqual(actual.indexOf('&idsite=1&rec=1'), expectedStartsWith.length);
        }

        function resetTracker(track, token, replace)
        {
            tracker.getRequestQueue().sendRequests(); // make sure to send any remaining queued requests
            tracker.clearTrackedContentImpressions();
            tracker.clearEnableTrackOnlyVisibleContent();
            tracker.setCustomData('token', token);
            scrollToTop();
        }

        var token = getContentToken();

        var tracker = Piwik.getTracker();
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        resetTracker(tracker, token);

        var visitorIdStart = tracker.getVisitorId();
        // need to wait at least 1 second so that the cookie would be different, if it wasnt persisted
        wait(2000);

        var origin = getOrigin();
        var originEncoded = window.encodeURIComponent(origin);
        var actual, expected, trackerUrl;

        var contentBlocks = [
            null,
            {
                "name": toAbsolutePath("img1-en.jpg"),
                "piece": toAbsoluteUrl("img1-en.jpg"),
                "target": ""
            },
            {
                "name": "img.jpg",
                "piece": "img.jpg",
                "target": "http://img2.example.com"
            },
            {
                "name": toAbsolutePath("img3-en.jpg"),
                "piece": toAbsoluteUrl("img3-en.jpg"),
                "target": "http://img3.example.com"
            },
            {
                "name": "My content 4",
                "piece": "My content 4",
                "target": "http://img4.example.com"
            },
            {
                "name": "My Ad 5",
                "piece": "http://img5.example.com/path/xyz.jpg",
                "target": origin + "/anylink5"
            },
            {
                "name": "http://www.example.com/path/xyz.jpg",
                "piece": "http://www.example.com/path/xyz.jpg",
                "target": "http://img6.example.com"
            },
            {
                "name": "My Ad 7",
                "piece": "Unknown",
                "target": "http://img7.example.com"
            }
        ];

        tracker.trackAllContentImpressions();
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'getTrackedContentImpressions, there is no content block to track');
        tracker.trackContentImpressionsWithinNode(_e('other'));
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'getTrackedContentImpressionsWithinNode, there is no content block to track');
        tracker.trackContentInteractionNode();
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'trackContentInteractionNode, no node given should not track anything');

        setupContentTrackingFixture('trackingContent', document.body);

        tracker.trackAllContentImpressions();
        strictEqual(tracker.getTrackedContentImpressions().length, 7, 'should mark 7 content blocks as tracked');

        wait(3000);

        var token2 = '2' + token;
        resetTracker(tracker, token2);
        tracker.trackContentImpressionsWithinNode(_s('#block1'));
        expected = [contentBlocks[4], contentBlocks[3], contentBlocks[2]];
        propEqual(tracker.getTrackedContentImpressions(), expected, 'should mark 3 content blocks as tracked');

        tracker.clearTrackedContentImpressions();
        tracker.trackContentImpressionsWithinNode(_e('click1'));
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'should not track anything as does not contain content block');

        wait(3000);

        var token3 = '3' + token;
        resetTracker(tracker, token3);
        tracker.trackContentImpression(); // should not track anything as name is required
        tracker.trackContentImpression('MyName'); // piece should default to Unknown
        wait(300);
        tracker.trackContentImpression('Any://Name', 'AnyPiece?', 'http://www.example.com');
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'manual impression call should not be marked as already tracked');

        wait(3000);

        var token4 = '4' + token;
        resetTracker(tracker, token4);
        tracker.trackContentInteraction(); // should not track anything as interaction and name is required
        tracker.trackContentInteraction('Clicki'); // should not track anything as interaction and name is required
        tracker.trackContentInteraction('Clicke', 'IntName'); // should use default for piece and ignore target as it is not set
        wait(300);
        tracker.trackContentInteraction('Clicki', 'IntN:/ame', 'IntPiece?', 'http://int.example.com');

        wait(3000);

        setupContentTrackingFixture('trackingContent', document.body);

        var token5 = '5' + token;
        resetTracker(tracker, token5);
        tracker.trackContentInteractionNode(_s('#ex5'), 'Clicki?iii');

        wait(3000);

        var token6 = '6' + token;
        resetTracker(tracker, token6);
        tracker.enableTrackOnlyVisibleContent(false, 0);
        tracker.trackAllContentImpressions();
        expected = [contentBlocks[7], contentBlocks[6], contentBlocks[5], contentBlocks[1], contentBlocks[4], contentBlocks[3], contentBlocks[2]];
        propEqual(tracker.getTrackedContentImpressions().length, 7, 'should still track all impressions even if visible enabled');

        wait(3000);

        var token7 = '7' + token;
        resetTracker(tracker, token7);
        tracker.enableTrackOnlyVisibleContent(false, 0);
        tracker.trackContentImpressionsWithinNode();
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'should not track anything, no node provided');
        tracker.trackContentImpressionsWithinNode(_s('#block1'));
        strictEqual(tracker.getTrackedContentImpressions().length, 0, 'should not track any block since all not visible');
        tracker.trackContentImpressionsWithinNode(_s('#block2'));
        expected = [contentBlocks[6], contentBlocks[5]];
        propEqual(tracker.getTrackedContentImpressions(), expected, 'should track the two visible ones');

        wait(3000);

        var token8 = '8' + token;
        resetTracker(tracker, token8);
        tracker.trackVisibleContentImpressions(false, 0, tracker);
        expected = [contentBlocks[6], contentBlocks[5], contentBlocks[1]];
        propEqual(tracker.getTrackedContentImpressions(), expected, 'should only track all visible impressions');

        wait(3000);

        // test detection of content via interval
        var token9  = '9' + token;
        var token10 = '10' + token;
        resetTracker(tracker, token9);
        tracker.trackVisibleContentImpressions(false, 500);
        expected = [contentBlocks[6], contentBlocks[5], contentBlocks[1]];
        propEqual(tracker.getTrackedContentImpressions(), expected, 'should only track all visible impressions, timeInterval');
        _s('#block1').style.display = 'block';
        scrollToTop();

        stop();
        setTimeout(function () {
            expected = [contentBlocks[6], contentBlocks[5], contentBlocks[1], contentBlocks[4], contentBlocks[3], contentBlocks[2]];
            propEqual(tracker.getTrackedContentImpressions(), expected, 'should now have tracked 6 impressions via time interval');
            tracker.clearEnableTrackOnlyVisibleContent(); // stop visible content time interval check

            // test detection of content via scroll
            setTimeout(function () {
                _s('#block1').style.display = 'none';
                resetTracker(tracker, token10);
                tracker.trackVisibleContentImpressions(true, 0);
                expected = [contentBlocks[6], contentBlocks[5], contentBlocks[1]];
                propEqual(tracker.getTrackedContentImpressions(), expected, 'should track 3 initial visible impressions, scroll');
                _s('#block1').style.display = 'block';
                window.scrollTo(0, 10); // should trigger scroll event
                setTimeout(function () {
                    strictEqual(tracker.getTrackedContentImpressions().length, 6, 'should detect 3 more afer scroll');
                    tracker.clearEnableTrackOnlyVisibleContent(); // stop visible content scroll interval check

                    start();
                }, 700);

            }, 400); // wait for time interval to stop.

        }, 1500);

        var trackingRequests = [
            null,
            'c_n=' + toEncodedAbsolutePath('img1-en.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img1-en.jpg') + '&ca=1',
            'c_n=img.jpg&c_p=img.jpg&c_t=http%3A%2F%2Fimg2.example.com&ca=1',
            'c_n=' + toEncodedAbsolutePath('img3-en.jpg') + '&c_p=' + toEncodedAbsoluteUrl('img3-en.jpg') + '&c_t=http%3A%2F%2Fimg3.example.com&ca=1',
            'c_n=My%20content%204&c_p=My%20content%204&c_t=http%3A%2F%2Fimg4.example.com&ca=1',
            'c_n=My%20Ad%205&c_p=http%3A%2F%2Fimg5.example.com%2Fpath%2Fxyz.jpg&c_t=' + originEncoded + '%2Fanylink5&ca=1',
            'c_n=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_p=http%3A%2F%2Fwww.example.com%2Fpath%2Fxyz.jpg&c_t=http%3A%2F%2Fimg6.example.com&ca=1',
            'c_n=My%20Ad%207&c_p=Unknown&c_t=http%3A%2F%2Fimg7.example.com&ca=1'
        ];

        stop();
        setTimeout(function() {
            removeContentTrackingFixture();

            // trackAllContentImpressions()
            var results = fetchTrackedRequests(token);

            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "7", "count trackAllContentImpressions requests. all content blocks should be tracked" );

            var requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], trackingRequests[7]);
            assertTrackingRequest(requests[1], trackingRequests[6]);
            assertTrackingRequest(requests[2], trackingRequests[5]);
            assertTrackingRequest(requests[3], trackingRequests[4]);
            assertTrackingRequest(requests[4], trackingRequests[3]);
            assertTrackingRequest(requests[5], trackingRequests[2]);
            assertTrackingRequest(requests[6], trackingRequests[1]);


            // trackContentImpressionsWithinNode()
            results = fetchTrackedRequests(token2);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "3", "count trackContentImpressionsWithinNode requests. should track only content blocks within node" );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], trackingRequests[4]);
            assertTrackingRequest(requests[1], trackingRequests[3]);
            assertTrackingRequest(requests[2], trackingRequests[2]);

            // trackContentImpression()
            results = fetchTrackedRequests(token3);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "2", "count trackContentImpression requests. " );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            var firstRequest  = 0;
            var secondRequest = 1;
            if (-1 === requests[0].indexOf('MyName')) {
                firstRequest  = 1;
                secondRequest = 0;
            }

            assertTrackingRequest(requests[firstRequest], 'c_n=MyName&c_p=Unknown&ca=1');
            assertTrackingRequest(requests[secondRequest], 'c_n=Any%3A%2F%2FName&c_p=AnyPiece%3F&c_t=http%3A%2F%2Fwww.example.com&ca=1');


            // trackContentInteraction()
            results = fetchTrackedRequests(token4);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "2", "count trackContentInteraction requests." );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            firstRequest  = 0;
            secondRequest = 1;
            if (-1 === requests[0].indexOf('IntName')) {
                firstRequest  = 1;
                secondRequest = 0;
            }

            assertTrackingRequest(requests[firstRequest], 'c_i=Clicke&c_n=IntName&c_p=Unknown&ca=1');
            assertTrackingRequest(requests[secondRequest], 'c_i=Clicki&c_n=IntN%3A%2Fame&c_p=IntPiece%3F&c_t=http%3A%2F%2Fint.example.com&ca=1');


            // trackContentInteractionNode()
            results = fetchTrackedRequests(token5);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "1", "count trackContentInteractionNode requests." );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], 'c_i=Clicki%3Fiii&c_n=My%20Ad%205&c_p=http%3A%2F%2Fimg5.example.com%2Fpath%2Fxyz.jpg&c_t=' + originEncoded + '%2Fanylink5&ca=1');


            // enableTrackOnlyVisibleContent() && trackAllContentImpressions()
            results = fetchTrackedRequests(token6);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "7", "count enabledVisibleContentImpressions requests." );


            // enableTrackOnlyVisibleContent() && trackContentImpressionsWithinNode()
            results = fetchTrackedRequests(token7);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "2", "count enabledVisibleContentImpressionsWithinNode requests." );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], trackingRequests[6]);
            assertTrackingRequest(requests[1], trackingRequests[5]);


            // trackVisibleContentImpressions()
            results = fetchTrackedRequests(token8);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "3", "count enabledVisibleContentImpressions requests." );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], trackingRequests[6]);
            assertTrackingRequest(requests[1], trackingRequests[5]);
            assertTrackingRequest(requests[2], trackingRequests[1]);


            // enableTrackOnlyVisibleContent(false, 500)
            results = fetchTrackedRequests(token9);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "6", "count automatically tracked requests via time interval. " );

            var requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], trackingRequests[6]);
            assertTrackingRequest(requests[1], trackingRequests[5]);
            assertTrackingRequest(requests[2], trackingRequests[1]);
            assertTrackingRequest(requests[3], trackingRequests[4]);
            assertTrackingRequest(requests[4], trackingRequests[3]);
            assertTrackingRequest(requests[5], trackingRequests[2]);


            // enableTrackOnlyVisibleContent(true, 0)
            results = fetchTrackedRequests(token10);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "6", "count automatically tracked requests, via scroll. " );

            start();
        }, 13000);

        expected =
            [
                {
                    "name": "My Ad 7",
                    "piece": "Unknown",
                    "target": "http://img7.example.com"
                },
                {
                    "name": "http://www.example.com/path/xyz.jpg",
                    "piece": "http://www.example.com/path/xyz.jpg",
                    "target": "http://img6.example.com"
                },
                {
                    "name": "My Ad 5",
                    "piece": "http://img5.example.com/path/xyz.jpg",
                    "target": origin + "/anylink5"
                },
                {
                    "name": "My content 4",
                    "piece": "My content 4",
                    "target": "http://img4.example.com"
                },
                {
                    "name": toAbsolutePath("img3-en.jpg"),
                    "piece": toAbsoluteUrl("img3-en.jpg"),
                    "target": "http://img3.example.com"
                },
                {
                    "name": "img.jpg",
                    "piece": "img.jpg",
                    "target": "http://img2.example.com"
                },
                {
                    "name": toAbsolutePath("img1-en.jpg"),
                    "piece": toAbsoluteUrl("img1-en.jpg"),
                    "target": ""
                },
                {
                    "name": "/tests/javascript/img1-en.jpg",
                    "piece": toAbsoluteUrl("img1-en.jpg"),
                    "target": ""
                }];

        var consoleOld = console;
        var loggedContentBlocks = [];
        console = {log: function (content){
            loggedContentBlocks = content;
        }};
        tracker.logAllContentBlocksOnPage();
        console = consoleOld;
        expected =
            [
                {
                    "name": "My Ad 7",
                    "piece": "Unknown",
                    "target": "http://img7.example.com"
                },
                {
                    "name": "http://www.example.com/path/xyz.jpg",
                    "piece": "http://www.example.com/path/xyz.jpg",
                    "target": "http://img6.example.com"
                },
                {
                    "name": "My Ad 5",
                    "piece": "http://img5.example.com/path/xyz.jpg",
                    "target": origin + "/anylink5"
                },
                {
                    "name": "My content 4",
                    "piece": "My content 4",
                    "target": "http://img4.example.com"
                },
                {
                    "name": toAbsolutePath("img3-en.jpg"),
                    "piece": toAbsoluteUrl("img3-en.jpg"),
                    "target": "http://img3.example.com"
                },
                {
                    "name": "img.jpg",
                    "piece": "img.jpg",
                    "target": "http://img2.example.com"
                },
                {
                    "name": toAbsolutePath("img1-en.jpg"),
                    "piece": toAbsoluteUrl("img1-en.jpg"),
                    "target": ""
                },
                {
                    "name": "/tests/javascript/img1-en.jpg",
                    "piece": toAbsoluteUrl("img1-en.jpg"),
                    "target": ""
                }];

        equal(expected.length, loggedContentBlocks.length, 'logAllContentBlocksOnPage should detect correct number of content blocks');
        equal(JSON.stringify(expected), JSON.stringify(loggedContentBlocks), 'logAllContentBlocksOnPage should log all content blocks');
    });

    test("trackingContentInteractionInteractive", function() {
        expect(15);

        function assertTrackingRequest(actual, expectedStartsWith, message)
        {
            if (!message) {
                message = '';
            } else {
                message += ', ';
            }

            expectedStartsWith = '<span>' + toAbsolutePath('matomo.php') + '?' + expectedStartsWith;

            strictEqual(actual.indexOf(expectedStartsWith), 0, message +  actual + ' should start with ' + expectedStartsWith);
            strictEqual(actual.indexOf('&idsite=1&rec=1'), expectedStartsWith.length);
        }

        function resetTracker(track, token)
        {
            tracker.getRequestQueue().sendRequests(); // make sure to send any remaining queued requests
            tracker.clearTrackedContentImpressions();
            tracker.clearEnableTrackOnlyVisibleContent();
            tracker.setCustomData('token', token);
            scrollToTop();
        }

        function preventClickDefault(selector)
        {
            $(_s(selector)).on('click', function (event) { event.preventDefault(); })
        }

        var token = getContentToken() + 'i'; // interactive namespace
        var origin = getOrigin();
        var originEncoded = window.encodeURIComponent(origin);
        var actual, expected, trackerUrl;

        var tracker = Piwik.getTracker();
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        resetTracker(tracker, token);

        var visitorIdStart = tracker.getVisitorId();
        // need to wait at least 1 second so that the cookie would be different, if it wasnt persisted
        wait(2000);


        setupContentTrackingFixture('trackingContent', document.body);

        tracker.trackAllContentImpressions();
        strictEqual(tracker.getTrackedContentImpressions().length, 7, 'should mark 7 content blocks as tracked');


        var token1 = '1' + token;
        resetTracker(tracker, token1);
        preventClickDefault('#isWithinOutlink');
        triggerEvent(_s('#isWithinOutlink'), 'click'); // should only track interaction and no outlink as link tracking not enabled

        tracker.enableLinkTracking();

        wait(500);

        var token2 = '2' + token;
        resetTracker(tracker, token2);
        preventClickDefault('#isWithinOutlink');
        triggerEvent(_s('#isWithinOutlink'), 'click'); // click on an element within a link

        wait(500);

        var token3 = '3' + token;
        resetTracker(tracker, token3);
        preventClickDefault('#isOutlink');
        triggerEvent(_s('#isOutlink'), 'click'); // click on the link element itself

        wait(500);

        var token4 = '4' + token;
        resetTracker(tracker, token4);
        preventClickDefault('#notWithinTarget');
        triggerEvent(_s('#notWithinTarget'), 'click'); // this element is in a content block, there is a content target, but this element is not child of content target


        var token5 = '5' + token;
        resetTracker(tracker, token5);
        preventClickDefault('#internalLink');

        wait(500);

        stop();
        setTimeout(function() {
            removeContentTrackingFixture();

            var results = fetchTrackedRequests(token1);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "1", "count #isWithinOutlink requests as interaction. " );

            var requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();
            assertTrackingRequest(requests[0], 'c_i=click&c_n=img.jpg&c_p=img.jpg&c_t=http%3A%2F%2Fimg2.example.com&ca=1');


            results = fetchTrackedRequests(token2);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "1", "count #isWithinOutlink requests as outlink + interaction. " );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], 'link=http%3A%2F%2Fimg2.example.com%2F&c_i=click&c_n=img.jpg&c_p=img.jpg&c_t=http%3A%2F%2Fimg2.example.com&ca=1');


            results = fetchTrackedRequests(token3);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "1", "count #isOutlink requests as outlink + interaction. " );

            requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            assertTrackingRequest(requests[0], 'link=http%3A%2F%2Fimg2.example.com%2F&c_i=click&c_n=img.jpg&c_p=img.jpg&c_t=http%3A%2F%2Fimg2.example.com&ca=1');


            results = fetchTrackedRequests(token4);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "0", "count #notWithinTarget requests." );


            results = fetchTrackedRequests(token5);
            equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "0", "count #internalLink requests. (would be tracked via redirect which we do not want to perform in test and it is tested above)" );

            start();
        }, 4000);
    });

    test("Test API - consent", function() {
        expect(34);

        var queue;
        var tracker = Piwik.getTracker();
        tracker.setCustomData('token', getConsentToken() + '1');
        deepEqual(tracker.getConsentRequestsQueue(), [], "getConsentRequestsQueue, by default is empty" );
        strictEqual(tracker.hasRememberedConsent(), false, "hasRememberedConsent, has no consent given by default" );
        strictEqual(tracker.getRememberedConsent(), null, "getConsentRequestsQueue, does not return consent cookie content as no consent given" );
        strictEqual(tracker.hasConsent(), true, "hasConsent, assumes consent by default" );

        ok(!tracker.isConsentRequired(), 'by default consent is not required');
        ok(tracker.areCookiesEnabled(), 'by default cookies are enabled');
        tracker.requireConsent();
        ok(!tracker.areCookiesEnabled(), 'require consent disables cookies');

        ok(tracker.isConsentRequired(), 'consent is required after requiring it')
        deepEqual(tracker.getConsentRequestsQueue(), [], "getConsentRequestsQueue, still empty after requiring consent" );

        tracker.trackRequest('myFoo=bar&baz=1');
        stop();
        // wait for client hints to be detected
        setTimeout(function() {
            queue = tracker.getConsentRequestsQueue();
            strictEqual(1, queue.length, "getConsentRequestsQueue, did not execute tracking request when requiring consent but added this request to the queue" );
            strictEqual(0, queue[0].indexOf('myFoo=bar&baz=1'), "getConsentRequestsQueue, the request contains the tracking request" );

            tracker.trackRequest('myFoo=bar&baz=2');
            queue = tracker.getConsentRequestsQueue();
            strictEqual(2, queue.length, "getConsentRequestsQueue, did not execute tracking request again and added a second request to the queue" );
            strictEqual(0, queue[1].indexOf('myFoo=bar&baz=2'), "getConsentRequestsQueue, the request contains the tracking request" );

            tracker.setConsentGiven();
            deepEqual(tracker.getConsentRequestsQueue(), [], "setConsentGiven, should reset queued requests" );
            strictEqual(tracker.hasRememberedConsent(), false, "getConsentRequestsQueue, has not remembered consent" );
            strictEqual(tracker.getRememberedConsent(), null, "getConsentRequestsQueue, does not return consent cookie content as no consent given" );

            tracker.requireConsent();
            ok(!tracker.areCookiesEnabled(), 'after requiring consent, cookies are disabled');
            tracker.rememberConsentGiven();
            ok(tracker.areCookiesEnabled(), 'remember cookie consent enables cookies');

            strictEqual(tracker.hasRememberedConsent(), true, "rememberConsentGiven, sets cookie to remember consent" );
            var rememberedConsent = tracker.getRememberedConsent();
            strictEqual(String(rememberedConsent).length, 13, "getRememberedConsent, returns the data in milliseconds eg '1522200406749'" );
            strictEqual(String(rememberedConsent).slice(0, 2), '17', "getRememberedConsent, starts with correct data" );

            tracker.requireConsent();
            strictEqual(tracker.hasConsent(), true, "when requiring consent, and we remembered consent, consent should be given" );

            tracker.forgetConsentGiven();
            strictEqual(tracker.hasConsent(), false, "forgetConsentGiven(), will remove remembered consent and require consent again" );
            strictEqual(tracker.hasRememberedConsent(), false, "forgetConsentGiven, has forgotten consent" );
            strictEqual(tracker.getRememberedConsent(), null, "forgetConsentGiven, has no longer a date for consent given stored" );

            tracker.trackRequest('myFoo=bar&baz=3');

            deleteCookies();

            var tracker2 = Piwik.getTracker();
            tracker2.disableBrowserFeatureDetection();
            tracker2.setCustomData({ "token" : getConsentToken() + '2' });
            tracker2.trackRequest('myFoo=bar&baz=3');

            setTimeout(function() {
                var results = fetchTrackedRequests(getConsentToken() + '1');
                strictEqual(true, results.indexOf('myFoo=bar&baz=1') > 0, "setConsentGiven does replay all queued requests" );
                strictEqual(true, results.indexOf('myFoo=bar&baz=2') > 0, "setConsentGiven does replay all queued requests" );
                strictEqual(true, results.indexOf('ping=1') > 0, "setConsentGiven does replay all queued requests" );// sent when enabling cookies as part of setConsentGiven. Called twice in total
                strictEqual(4, (results.match(/consent=1/g) || []).length, "consent=1 parameter appears in URL when explicit consent given");

                var results2 = fetchTrackedRequests(getConsentToken() + '2');
                strictEqual(true, results2.indexOf('myFoo=bar&baz=3') > 0, "normal request" );
                strictEqual(0, (results2.match(/consent=1/g) || []).length, "consent=1 parameter not added when consent is assumed");
                start();
            }, 3000);
        }, 2000);
    });

    test("Test API - cookie consent", function() {
        expect(16);

        var queue;
        var tracker = Piwik.getTracker();
        tracker.setCustomData('token', getCookieConsentToken() + '1');
        strictEqual(tracker.areCookiesEnabled(), true, "areCookiesEnabled, enabled by default" );
        strictEqual(tracker.getRememberedCookieConsent(), 0, "getRememberedCookieConsent, not set by default" );

        var success = tracker.requireCookieConsent();
        ok(success, 'cookies were disabled after calling requireCookieConsent');
        ok(!tracker.areCookiesEnabled(), 'disabling cookies disables cookies');

        tracker.setCookieConsentGiven();
        ok(tracker.areCookiesEnabled(), 'setCookieConsentGiven enables cookies');

        tracker.rememberCookieConsentGiven();
        var nowBefore = new Date().getTime() - 10000;
        var nowAfter = new Date().getTime() + 10000;
        var timeConsentGiven = tracker.getRememberedCookieConsent();
        ok(timeConsentGiven && nowBefore < timeConsentGiven && nowAfter > timeConsentGiven, "getRememberedCookieConsent, returns time was given" );

        success = tracker.requireCookieConsent();
        ok(!success, 'cookies were not disabled because consent was remembered')
        ok(tracker.areCookiesEnabled(), 'disableCookies wont disable cookies if cookie consent was remembered');

        tracker.forgetCookieConsentGiven();
        ok(!tracker.areCookiesEnabled(), 'forgetCookieConsentGiven will disable cookies');

        tracker.setCookieConsentGiven();
        ok(tracker.areCookiesEnabled(), 'cookies can be enabled again after forgetting cookies');

        tracker.requireCookieConsent();
        ok(!tracker.areCookiesEnabled(), 'requireCookieConsent works after forgetting cookies');

        tracker.rememberCookieConsentGiven();
        ok(tracker.areCookiesEnabled(), 'cookies are enabled before disabling it');

        tracker.disableCookies();
        ok(!tracker.areCookiesEnabled(), 'disable cookies always disables cookies');
        tracker.forgetCookieConsentGiven();
    });

    test("Test API - set cookie domain", function() {
        expect(6);

        var tracker = Piwik.getTracker();
        var cookie_domain = tracker.getCookieDomain()
        var test_domain = '.' + cookie_domain;

        tracker.setCookieDomain(cookie_domain + '.broken.tld')
        equal(tracker.getCookieDomain(), cookie_domain, "can't set a bad cookie domain" );

        tracker.requireCookieConsent();
        tracker.setCookieDomain(test_domain);
        equal(tracker.getCookieDomain(), test_domain, "can set cookie domain after requireCookieConsent disables cookies" );

        var interceptedMessages = testWithWrappedConsole(function () {
            var tracker2 = Piwik.getTracker();

            tracker2.setCookieDomain(window.location.hostname);
        });

        deepEqual(interceptedMessages, [], "no console logs should have been outputted when setting a correct cookie domain");

        function testWithWrappedConsole(callback) {
            var interceptedMessages = [];

            try {
                var originalConsoleError = console.error;
                console.error = function catchConsoleError(message) {
                    interceptedMessages.push(message);
                };

                callback();
            } catch (e) {
                console.error = originalConsoleError;
                throw e;
            }
            console.error = originalConsoleError;

            return interceptedMessages;
        }
    });

    test("Test API - optOut (via consent feature)", function () {
        expect(9);

        var token = getOptInToken();

        var tracker = Piwik.getTracker();
        tracker.setCustomData({ "token" : token });

        // test default consent w/o cookie
        strictEqual(tracker.hasConsent(), true, "hasConsent(), should be true by default" );

        stop();
        Q.delay(1).then(function () {
            // test track w/ assumed consent
            tracker.trackRequest('myFoo=bar&baz=1');

            return Q.delay(500);
        }).then(function () {
            // opt user out & track w/ consent_removed
            tracker.optUserOut();
            strictEqual(tracker.hasConsent(), false, "optUserOut(), should have set the cookie" );

            tracker.trackRequest('myFoo=bar&baz=2');

            return Q.delay(500);
        }).then(function () {
            // new tracker (so new consent request queue), check it detects cookie
            var tracker2 = Piwik.getTracker();
            tracker2.setCustomData({ "token" : token });
            strictEqual(tracker.hasConsent(), false, "hasConsent(), should be false in the second tracker since we opted out before");

            // forget user opt out & check new tracker sends request
            tracker2.forgetUserOptOut();
            tracker2.trackRequest('myFoo=bar&baz=3');

            return Q.delay(500);
        }).then(function () {
            var results = fetchTrackedRequests(token);
            var requests = results.match(/<span\>(.*?)\<\/span\>/g);
            requests.shift();

            strictEqual(2, requests.length, "should have only sent two requests");
            strictEqual(true, requests[0].indexOf('myFoo=bar&baz=1') >= 0, "should have sent first request since user was not opted out");
            strictEqual(true, requests[1].indexOf('myFoo=bar&baz=3') >= 0, "should have sent third request since user was opted back in");
            start();
        }).catch(function (e) {
            console.log('caught', e.stack || e.message || e);
        });
    });

    test("Test optOut (via iframe)", function () {
        expect(6);

        var tracker = Piwik.addTracker();

        strictEqual(tracker.hasConsent(), true, "hasConsent(), should be true by default" );

        stop();
        Q.delay(1).then(function () {
            // Fire a message to set the opt in status to false
            var optOutMessage = JSON.stringify({maq_opted_in: false});
            tracker.hook.test._windowAlias.postMessage(optOutMessage, '*');
            return Q.delay(500);
       }).then(function () {
            strictEqual(tracker.hasConsent(), false, "optout message listener should have set the cookie to false (async tracker)" );
            // Fire another message to set it back to true
            var optInMessage = JSON.stringify({maq_opted_in: true});
            tracker.hook.test._windowAlias.postMessage(optInMessage, '*');
            return Q.delay(500);
        }).then(function () {
            strictEqual(tracker.hasConsent(), true, "optout message listener should have set the cookie to true" );
            start();
        }).catch(function (e) {
            console.log('caught', e.stack || e.message || e);
        });
    });

    test("Test refreshConsentStatus()", function() {
        expect(7);

        var tracker = Piwik.addTracker();
        var document = tracker.hook.test._windowAlias.document;

        // Test 1: no cookies
        tracker.hook.test._refreshConsentStatus();
        strictEqual(tracker.hasConsent(), true, "hasConsent() true when no cookies present");

        // Test 2: optout cookie
        document.cookie = 'mtm_consent_removed=12345';
        tracker.hook.test._refreshConsentStatus();
        strictEqual(tracker.hasConsent(), false, "hasConsent() false when optout cookie present");

        // Test 3: optin cookie
        document.cookie = 'mtm_consent_removed=;expires=Sun, 01 Dec 2019 00:00:01 GMT';
        document.cookie = 'mtm_consent=12345';
        tracker.hook.test._refreshConsentStatus();
        strictEqual(tracker.hasConsent(), true, "hasConsent() true when optin cookie present");

        // Test 4: both cookies
        document.cookie = 'mtm_consent_removed=12345';
        tracker.hook.test._refreshConsentStatus();
        strictEqual(tracker.hasConsent(), false, "hasConsent() false when optout cookie present");
    });

    test("Internal timers and setLinkTrackingTimer()", function() {
        expect(8);

        var tracker = Piwik.getTracker();
        tracker.disableBrowserFeatureDetection(); // avoid client hint queue

        ok( ! ( _paq instanceof Array ), "async tracker proxy not an array" );
        equal( typeof tracker, typeof _paq, "async tracker proxy" );

        var startTime, stopTime;

        wait(1500); // in case there is  a previous expireDateTime set

        equal( typeof tracker.hook.test._beforeUnloadHandler, 'function', 'beforeUnloadHandler' );

        startTime = new Date();
        tracker.hook.test._beforeUnloadHandler();
        stopTime = new Date();
        var msSinceStarted = (stopTime.getTime() - startTime.getTime());
        ok( msSinceStarted < 580, 'beforeUnloadHandler(): ' + msSinceStarted + ' was greater than 580 ' );

        tracker.disableAlwaysUseSendBeacon();
        tracker.setLinkTrackingTimer(2000);
        startTime = new Date();
        tracker.trackPageView();
        tracker.hook.test._beforeUnloadHandler();
        stopTime = new Date();
        var diffTime = (stopTime.getTime() - startTime.getTime());
        ok( diffTime >= 2000, 'setLinkTrackingTimer(): ' + diffTime);
    });

    test("Browser detector feature Disable and enable", function() {
        expect(9);
        var pattern = /(res=)|(cookie=)/;
        var siteIdPattern = /idsite/;
        var tracker = Piwik.getTracker();
        tracker.setCustomData({ "token" : getBrowserFeatureToken() });

        // browser feature should be enabled by default
        tracker.trackPageView('hello=world');

        stop();
        setTimeout(function() {
            // wait till client hints were resolved
            tracker.disableBrowserFeatureDetection();
            tracker.trackPageView('hello=world');

            tracker.enableBrowserFeatureDetection();
            tracker.trackPageView('hello=world');

            setTimeout(function() {
                // wait till client hints were resolved
                var results = fetchTrackedRequests(getBrowserFeatureToken(), true);
                ok(siteIdPattern.test(results[0]), 'Request should include idSite');
                ok(pattern.test(results[0]), 'Browser features should be included by default');

                ok(siteIdPattern.test(results[1]), 'Request should include idSite');
                ok(!pattern.test(results[1]), 'Browser features should not be included when manually disabled');

                ok(siteIdPattern.test(results[2]), 'Request should include idSite');
                ok(pattern.test(results[2]), 'Browser features should be included again if enabled again');
                start();
            }, 1500);
        }, 1500);
    });

    test("Test API - disable CampaignParameters", function() {
        expect(4);

        var tracker = Piwik.getTracker();

        tracker.setCustomData('token', getCampaignParamToken());

        tracker.disableCampaignParameters();

        tracker.setCustomUrl('http://localhost.localdomain/?mtm_campaign=something&mtm_kwd=keyword');

        // Do request when consent for campaign tracking hasn't been given
        tracker.trackRequest('foo=bar');
        stop();

        // wait for client hints to be detected
        setTimeout(function() {
          var results = fetchTrackedRequests(getCampaignParamToken());
          strictEqual(true, results.indexOf('mtm_campaign%3Dsomething%26mtm_kwd%3Dkeyword') === -1, "campaign parameters are stripped");
        }, 2000);
    });

    test("Test API - disable CampaignParameters has no effect when consent is required", function() {
        expect(1);

        var tracker = Piwik.getTracker();

        tracker.setCustomData('token', getCampaignParamToken() + '2');

        tracker.disableCampaignParameters();
        tracker.requireConsent();

        tracker.setCustomUrl('http://localhost.localdomain/?mtm_campaign=something&mtm_kwd=keyword');

        // Do request when consent for campaign tracking hasn't been given
        tracker.trackRequest('foo=bar');
        tracker.setConsentGiven();
        stop();

        // wait for client hints to be detected
        setTimeout(function() {
          var results = fetchTrackedRequests(getCampaignParamToken() + '2');
          strictEqual(false, results.indexOf('mtm_campaign%3Dsomething%26mtm_kwd%3Dkeyword') === -1, "campaign parameters are not stripped");

          start();
        }, 2000);
    });
<?php
}
?>
}

// do not name this addEventListener so it won't overwrite the member in window
function customAddEventListener(element, eventType, eventHandler, useCapture) {
    if (element.addEventListener) {
        element.addEventListener(eventType, eventHandler, useCapture);
        return true;
    }
    if (element.attachEvent) {
        return element.attachEvent('on' + eventType, eventHandler);
    }
    element['on' + eventType] = eventHandler;
}

(function (f) {
    if (document.addEventListener) {
        customAddEventListener(document, 'DOMContentLoaded', function ready() {
            document.removeEventListener('DOMContentLoaded', ready, false);
            f();
        });
    } else if (document.attachEvent) {
        document.attachEvent('onreadystatechange', function ready() {
            if (document.readyState === 'complete') {
                document.detachEvent('onreadystatechange', ready);
                f();
            }
        });

        if (document.documentElement.doScroll && window === top) {
            (function ready() {
                if (!hasLoaded) {
                    try {
                        document.documentElement.doScroll('left');
                    } catch (error) {
                        setTimeout(ready, 0);
                        return;
                    }
                    f();
                }
            }());
        }
    } else {
        customAddEventListener(window, 'load', f, false);
    }
})(PiwikTest);
 </script>

<?php
    include_once $root . '/core/Filesystem.php';
    $files = \Piwik\Filesystem::globr($root . '/plugins/'.$testPluginPath.'/tests/javascript', 'index.php');
    foreach ($files as $file) {
        include_once $file;
    }
    if ($testPluginPath !== '*') {
        // CI would always include tag manager
        $files = \Piwik\Filesystem::globr($root . '/plugins/TagManager/tests/javascript', 'index.php');
        foreach ($files as $file) {
            include_once $file;
        }
    }
?>

 <div id="jashDiv">
 <a href="#" onclick="javascript:loadJash();" title="Open JavaScript Shell"><img id="title" src="gnome-terminal.png" border="0" width="24" height="24" /></a>
 </div>

</body>
</html>
