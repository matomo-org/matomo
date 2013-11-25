<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>piwik.js: Unit Tests</title>
<?php
require_once(dirname(__FILE__).'/SQLite.php');

if(file_exists("stub.tpl")) {
	echo file_get_contents("stub.tpl");
}
?>
 <script type="text/javascript">
function getToken() {
	return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
<?php
$sqlite = false;
if (file_exists("enable_sqlite")) {
	if (class_exists('SQLite')) {
		$sqlite = true;
	} 
}

if(!$sqlite) {
	echo 'alert("WARNING: Javascript integration tests require sqlite, \n1) ensure this PHP extension is enabled to make sure you run all tests \napt-get install php5-sqlite \n2) Then please create an empty file enable_sqlite in tests/javascript/enable_sqlite \n3) Re-execute this page and make sure this popup does not display ");';
}
if ($sqlite) {
  echo '
var _paq = _paq || [];

function testCallingTrackPageViewBeforeSetTrackerUrlWorks() {
	_paq.push(["setCustomData", { "token" : getToken() }]);
	_paq.push(["trackPageView", "Asynchronous Tracker ONE"]);
	_paq.push(["setSiteId", 1]);
	_paq.push(["setTrackerUrl", "piwik.php"]);
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
 <script src="../../js/piwik.js" type="text/javascript"></script>
 <script src="../../plugins/Overlay/client/urlnormalizer.js" type="text/javascript"></script>
 <script src="piwiktest.js" type="text/javascript"></script>
 <link rel="stylesheet" href="assets/qunit.css" type="text/css" media="screen" />
 <link rel="stylesheet" href="jash/Jash.css" type="text/css" media="screen" />
 <script src="assets/qunit.js" type="text/javascript"></script>
 <script src="jslint/jslint.js" type="text/javascript"></script>
 <script type="text/javascript">
function _e(id){ 
	if (document.getElementById)
		return document.getElementById(id);
	if (document.layers)
		return document[id];
	if (document.all)
		return document.all[id];
}

function loadJash() {
	var jashDiv = _e('jashDiv');

	jashDiv.innerHTML = '';
	document.body.appendChild(document.createElement('script')).src='jash/Jash.js';
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
 </script>
</head>
<body>
<div style="display:none;"><a href="http://piwik.org/qa">First anchor link</a></div>

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
  <ul>
    <li><a id="click1" href="javascript:_e('div1').innerHTML='&lt;iframe src=&quot;http://click.example.com&quot;&gt;&lt;/iframe&gt;';void(0)" class="clicktest">ignore: implicit (JavaScript href)</a></li>
    <li><a id="click2" href="http://example.org" target="iframe2" class="piwik_ignore clicktest">ignore: explicit</a></li>
    <li><a id="click3" href="example.php" target="iframe3" class="clicktest">ignore: implicit (localhost)</a></li>
    <li><a id="click4" href="http://example.net" target="iframe4" class="clicktest">outlink: implicit (outbound URL)</a></li>
    <li><a id="click5" href="example.html" target="iframe5" class="piwik_link clicktest">outlink: explicit (localhost)</a></li>
    <li><a id="click6" href="example.pdf" target="iframe6" class="clicktest">download: implicit (file extension)</a></li>
    <li><a id="click7" href="example.word" target="iframe7" class="piwik_download clicktest">download: explicit</a></li>
    <li><a id="click8" href="example.exe" target="iframe8" class="clicktest">no click handler</a></li>
  </ul>
  <div id="clickDiv"></div>
 </div>

 <ol id="qunit-tests"></ol>

 <div id="main" style="display:none;"></div>

 <script>
var hasLoaded = false;
function PiwikTest() {
    hasLoaded = true;

	module('externals');

	test("JSLint", function() {
		expect(1);
		var src = '<?php
			$src = file_get_contents('../../js/piwik.js');
			$src = strtr($src, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
			echo "$src"; ?>';
		ok( JSLINT(src), "JSLint" );
//		alert(JSLINT.report(true));
	});

	test("JSON", function() {
		expect(49);

		var tracker = Piwik.getTracker(), dummy;

		equal( typeof JSON2.stringify, 'function', 'JSON.stringify function' );
		equal( typeof JSON2.stringify(dummy), 'undefined', 'undefined' );

		equal( JSON2.stringify(null), 'null', 'null' );
		equal( JSON2.stringify(true), 'true', 'true' );
		equal( JSON2.stringify(false), 'false', 'false' );
		ok( JSON2.stringify(0) === '0', 'Number 0' );
		ok( JSON2.stringify(1) === '1', 'Number 1' );
		ok( JSON2.stringify(-1) === '-1', 'Number -1' );
		ok( JSON2.stringify(42) === '42', 'Number 42' );

		ok( JSON2.stringify(1.0) === '1.0'
			|| JSON2.stringify(1.0) === '1', 'float 1.0' );

		equal( JSON2.stringify(1.1), '1.1', 'float 1.1' );
		equal( JSON2.stringify(""), '""', 'empty string' );
		equal( JSON2.stringify('"'), '"' + '\\' + '"' + '"', 'string "' );
		equal( JSON2.stringify('\\'), '"' + '\\\\' + '"', 'string \\' );

		equal( JSON2.stringify("1"), '"1"', 'string "1"' );
		equal( JSON2.stringify("ABC"), '"ABC"', 'string ABC' );
		equal( JSON2.stringify("\x40\x41\x42\x43"), '"@ABC"', '\\x hex string @ABC' );

		ok( JSON2.stringify("\u60a8\u597d") == '"您好"'
			|| JSON2.stringify("\u60a8\u597d") == '"\\u60a8\\u597d"', '\\u Unicode string 您好' );

		ok( JSON2.stringify("ßéàêö您好") == '"ßéàêö您好"'
			|| JSON2.stringify("ßéàêö您好") == '"\\u00df\\u00e9\\u00e0\\u00ea\\u00f6\\u60a8\\u597d"', 'string non-ASCII text' );

		equal( JSON2.stringify("20060228T08:00:00"), '"20060228T08:00:00"', 'string "20060228T08:00:00"' );

		var d = new Date();
		d.setTime(1240013340000);
		ok( JSON2.stringify(d) === '"2009-04-18T00:09:00Z"'
			|| JSON2.stringify(d) === '"2009-04-18T00:09:00.000Z"', 'Date');

		equal( JSON2.stringify([1, 2, 3]), '[1,2,3]', 'Array of numbers' );
		equal( JSON2.stringify({'key' : 'value'}), '{"key":"value"}', 'Object (members)' );
		equal( JSON2.stringify(
			[ {'domains' : ['example.com', 'example.ca']},
			{'names' : ['Sean', 'Cathy'] } ]
		), '[{"domains":["example.com","example.ca"]},{"names":["Sean","Cathy"]}]', 'Nested members' );

		equal( typeof eval('('+dummy+')'), 'undefined', 'eval undefined' );

		equal( typeof JSON2.parse, 'function', 'JSON.parse function' );

		// these throw a SyntaxError
//		equal( typeof JSON2.parse('undefined'), 'undefined', 'undefined' );
//		equal( typeof JSON2.parse(dummy), 'undefined', 'undefined' );
//		equal( JSON2.parse('undefined'), dummy, 'undefined' );
//		equal( JSON2.parse('undefined'), undefined, 'undefined' );

		strictEqual( JSON2.parse('null'), null, 'null' );
		strictEqual( JSON2.parse('true'), true, 'true' );
		strictEqual( JSON2.parse('false'), false, 'false' );

		equal( JSON2.parse('0'), 0, 'Number 0' );
		equal( JSON2.parse('1'), 1, 'Number 1' );
		equal( JSON2.parse('-1'), -1, 'Number -1' );
		equal( JSON2.parse('42'), 42, 'Number 42' );

		ok( JSON2.parse('1.0') === 1.0
			|| JSON2.parse('1.0') === 1, 'float 1.0' );

		equal( JSON2.parse('1.1'), 1.1, 'float 1.1' );
		equal( JSON2.parse('""'), "", 'empty string' );
		equal( JSON2.parse('"' + '\\' + '"' + '"'), '"', 'string "' );
		equal( JSON2.parse('"\\\\"'), '\\', 'string \\' );

		equal( JSON2.parse('"1"'), "1", 'string "1"' );
		equal( JSON2.parse('"ABC"'), "ABC", 'string ABC' );
		equal( JSON2.parse('"@ABC"'), "\x40\x41\x42\x43", 'Hex string @ABC' );

		ok( JSON2.parse('"您好"') == "\u60a8\u597d"
			&& JSON2.parse('"\\u60a8\\u597d"') == "您好", 'Unicode string 您好' );

		ok( JSON2.parse('"ßéàêö您好"') == "ßéàêö您好"
			&& JSON2.parse('"\\u00df\\u00e9\\u00e0\\u00ea\\u00f6\\u60a8\\u597d"') == "ßéàêö您好", 'string non-ASCII text' );

		equal( JSON2.parse('"20060228T08:00:00"'), "20060228T08:00:00", 'string "20060228T08:00:00"' );

		// these aren't converted back to Date objects
		equal( JSON2.parse('"2009-04-18T00:09:00Z"'), "2009-04-18T00:09:00Z", 'string "2009-04-18T00:09:00Z"' );
		equal( JSON2.parse('"2009-04-18T00:09:00.000Z"'), "2009-04-18T00:09:00.000Z", 'string "2009-04-18T00:09:00.000Z"' );

		deepEqual( JSON2.parse('[1,2,3]'), [1, 2, 3], 'Array of numbers' );
		deepEqual( JSON2.parse('{"key":"value"}'), {'key' : 'value'}, 'Object (members)' );
		deepEqual( JSON2.parse('[{"domains":["example.com","example.ca"]},{"names":["Sean","Cathy"]}]'),
			[ {'domains' : ['example.com', 'example.ca']}, {'names' : ['Sean', 'Cathy'] } ], 'Nested members' );
	});

	module("core");

	test("Basic requirements", function() {
		expect(3);

		equal( typeof encodeURIComponent, 'function', 'encodeURIComponent' );
		ok( RegExp, "RegExp" );
		ok( Piwik, "Piwik" );
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
		expect(53);

		equal( typeof Piwik.addPlugin, 'function', 'addPlugin' );
		equal( typeof Piwik.getTracker, 'function', 'getTracker' );
		equal( typeof Piwik.getAsyncTracker, 'function', 'getAsyncTracker' );

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
		equal( typeof tracker.setSiteId, 'function', 'setSiteId' );
		equal( typeof tracker.setCustomData, 'function', 'setCustomData' );
		equal( typeof tracker.getCustomData, 'function', 'getCustomData' );
		equal( typeof tracker.setCustomVariable, 'function', 'setCustomVariable' );
		equal( typeof tracker.getCustomVariable, 'function', 'getCustomVariable' );
		equal( typeof tracker.deleteCustomVariable, 'function', 'deleteCustomVariable' );
		equal( typeof tracker.setLinkTrackingTimer, 'function', 'setLinkTrackingTimer' );
		equal( typeof tracker.setDownloadExtensions, 'function', 'setDownloadExtensions' );
		equal( typeof tracker.addDownloadExtensions, 'function', 'addDownloadExtensions' );
		equal( typeof tracker.setDomains, 'function', 'setDomains' );
		equal( typeof tracker.setIgnoreClasses, 'function', 'setIgnoreClasses' );
		equal( typeof tracker.setRequestMethod, 'function', 'setRequestMethod' );
		equal( typeof tracker.setReferrerUrl, 'function', 'setReferrerUrl' );
		equal( typeof tracker.setCustomUrl, 'function', 'setCustomUrl' );
		equal( typeof tracker.setDocumentTitle, 'function', 'setDocumentTitle' );
		equal( typeof tracker.setDownloadClasses, 'function', 'setDownloadClasses' );
		equal( typeof tracker.setLinkClasses, 'function', 'setLinkClasses' );
		equal( typeof tracker.setCampaignNameKey, 'function', 'setCampaignNameKey' );
		equal( typeof tracker.setCampaignKeywordKey, 'function', 'setCampaignKeywordKey' );
		equal( typeof tracker.discardHashTag, 'function', 'discardHashTag' );
		equal( typeof tracker.setCookieNamePrefix, 'function', 'setCookieNamePrefix' );
		equal( typeof tracker.setCookieDomain, 'function', 'setCookieDomain' );
		equal( typeof tracker.setCookiePath, 'function', 'setCookiePath' );
		equal( typeof tracker.setVisitorCookieTimeout, 'function', 'setVisitorCookieTimeout' );
		equal( typeof tracker.setSessionCookieTimeout, 'function', 'setSessionCookieTimeout' );
		equal( typeof tracker.setReferralCookieTimeout, 'function', 'setReferralCookieTimeout' );
		equal( typeof tracker.setConversionAttributionFirstReferrer, 'function', 'setConversionAttributionFirstReferrer' );
		equal( typeof tracker.addListener, 'function', 'addListener' );
		equal( typeof tracker.enableLinkTracking, 'function', 'enableLinkTracking' );
		equal( typeof tracker.setHeartBeatTimer, 'function', 'setHeartBeatTimer' );
		equal( typeof tracker.killFrame, 'function', 'killFrame' );
		equal( typeof tracker.redirectFile, 'function', 'redirectFile' );
		equal( typeof tracker.setCountPreRendered, 'function', 'setCountPreRendered' );
		equal( typeof tracker.trackGoal, 'function', 'trackGoal' );
		equal( typeof tracker.trackLink, 'function', 'trackLink' );
		equal( typeof tracker.trackPageView, 'function', 'trackPageView' );
		// ecommerce
		equal( typeof tracker.setEcommerceView, 'function', 'setEcommerceView' );
		equal( typeof tracker.addEcommerceItem, 'function', 'addEcommerceItem' );
		equal( typeof tracker.trackEcommerceOrder, 'function', 'trackEcommerceOrder' );
		equal( typeof tracker.trackEcommerceCartUpdate, 'function', 'trackEcommerceCartUpdate' );
	});

	module("API and internals");

	test("Tracker is_a functions", function() {
		expect(22);

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
	});

	test("Tracker encode, decode, urldecode wrappers", function() {
		expect(6);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._encode, 'function', 'encodeWrapper' );
		equal( typeof tracker.hook.test._decode, 'function', 'decodeWrapper' );
		equal( typeof tracker.hook.test._urldecode, 'function', 'urldecodeWrapper' );

		equal( tracker.hook.test._encode("&=?;/#"), '%26%3D%3F%3B%2F%23', 'encodeWrapper()' );
		equal( tracker.hook.test._decode("%26%3D%3F%3B%2F%23"), '&=?;/#', 'decodeWrapper()' );
		equal( tracker.hook.test._urldecode("mailto:%69%6e%66%6f@%65%78%61%6d%70%6c%65.%63%6f%6d"), 'mailto:info@example.com', 'decodeWrapper()' );
	});

	test("Tracker getHostName(), getParameter(), urlFixup(), domainFixup(), titleFixup() and purify()", function() {
		expect(57);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._getHostName, 'function', 'getHostName' );
		equal( typeof tracker.hook.test._getParameter, 'function', 'getParameter' );

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

		equal( tracker.hook.test._getParameter('http://piwik.org/', 'q'), '', 'no query');
		equal( tracker.hook.test._getParameter('http://piwik.org/?q=test', 'q'), 'test', '?q');
		equal( tracker.hook.test._getParameter('http://piwik.org/?q=test#aq=not', 'q'), 'test', '?q');
		equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2', 'q'), 'test2', '&q');

        // getParameter in hash tag
        equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#aq=not', 'q'), 'test2', '&q');
		equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#aq=not', 'aq'), 'not', '#aq');
		equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'bq'), 'yes', '#bq');
		equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#pk_campaign=campaign', 'pk_campaign'), 'campaign', '#pk_campaign');
		equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'q'), 'test2', '#q');

        // URL decoded
		equal( tracker.hook.test._getParameter('http://piwik.org/?q=http%3a%2f%2flocalhost%2f%3fr%3d1%26q%3dfalse', 'q'), 'http://localhost/?r=1&q=false', 'url');
        equal( tracker.hook.test._getParameter('http://piwik.org/?q=http%3a%2f%2flocalhost%2f%3fr%3d1%26q%3dfalse&notq=not', 'q'), 'http://localhost/?r=1&q=false', 'url');

        // non existing parameters
        equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'bqq'), "", '#q');
        equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'bq='), "", '#q');
        equal( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2#bq=yes&aq=not', 'sp='), "", '#q');

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
		equal( tracker.hook.test._titleFixup( document.title ), 'piwik.js: Unit Tests', 'hello string' );

		equal( typeof tracker.hook.test._purify, 'function', 'purify' );

		equal( tracker.hook.test._purify('http://example.com'), 'http://example.com', 'http://example.com');
		equal( tracker.hook.test._purify('http://example.com#hash'), 'http://example.com#hash', 'http://example.com#hash');
		equal( tracker.hook.test._purify('http://example.com/?q=xyz#hash'), 'http://example.com/?q=xyz#hash', 'http://example.com/?q=xyz#hash');

		tracker.discardHashTag(true);

		equal( tracker.hook.test._purify('http://example.com'), 'http://example.com', 'http://example.com');
		equal( tracker.hook.test._purify('http://example.com#hash'), 'http://example.com', 'http://example.com#hash');
		equal( tracker.hook.test._purify('http://example.com/?q=xyz#hash'), 'http://example.com/?q=xyz', 'http://example.com/?q=xyz#hash');
	});

	// support for setCustomUrl( relativeURI )
	test("getProtocolScheme and resolveRelativeReference", function() {
		expect(27);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._getProtocolScheme, 'function', "getProtocolScheme" );

		ok( tracker.hook.test._getProtocolScheme('http://example.com') === 'http', 'http://' );
		ok( tracker.hook.test._getProtocolScheme('https://example.com') === 'https', 'https://' );
		ok( tracker.hook.test._getProtocolScheme('file://somefile.txt') === 'file', 'file://' );
		ok( tracker.hook.test._getProtocolScheme('mailto:somebody@example.com') === 'mailto', 'mailto:' );
		ok( tracker.hook.test._getProtocolScheme('javascript:alert(document.cookie)') === 'javascript', 'javascript:' );
		ok( tracker.hook.test._getProtocolScheme('') === null, 'empty string' );	
		ok( tracker.hook.test._getProtocolScheme(':') === null, 'unspecified scheme' );	
		ok( tracker.hook.test._getProtocolScheme('scheme') === null, 'missing colon' );	


		equal( typeof tracker.hook.test._resolveRelativeReference, 'function', 'resolveRelativeReference' );

		var i, j, data = [
			// unsupported
//			['http://example.com/index.php/pathinfo?query', 'test.php', 'http://example.com/test.php'],
//			['http://example.com/subdir/index.php', '../test.php', 'http://example.com/test.php'],

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

	test("Tracker setDomains() and isSiteHostName()", function() {
		expect(13);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._isSiteHostName, 'function', "isSiteHostName" );

		// test wildcards
		tracker.setDomains( ['*.Example.com'] );

		// skip test if testing on localhost
		ok( window.location.hostname != 'localhost' ? !tracker.hook.test._isSiteHostName('localhost') : true, '!isSiteHostName("localhost")' );

		ok( !tracker.hook.test._isSiteHostName('google.com'), '!isSiteHostName("google.com")' );
		ok( tracker.hook.test._isSiteHostName('example.com'), 'isSiteHostName("example.com")' );
		ok( tracker.hook.test._isSiteHostName('www.example.com'), 'isSiteHostName("www.example.com")' );
		ok( tracker.hook.test._isSiteHostName('www.sub.example.com'), 'isSiteHostName("www.sub.example.com")' );

		tracker.setDomains( 'dev.piwik.org' );
		ok( !tracker.hook.test._isSiteHostName('piwik.org'), '!isSiteHostName("piwik.org")' );
		ok( tracker.hook.test._isSiteHostName('dev.piwik.org'), 'isSiteHostName("dev.piwik.org")' );
		ok( !tracker.hook.test._isSiteHostName('piwik.example.org'), '!isSiteHostName("piwik.example.org")');
		ok( !tracker.hook.test._isSiteHostName('dev.piwik.org.com'), '!isSiteHostName("dev.piwik.org.com")');

		tracker.setDomains( '.piwik.org' );
		ok( tracker.hook.test._isSiteHostName('piwik.org'), 'isSiteHostName("piwik.org")' );
		ok( tracker.hook.test._isSiteHostName('dev.piwik.org'), 'isSiteHostName("dev.piwik.org")' );
		ok( !tracker.hook.test._isSiteHostName('piwik.org.com'), '!isSiteHostName("piwik.org.com")');
	});

	test("Tracker getClassesRegExp()", function() {
		expect(3);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._getClassesRegExp, 'function', "getClassesRegExp" );

		var download = tracker.hook.test._getClassesRegExp([], 'download');
		ok( download.test('piwik_download'), 'piwik_download (default)' );

		var outlink = tracker.hook.test._getClassesRegExp([], 'link');
		ok( outlink.test('piwik_link'), 'piwik_link (default)' );

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

	test("Tracker setDownloadExtensions(), addDownloadExtensions(), setDownloadClasses(), setLinkClasses(), and getLinkType()", function() {
		expect(24);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._getLinkType, 'function', 'getLinkType' );

		equal( tracker.hook.test._getLinkType('something', 'goofy.html', false), 'link', 'implicit link' );
		equal( tracker.hook.test._getLinkType('something', 'goofy.pdf', false), 'download', 'external PDF files are downloads' );
		equal( tracker.hook.test._getLinkType('something', 'goofy.pdf', true), 'download', 'local PDF are downloads' );

		equal( tracker.hook.test._getLinkType('piwik_download', 'piwiktest.ext', true), 'download', 'piwik_download' );
		equal( tracker.hook.test._getLinkType('abc piwik_download xyz', 'piwiktest.ext', true), 'download', 'abc piwik_download xyz' );
		equal( tracker.hook.test._getLinkType('piwik_link', 'piwiktest.asp', true), 'link', 'piwik_link' );
		equal( tracker.hook.test._getLinkType('abc piwik_link xyz', 'piwiktest.asp', true), 'link', 'abc piwik_link xyz' );
		equal( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true), 'download', 'download extension' );
		equal( tracker.hook.test._getLinkType('something', 'piwiktest.ext', true), 0, '[1] link (default)' );

		equal( tracker.hook.test._getLinkType('something', 'file.zip', true), 'download', 'download file.zip' );
		equal( tracker.hook.test._getLinkType('something', 'index.php?name=file.zip#anchor', true), 'download', 'download file.zip (anchor)' );
		equal( tracker.hook.test._getLinkType('something', 'index.php?name=file.zip&redirect=yes', true), 'download', 'download file.zip (is param)' );
		equal( tracker.hook.test._getLinkType('something', 'file.zip?mirror=true', true), 'download', 'download file.zip (with param)' );

		tracker.setDownloadExtensions('pk');
		equal( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true), 'download', '[1] .pk == download extension' );
		equal( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true), 0, '.txt =! download extension' );

		tracker.addDownloadExtensions('xyz');
		equal( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true), 'download', '[2] .pk == download extension' );
		equal( tracker.hook.test._getLinkType('something', 'piwiktest.xyz', true), 'download', '.xyz == download extension' );

		tracker.setDownloadClasses(['a', 'b']);
		equal( tracker.hook.test._getLinkType('abc piwik_download', 'piwiktest.ext', true), 'download', 'download (default)' );
		equal( tracker.hook.test._getLinkType('abc a', 'piwiktest.ext', true), 'download', 'download (a)' );
		equal( tracker.hook.test._getLinkType('b abc', 'piwiktest.ext', true), 'download', 'download (b)' );

		tracker.setLinkClasses(['c', 'd']);
		equal( tracker.hook.test._getLinkType('abc piwik_link', 'piwiktest.ext', true), 'link', 'link (default)' );
		equal( tracker.hook.test._getLinkType('abc c', 'piwiktest.ext', true), 'link', 'link (c)' );
		equal( tracker.hook.test._getLinkType('d abc', 'piwiktest.ext', true), 'link', 'link (d)' );
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

	test("prefixPropertyName()", function() {
		expect(3);

		var tracker = Piwik.getTracker();

		equal( typeof tracker.hook.test._prefixPropertyName, 'function', 'prefixPropertyName' );
		equal( tracker.hook.test._prefixPropertyName('', 'hidden'), 'hidden', 'no prefix' );
		equal( tracker.hook.test._prefixPropertyName('webkit', 'hidden'), 'webkitHidden', 'webkit prefix' );
	});

	test("Internal timers and setLinkTrackingTimer()", function() {
		expect(5);

		var tracker = Piwik.getTracker();

		ok( ! ( _paq instanceof Array ), "async tracker proxy not an array" );
		equal( typeof tracker, typeof _paq, "async tracker proxy" );

		var startTime, stopTime;

		equal( typeof tracker.hook.test._beforeUnloadHandler, 'function', 'beforeUnloadHandler' );

		startTime = new Date();
		tracker.hook.test._beforeUnloadHandler();
		stopTime = new Date();
		ok( (stopTime.getTime() - startTime.getTime()) < 500, 'beforeUnloadHandler()' );

		tracker.setLinkTrackingTimer(2000);
		startTime = new Date();
		tracker.trackPageView();
		tracker.hook.test._beforeUnloadHandler();
		stopTime = new Date();
		ok( (stopTime.getTime() - startTime.getTime()) >= 2000, 'setLinkTrackingTimer()' );
	});
	
	test("Overlay URL Normalizer", function() {
		expect(11);

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
	});

<?php
if ($sqlite) {
	echo '

	module("request", {
		setup: function () {
			ok(true, "request.setup");

			deleteCookies();
			ok(document.cookie === "", "deleteCookies");
		},
		teardown: function () {
			ok(true, "request.teardown");
		}
	});

	test("tracking", function() {
		expect(85);

		/*
		 * Prevent Opera and HtmlUnit from performing the default action (i.e., load the href URL)
		 */
		var stopEvent = function (evt) {
				evt = evt || window.event;

//				evt.cancelBubble = true;
				evt.returnValue = false;

				if (evt.preventDefault)
					evt.preventDefault();
//				if (evt.stopPropagation)
//					evt.stopPropagation();

//				return false;
			};

		var tracker = Piwik.getTracker();
		tracker.setTrackerUrl("piwik.php");
		tracker.setSiteId(1);

		function wait(msecs)
		{
			var start = new Date().getTime();
			var cur = start
			while(cur - start < msecs)
			{
				cur = new Date().getTime();
			}
		}
				
		var visitorIdStart = tracker.getVisitorId();
		// need to wait at least 1 second so that the cookie would be different, if it wasnt persisted
		wait(2000);
		var visitorIdStart2 = tracker.getVisitorId();
		ok( visitorIdStart == visitorIdStart2, "getVisitorId() same when called twice with more than 1 second delay");
		var customUrl = "http://localhost.localdomain/?utm_campaign=YEAH&utm_term=RIGHT!";
		tracker.setCustomUrl(customUrl);

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
		
		tracker.setDocumentTitle("PiwikTest");
		
		var referrerUrl = "http://referrer.example.com/page/sub?query=test&test2=test3";
		tracker.setReferrerUrl(referrerUrl);

		referrerTimestamp = Math.round(new Date().getTime() / 1000);
		tracker.trackPageView();

		tracker.trackPageView("CustomTitleTest");

		var customUrlShouldNotChangeCampaign = "http://localhost.localdomain/?utm_campaign=NONONONONONONO&utm_term=PLEASE NO!";
		tracker.setCustomUrl(customUrl);

		tracker.trackPageView();

		tracker.trackLink("http://example.ca", "link", { "token" : getToken() });

		// async tracker proxy
		_paq.push(["trackLink", "http://example.fr/async.zip", "download",  { "token" : getToken() }]);

		// push function
		_paq.push([ function(t) {
			tracker.trackLink("http://example.de", "link", { "token" : t });
		}, getToken() ]);

		tracker.setRequestMethod("POST");
		tracker.trackGoal(42, 69, { "token" : getToken(), "boy" : "Michael", "girl" : "Mandy"});

		piwik_log("CompatibilityLayer", 1, "piwik.php", { "token" : getToken() });

		tracker.hook.test._addEventListener(_e("click8"), "click", stopEvent);
		QUnit.triggerEvent( _e("click8"), "click" );

		tracker.enableLinkTracking();

		tracker.setRequestMethod("GET");
		var buttons = new Array("click1", "click2", "click3", "click4", "click5", "click6", "click7");
		for (var i=0; i < buttons.length; i++) {
			tracker.hook.test._addEventListener(_e(buttons[i]), "click", stopEvent);
			QUnit.triggerEvent( _e(buttons[i]), "click" );
		}

		var xhr = window.XMLHttpRequest ? new window.XMLHttpRequest() :
			window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") :
			null;

		var clickDiv = _e("clickDiv"),
			anchor = document.createElement("a");

		anchor.id = "click9";
		anchor.href = "http://example.us";
		clickDiv.innerHTML = "";
		clickDiv.appendChild(anchor);
		tracker.addListener(anchor);
		tracker.hook.test._addEventListener(anchor, "click", stopEvent);
		QUnit.triggerEvent( _e("click9"), "click" );

		var visitorId1, visitorId2;

		_paq.push([ function() {
			visitorId1 = Piwik.getAsyncTracker().getVisitorId();
		}]);
		visitorId2 = tracker.getVisitorId();
		ok( visitorId1 && visitorId1 != "" && visitorId2 && visitorId2 != "" && (visitorId1 == visitorId2), "getVisitorId()" );

		var visitorInfo1, visitorInfo2;

		// Visitor INFO + Attribution INFO tests
		tracker.setReferrerUrl(referrerUrl);
		_paq.push([ function() {
			visitorInfo1 = Piwik.getAsyncTracker().getVisitorInfo();
			attributionInfo1 = Piwik.getAsyncTracker().getAttributionInfo();
			referrer1 = Piwik.getAsyncTracker().getAttributionReferrerUrl();
		}]);
		visitorInfo2 = tracker.getVisitorInfo();
		ok( visitorInfo1 && visitorInfo2 && visitorInfo1.length == visitorInfo2.length, "getVisitorInfo()" );
		for (var i = 0; i < 6; i++) {
			ok( visitorInfo1[i] == visitorInfo2[i], "(loadVisitorId())["+i+"]" );
		}
		
		attributionInfo2 = tracker.getAttributionInfo();
		ok( attributionInfo1 && attributionInfo2 && attributionInfo1.length == attributionInfo2.length, "getAttributionInfo()" );
		referrer2 = tracker.getAttributionReferrerUrl();
		ok( referrer2 == referrerUrl, "getAttributionReferrerUrl()" );
		ok( referrer1 == referrerUrl, "async getAttributionReferrerUrl()" );
		referrerTimestamp2 = tracker.getAttributionReferrerTimestamp();
		ok( referrerTimestamp2 == referrerTimestamp, "tracker.getAttributionReferrerTimestamp()" );
		campaignName2 = tracker.getAttributionCampaignName();
		campaignKeyword2 = tracker.getAttributionCampaignKeyword();
		ok( campaignName2 == "YEAH", "getAttributionCampaignName()");
		ok( campaignKeyword2 == "RIGHT!", "getAttributionCampaignKeyword()");
		

		// Test visitor ID at the start is the same at the end
		var visitorIdEnd = tracker.getVisitorId();
		ok( visitorIdStart == visitorIdEnd, "tracker.getVisitorId() same at the start and end of process");
		
		// Custom variables
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
		tracker.setEcommerceView( "", false, ["CATEGORY1","CATEGORY2"] );
		deepEqual( tracker.getCustomVariable(3, "page"), false, "Ecommerce view SKU");
		tracker.setEcommerceView( "SKUMultiple", false, ["CATEGORY1","CATEGORY2"] );
		deepEqual( tracker.getCustomVariable(4, "page"), ["_pkn",""], "Ecommerce view Name");
		deepEqual( tracker.getCustomVariable(5, "page"), ["_pkc","[\"CATEGORY1\",\"CATEGORY2\"]"], "Ecommerce view Category");
		tracker.trackPageView("MultipleCategories");
		
		var tracker2 = Piwik.getTracker();
		tracker2.setTrackerUrl("piwik.php");
		tracker2.setSiteId(1);
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
		tracker3.setTrackerUrl("piwik.php");
		tracker3.setSiteId(1);
		tracker3.setCustomData({ "token" : getToken() });
		tracker3.setCookieNamePrefix("PREFIX");
		ok( tracker3.getCustomVariable(1) === false, "getCustomVariable(cvarDeleted) from cookie  === false" );
		
		// Ecommerce Views
		tracker3.setEcommerceView( "SKU", "NAME HERE", "CATEGORY HERE" );
		deepEqual( tracker3.getCustomVariable(3, "page"), ["_pks","SKU"], "Ecommerce view SKU");
		deepEqual( tracker3.getCustomVariable(4, "page"), ["_pkn","NAME HERE"], "Ecommerce view Name");
		deepEqual( tracker3.getCustomVariable(5, "page"), ["_pkc","CATEGORY HERE"], "Ecommerce view Category");
		tracker3.trackPageView("EcommerceView");

		//Ecommerce tests
		tracker3.addEcommerceItem("SKU PRODUCT", "PRODUCT NAME", "PRODUCT CATEGORY", 11.1111, 2); 
		tracker3.addEcommerceItem("SKU PRODUCT", "random", "random PRODUCT CATEGORY", 11.1111, 2); 
		tracker3.addEcommerceItem("SKU ONLY SKU", "", "", "", ""); 
		tracker3.addEcommerceItem("SKU ONLY NAME", "PRODUCT NAME 2", "", ""); 
		tracker3.addEcommerceItem("SKU NO PRICE NO QUANTITY", "PRODUCT NAME 3", "CATEGORY", "", "" ); 
		tracker3.addEcommerceItem("SKU ONLY" ); 
		tracker3.trackEcommerceCartUpdate( 555.55 );
		tracker3.trackEcommerceOrder( "ORDER ID YES", 666.66, 333, 222, 111, 1 );
		
		// do not track
		tracker3.setDoNotTrack(false);

		// Append tracking url parameter
		tracker3.appendToTrackingUrl("appended=1&appended2=value");

        // Track pageview
		tracker3.trackPageView("DoTrack");

		// Firefox 9: navigator.doNotTrack is read-only
		navigator.doNotTrack = "yes";
		if (navigator.doNotTrack === "yes")
		{
			tracker3.setDoNotTrack(true);
			tracker3.trackPageView("DoNotTrack");
		}


		stop();
		setTimeout(function() {
			xhr.open("GET", "piwik.php?requests=" + getToken(), false);
			xhr.send(null);
			results = xhr.responseText;
			equal( (/<span\>([0-9]+)\<\/span\>/.exec(results))[1], "28", "count tracking events" );

			// tracking requests
			ok( /PiwikTest/.test( results ), "trackPageView(), setDocumentTitle()" );
			ok( results.indexOf("tests/javascript/piwik.php?action_name=Asynchronous%20Tracker%20ONE&idsite=1&rec=1") >= 0 , "async trackPageView() called before setTrackerUrl() should work" );
			ok( /Asynchronous%20tracking%20TWO/.test( results ), "async trackPageView() called after another trackPageView()" );
			ok( /CustomTitleTest/.test( results ), "trackPageView(customTitle)" );
			ok( ! /click.example.com/.test( results ), "click: ignore href=javascript" );
			ok( /example.ca/.test( results ), "trackLink()" );
			ok( /example.fr/.test( results ), "async trackLink()" );
			ok( /example.de/.test( results ), "push function" );
			ok( /example.us/.test( results ), "addListener()" );

			ok( /example.net/.test( results ), "setRequestMethod(GET), click: implicit outlink (by outbound URL)" );
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
			ok( /cookiename/.test( results ) && /cookievalue/.test( results ), "tracking request contains custom variable" );
			ok( /DeleteCustomVariableCookie/.test( results ), "tracking request deleting custom variable" );
			ok( /DoTrack/.test( results ), "setDoNotTrack(false)" );
			ok( ! /DoNotTrack/.test( results ), "setDoNotTrack(true)" );

			// Test Custom variables
			ok( /SaveCustomVariableCookie.*&cvar=%7B%222%22%3A%5B%22cookiename2PAGE%22%2C%22cookievalue2PAGE%22%5D%7D.*&_cvar=%7B%221%22%3A%5B%22cookiename%22%2C%22cookievalue%22%5D%2C%222%22%3A%5B%22cookiename2%22%2C%22cookievalue2%22%5D%7D/.test(results), "test custom vars are set");
			
			// Test campaign parameters set
			ok( /&_rcn=YEAH&_rck=RIGHT!/.test( results), "Test campaign parameters found"); 
			ok( /&_ref=http%3A%2F%2Freferrer.example.com%2Fpage%2Fsub%3Fquery%3Dtest%26test2%3Dtest3/.test( results), "Test cookie Ref URL found "); 

			// Test site search
			ok( /search=No%20result%20keyword%20%C3%A9%C3%A0&search_cat=Search%20cat&search_count=0&idsite=1/.test(results), "site search, cat, 0 result ");
			ok( /search=Keyword%20with%2010%20results&search_count=10&idsite=1/.test(results), "site search, no cat, 10 results ");
			ok( /search=search%20Keyword&idsite=1/.test(results), "site search, no cat, no results count ");

			// Test events
			ok( /(e_c=Event%20Category&e_a=Event%20Action&idsite=1).*(&e_cvar=%7B%221%22%3A%5B%22cvarEventName%22%2C%22cvarEventValue%22%5D%2C%222%22%3A%5B%22cookiename2EVENT%22%2C%22cookievalue2EVENT%22%5D%7D)/.test(results), "event Category + Action + Custom Variable");
			ok( /e_c=Event%20Category2&e_a=Event%20Action2&e_n=Event%20Name2&idsite=1/.test(results), "event Category + Action + Name");
			ok( /e_c=Event%20Category3&e_a=Event%20Action3&e_n=Event%20Name3&e_v=3.333&idsite=1/.test(results), "event Category + Action + Name + Value");

			// ecommerce view
			ok( /(EcommerceView).*(&cvar=%7B%225%22%3A%5B%22_pkc%22%2C%22CATEGORY%20HERE%22%5D%2C%223%22%3A%5B%22_pks%22%2C%22SKU%22%5D%2C%224%22%3A%5B%22_pkn%22%2C%22NAME%20HERE%22%5D%7D)/.test(results)
			 || /(EcommerceView).*(&cvar=%7B%223%22%3A%5B%22_pks%22%2C%22SKU%22%5D%2C%224%22%3A%5B%22_pkn%22%2C%22NAME%20HERE%22%5D%2C%225%22%3A%5B%22_pkc%22%2C%22CATEGORY%20HERE%22%5D%7D)/.test(results), "ecommerce view");

			// ecommerce view multiple categories
			ok( /(MultipleCategories).*(&cvar=%7B%222%22%3A%5B%22cookiename2PAGE%22%2C%22cookievalue2PAGE%22%5D%2C%225%22%3A%5B%22_pkc%22%2C%22%5B%5C%22CATEGORY1%5C%22%2C%5C%22CATEGORY2%5C%22%5D%22%5D%2C%223%22%3A%5B%22_pks%22%2C%22SKUMultiple%22%5D%2C%224%22%3A%5B%22_pkn%22%2C%22%22%5D%7D)/.test(results)
			|| /(MultipleCategories).*(&cvar=%7B%222%22%3A%5B%22cookiename2PAGE%22%2C%22cookievalue2PAGE%22%5D%2C%223%22%3A%5B%22_pks%22%2C%22SKUMultiple%22%5D%2C%224%22%3A%5B%22_pkn%22%2C%22%22%5D%2C%225%22%3A%5B%22_pkc%22%2C%22%5B%5C%22CATEGORY1%5C%22%2C%5C%22CATEGORY2%5C%22%5D%22%5D%7D)/.test(results), "ecommerce view multiple categories");
			
			// Ecommerce order
			ok( /idgoal=0&ec_id=ORDER%20ID%20YES&revenue=666.66&ec_st=333&ec_tx=222&ec_sh=111&ec_dt=1&ec_items=%5B%5B%22SKU%20PRODUCT%22%2C%22random%22%2C%22random%20PRODUCT%20CATEGORY%22%2C11.1111%2C2%5D%2C%5B%22SKU%20ONLY%20SKU%22%2C%22%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%20NAME%22%2C%22PRODUCT%20NAME%202%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20NO%20PRICE%20NO%20QUANTITY%22%2C%22PRODUCT%20NAME%203%22%2C%22CATEGORY%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%22%2C%22%22%2C%22%22%2C0%2C1%5D%5D/.test( results ), "logEcommerceOrder() with items" );

			// Not set for the first ecommerce order
			ok( ! /idgoal=0&ec_id=ORDER%20ID.*_ects=1/.test(results), "Ecommerce last timestamp set");
			
			// Ecommerce last timestamp set properly for subsequent page view
			ok( /DoTrack.*_ects=1/.test(results), "Ecommerce last timestamp set");

			// Cart update
			ok( /idgoal=0&revenue=555.55&ec_items=%5B%5B%22SKU%20PRODUCT%22%2C%22random%22%2C%22random%20PRODUCT%20CATEGORY%22%2C11.1111%2C2%5D%2C%5B%22SKU%20ONLY%20SKU%22%2C%22%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%20NAME%22%2C%22PRODUCT%20NAME%202%22%2C%22%22%2C0%2C1%5D%2C%5B%22SKU%20NO%20PRICE%20NO%20QUANTITY%22%2C%22PRODUCT%20NAME%203%22%2C%22CATEGORY%22%2C0%2C1%5D%2C%5B%22SKU%20ONLY%22%2C%22%22%2C%22%22%2C0%2C1%5D%5D/.test( results ), "logEcommerceCartUpdate() with items" );
			
			// parameters inserted by plugin hooks
			ok( /testlog/.test( results ), "plugin hook log" );
			ok( /testlink/.test( results ), "plugin hook link" );
			ok( /testgoal/.test( results ), "plugin hook goal" );

			// Testing the Tracking URL append
			ok( /&appended=1&appended2=value/.test( results ), "appendToTrackingUrl(query) function");

			start();
		}, 5000);
	});
	';
}
?>
}

function addEventListener(element, eventType, eventHandler, useCapture) {
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
		addEventListener(document, 'DOMContentLoaded', function ready() {
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
	}
	addEventListener(window, 'load', f, false);
})(PiwikTest);
 </script>

 <div id="jashDiv">
 <a href="#" onclick="javascript:loadJash();" title="Open JavaScript Shell"><img id="title" src="gnome-terminal.png" border="0" width="24" height="24" /></a>
 </div>

</body>
</html>
