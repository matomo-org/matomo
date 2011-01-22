<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
                    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <title>piwik.js: Piwik Unit Tests</title>
 <script type="text/javascript">
function getToken() {
	return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}
<?php
$sqlite = false;
if (file_exists("enable_sqlite")) {
	if (extension_loaded('sqlite')) {
		$sqlite = true;
	}
}

if ($sqlite) {
  echo '
var _paq = _paq || [];
_paq.push(["setSiteId", 1]);
_paq.push(["setTrackerUrl", "piwik.php"]);
_paq.push(["setCustomData", { "token" : getToken() }]);
_paq.push(["trackPageView", "Asynchronous tracker"]);';
}
?>
 </script>
 <script src="../../js/piwik.js" type="text/javascript"></script>
 <script src="piwiktest.js" type="text/javascript"></script>
 <link rel="stylesheet" href="assets/qunit.css" type="text/css" media="screen" />
 <script src="assets/qunit.js" type="text/javascript"></script>
 <script src="jslint/fulljslint.js" type="text/javascript"></script>
 <script type="text/javascript">
<!--
/**
 * Add random number to url to stop IE from caching
 *
 * @example url("data/test.html")
 * @result "data/test.html?10538358428943"
 *
 * @example url("data/test.php?foo=bar")
 * @result "data/test.php?foo=bar&10538358345554"
 */
function url(value) {
        return value + (/\?/.test(value) ? "&" : "?") + new Date().getTime() + "" + parseInt(Math.random()*100000);
}
//-->
 </script>
</head>
<body>
<div style="display:none;"><a href="http://piwik.org/qa">First anchor link</a></div>

 <h1 id="qunit-header">piwik.js: Piwik Unit Tests</h1>
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
    <li><a id="click1" href="javascript:document.getElementById('div1').innerHTML='&lt;iframe src=&quot;http://example.com&quot;&gt;&lt;/iframe&gt;';void(0)" class="clicktest">ignore: implicit (JavaScript href)</a></li>
    <li><a id="click2" href="http://example.org" target="iframe2" class="piwik_ignore clicktest">ignore: explicit</a></li>
    <li><a id="click3" href="example.php" target="iframe3" class="clicktest">ignore: implicit (localhost)</a></li>
    <li><a id="click4" href="http://example.net" target="iframe4" class="clicktest">outlink: implicit (outbound URL)</a></li>
    <li><a id="click5" href="example.html" target="iframe5" class="piwik_link clicktest">outlink: explicit (localhost)</a></li>
    <li><a id="click6" href="example.pdf" target="iframe6" class="clicktest">download: implicit (file extension)</a></li>
    <li><a id="click7" href="example.word" target="iframe7" class="piwik_download clicktest">download: explicit</a></li>
  </ul>
 </div>

 <ol id="qunit-tests"></ol>

 <div id="main" style="display:none;"></div>

 <script>
var hasLoaded = false;
function PiwikTest() {
    hasLoaded = true;

	test("JSLint", function() {
		expect(1);
		var src = '<?php
			$src = file_get_contents('../../js/piwik.js');
			$src = strtr($src, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
			echo $src; ?>';
		ok( JSLINT(src), "JSLint" );
	});

	test("JSON", function() {
		expect(10);

		var tracker = Piwik.getTracker(), dummy;

		equals( JSON.stringify(true), 'true', 'Boolean (true)' );
		equals( JSON.stringify(false), 'false', 'Boolean (false)' );
		equals( JSON.stringify(42), '42', 'Number' );
		equals( JSON.stringify("ABC"), '"ABC"', 'String' );

		var d = new Date();
		d.setTime(1240013340000);
		ok( JSON.stringify(d) == '"2009-04-18T00:09:00Z"'
		|| JSON.stringify(d) == '"2009-04-18T00:09:00.000Z"', 'Date');

		equals( JSON.stringify(null), 'null', 'null' );
		equals( typeof JSON.stringify(dummy), 'undefined', 'undefined' );
		equals( JSON.stringify([1, 2, 3]), '[1,2,3]', 'Array of numbers' );
		equals( JSON.stringify({'key' : 'value'}), '{"key":"value"}', 'Object (members)' );
		equals( JSON.stringify(
			[ {'domains' : ['example.com', 'example.ca']},
			  {'names' : ['Sean', 'Cathy'] } ]
		), '[{"domains":["example.com","example.ca"]},{"names":["Sean","Cathy"]}]', 'Nested members' );
	});

	test("Basic requirements", function() {
		expect(3);

		equals( typeof encodeURIComponent, 'function', 'encodeURIComponent' );
		ok( RegExp, "RegExp" );
		ok( Piwik, "Piwik" );
	});

	module("piwik test");
	test("Test API - addPlugin(), getTracker(), getHook(), and hook", function() {
		expect(6);

		ok( Piwik.addPlugin, "Piwik.addPlugin" );

		var tracker = Piwik.getTracker();

		equals( typeof tracker, 'object', "Piwik.getTracker()" );
		equals( typeof tracker.getHook, 'function', "test Tracker getHook" );
		equals( typeof tracker.hook, 'object', "test Tracker hook" );
		equals( typeof tracker.getHook('test'), 'object', "test Tracker getHook('test')" );
		equals( typeof tracker.hook.test, 'object', "test Tracker hook.test" );
	});

	module("piwik");
	test("Tracker is_a functions", function() {
		expect(22);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._isDefined, 'function', 'isDefined' );
		ok( tracker.hook.test._isDefined(tracker), 'isDefined true' );
		ok( tracker.hook.test._isDefined(tracker.hook), 'isDefined(obj.exists) true' );
		ok( !tracker.hook.test._isDefined(tracker.non_existant_property), 'isDefined(obj.missing) false' );

		equals( typeof tracker.hook.test._isFunction, 'function', 'isFunction' );
		ok( tracker.hook.test._isFunction(tracker.hook.test._isFunction), 'isFunction(isFunction)' );
		ok( tracker.hook.test._isFunction(function () { }), 'isFunction(function)' );

		equals( typeof tracker.hook.test._isObject, 'function', 'isObject' );
		ok( tracker.hook.test._isObject(null), 'isObject(null)' ); // null is an object!
		ok( tracker.hook.test._isObject(new Object), 'isObject(Object)' );
		ok( tracker.hook.test._isObject(window), 'isObject(window)' );
		ok( !tracker.hook.test._isObject('string'), 'isObject("string")' );
		ok( tracker.hook.test._isObject(new String), 'isObject(String)' ); // String is an object!

		equals( typeof tracker.hook.test._isString, 'function', 'isString' );
		ok( tracker.hook.test._isString(''), 'isString(emptyString)' );
		ok( tracker.hook.test._isString('abc'), 'isString("abc")' );
		ok( tracker.hook.test._isString('123'), 'isString("123")' );
		ok( !tracker.hook.test._isString(123), 'isString(123)' );
		ok( !tracker.hook.test._isString(null), 'isString(null)' );
		ok( !tracker.hook.test._isString(window), 'isString(window)' );
		ok( !tracker.hook.test._isString(function () { }), 'isString(function)' );
		ok( tracker.hook.test._isString(new String), 'isString(String)' ); // String is a string
	});

	test("Tracker encode and decode wrappers", function() {
		expect(4);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._encode, 'function', 'encodeWrapper' );
		equals( typeof tracker.hook.test._decode, 'function', 'decodeWrapper' );

		equals( tracker.hook.test._encode("&=?;/#"), '%26%3D%3F%3B%2F%23', 'encodeWrapper()' );
		equals( tracker.hook.test._decode("%26%3D%3F%3B%2F%23"), '&=?;/#', 'decodeWrapper()' );
	});

	test("Tracker getHostName(), getParameter(), urlFixup(), domainFixup(), and purify()", function() {
		expect(40);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._getHostName, 'function', 'getHostName' );
		equals( typeof tracker.hook.test._getParameter, 'function', 'getParameter' );

		equals( tracker.hook.test._getHostName('http://example.com'), 'example.com', 'http://example.com');
		equals( tracker.hook.test._getHostName('http://example.com/'), 'example.com', 'http://example.com/');
		equals( tracker.hook.test._getHostName('http://example.com/index'), 'example.com', 'http://example.com/index');
		equals( tracker.hook.test._getHostName('http://example.com/index?q=xyz'), 'example.com', 'http://example.com/index?q=xyz');
		equals( tracker.hook.test._getHostName('http://example.com/?q=xyz'), 'example.com', 'http://example.com/?q=xyz');
		equals( tracker.hook.test._getHostName('http://example.com/?q=xyz#hash'), 'example.com', 'http://example.com/?q=xyz#hash');
		equals( tracker.hook.test._getHostName('http://example.com#hash'), 'example.com', 'http://example.com#hash');
		equals( tracker.hook.test._getHostName('http://example.com/#hash'), 'example.com', 'http://example.com/#hash');
		equals( tracker.hook.test._getHostName('http://example.com:80'), 'example.com', 'http://example.com:80');
		equals( tracker.hook.test._getHostName('http://example.com:80/'), 'example.com', 'http://example.com:80/');
		equals( tracker.hook.test._getHostName('https://example.com/'), 'example.com', 'https://example.com/');
		equals( tracker.hook.test._getHostName('http://user@example.com/'), 'example.com', 'http://user@example.com/');
		equals( tracker.hook.test._getHostName('http://user:password@example.com/'), 'example.com', 'http://user:password@example.com/');

		equals( tracker.hook.test._getParameter('http://piwik.org/', 'q'), '', 'no query');
		equals( tracker.hook.test._getParameter('http://piwik.org/?q=test', 'q'), 'test', '?q');
		equals( tracker.hook.test._getParameter('http://piwik.org/?p=test1&q=test2', 'q'), 'test2', '&q');
		equals( tracker.hook.test._getParameter('http://piwik.org/?q=http%3a%2f%2flocalhost%2f%3fr%3d1%26q%3dfalse', 'q'), 'http://localhost/?r=1&q=false', 'url');

		equals( typeof tracker.hook.test._urlFixup, 'function', 'urlFixup' );

		same( tracker.hook.test._urlFixup( 'webcache.googleusercontent.com', 'http://webcache.googleusercontent.com/search?q=cache:CD2SncROLs4J:piwik.org/blog/2010/04/piwik-0-6-security-advisory/+piwik+security&cd=1&hl=en&ct=clnk', '' ),
				['piwik.org', 'http://piwik.org/qa', ''], 'webcache.googleusercontent.com' );

		same( tracker.hook.test._urlFixup( 'cc.bingj.com', 'http://cc.bingj.com/cache.aspx?q=web+analytics&d=5020318678516316&mkt=en-CA&setlang=en-CA&w=6ea8ea88,ff6c44df', '' ),
				['piwik.org', 'http://piwik.org/qa', ''], 'cc.bingj.com' );

		same( tracker.hook.test._urlFixup( '74.6.239.185', 'http://74.6.239.185/search/srpcache?ei=UTF-8&p=piwik&fr=yfp-t-964&fp_ip=ca&u=http://cc.bingj.com/cache.aspx?q=piwik&d=4770519086662477&mkt=en-US&setlang=en-US&w=f4bc05d8,8c8af2e3&icp=1&.intl=us&sig=PXmPDNqapxSQ.scsuhIpZA--', '' ),
				['piwik.org', 'http://piwik.org/qa', ''], 'yahoo cache (1)' );

		same( tracker.hook.test._urlFixup( '74.6.239.84', 'http://74.6.239.84/search/srpcache?ei=UTF-8&p=web+analytics&fr=yfp-t-715&u=http://cc.bingj.com/cache.aspx?q=web+analytics&d=5020318680482405&mkt=en-CA&setlang=en-CA&w=a68d7af0,873cfeb0&icp=1&.intl=ca&sig=x6MgjtrDYvsxi8Zk2ZX.tw--', '' ),
				['piwik.org', 'http://piwik.org/qa', ''], 'yahoo cache (2)' );

		same( tracker.hook.test._urlFixup( 'translate.googleusercontent.com', 'http://translate.googleusercontent.com/translate_c?hl=en&ie=UTF-8&sl=en&tl=fr&u=http://piwik.org/&prev=_t&rurl=translate.google.com&twu=1&usg=ALkJrhirI_ijXXT7Ja_aDGndEJbE7pJqpQ', '' ),
				['piwik.org', 'http://piwik.org/', 'http://translate.googleusercontent.com/translate_c?hl=en&ie=UTF-8&sl=en&tl=fr&u=http://piwik.org/&prev=_t&rurl=translate.google.com&twu=1&usg=ALkJrhirI_ijXXT7Ja_aDGndEJbE7pJqpQ'], 'translate.googleusercontent.com' );

		equals( typeof tracker.hook.test._domainFixup, 'function', 'domainFixup' );

		same( tracker.hook.test._domainFixup( 'localhost' ), 'localhost', 'domainFixup: localhost' );
		same( tracker.hook.test._domainFixup( 'localhost.' ), 'localhost', 'domainFixup: localhost.' );
		same( tracker.hook.test._domainFixup( 'localhost.localdomain' ), 'localhost.localdomain', 'domainFixup: localhost.localdomain' );
		same( tracker.hook.test._domainFixup( 'localhost.localdomain.' ), 'localhost.localdomain', 'domainFixup: localhost.localdomain.' );
		same( tracker.hook.test._domainFixup( '127.0.0.1' ), '127.0.0.1', 'domainFixup: 127.0.0.1' );
		same( tracker.hook.test._domainFixup( 'www.example.com' ), 'www.example.com', 'domainFixup: www.example.com' );
		same( tracker.hook.test._domainFixup( 'www.example.com.' ), 'www.example.com', 'domainFixup: www.example.com.' );

		equals( typeof tracker.hook.test._purify, 'function', 'purify' );

		equals( tracker.hook.test._purify('http://example.com'), 'http://example.com', 'http://example.com');
		equals( tracker.hook.test._purify('http://example.com#hash'), 'http://example.com#hash', 'http://example.com#hash');
		equals( tracker.hook.test._purify('http://example.com/?q=xyz#hash'), 'http://example.com/?q=xyz#hash', 'http://example.com/?q=xyz#hash');

		tracker.discardHashTag(true);

		equals( tracker.hook.test._purify('http://example.com'), 'http://example.com', 'http://example.com');
		equals( tracker.hook.test._purify('http://example.com#hash'), 'http://example.com', 'http://example.com#hash');
		equals( tracker.hook.test._purify('http://example.com/?q=xyz#hash'), 'http://example.com/?q=xyz', 'http://example.com/?q=xyz#hash');
	});

	test("Tracker setDomains() and isSiteHostName()", function() {
		expect(9);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._isSiteHostName, 'function', "isSiteHostName" );

		// test wildcards
		tracker.setDomains( ['*.Example.com'] );

		// skip test if testing on localhost
		ok( window.location.hostname != 'localhost' ? !tracker.hook.test._isSiteHostName('localhost') : true, '!isSiteHostName("localhost")' );

		ok( !tracker.hook.test._isSiteHostName('google.com'), '!isSiteHostName("google.com")' );
		ok( tracker.hook.test._isSiteHostName('example.com'), 'isSiteHostName("example.com")' );
		ok( tracker.hook.test._isSiteHostName('www.example.com'), 'isSiteHostName("www.example.com")' );
		ok( tracker.hook.test._isSiteHostName('www.sub.example.com'), 'isSiteHostName("www.sub.example.com")' );

		tracker.setDomains( 'dev.piwik.org' );
		ok( !tracker.hook.test._isSiteHostName('piwik.org'), '!isSiteHostName("dev.piwik.org")' );
		ok( tracker.hook.test._isSiteHostName('dev.piwik.org'), 'isSiteHostName("dev.piwik.org")' );
		ok( !tracker.hook.test._isSiteHostName('piwik.example.org'), '!isSiteHostName("piwik.example.org")');
	});

	test("Tracker getClassesRegExp()", function() {
		expect(0);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._getClassesRegExp, 'function', "getClassesRegExp" );

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
		    expectedValue = Math.random();
		tracker.hook.test._setCookie( cookieName, expectedValue );
		equals( tracker.hook.test._getCookie( cookieName ), expectedValue, 'getCookie(), setCookie()' );
	});

	test("Tracker setDownloadExtensions(), addDownloadExtensions(), setDownloadClasses(), setLinkClasses(), and getLinkType()", function() {
		expect(23);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._getLinkType, 'function', 'getLinkType' );

		equals( tracker.hook.test._getLinkType('something', 'goofy.html', false), 'link', 'implicit link' );
		equals( tracker.hook.test._getLinkType('something', 'goofy.pdf', false), 'link', 'implicit link' );

		equals( tracker.hook.test._getLinkType('piwik_download', 'piwiktest.ext', true), 'download', 'piwik_download' );
		equals( tracker.hook.test._getLinkType('abc piwik_download xyz', 'piwiktest.ext', true), 'download', 'abc piwik_download xyz' );
		equals( tracker.hook.test._getLinkType('piwik_link', 'piwiktest.asp', true), 'link', 'piwik_link' );
		equals( tracker.hook.test._getLinkType('abc piwik_link xyz', 'piwiktest.asp', true), 'link', 'abc piwik_link xyz' );
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true), 'download', 'download extension' );
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.ext', true), 0, '[1] link (default)' );

		equals( tracker.hook.test._getLinkType('something', 'file.zip', true), 'download', 'download file.zip' );
		equals( tracker.hook.test._getLinkType('something', 'index.php?name=file.zip#anchor', true), 'download', 'download file.zip (anchor)' );
		equals( tracker.hook.test._getLinkType('something', 'index.php?name=file.zip&redirect=yes', true), 'download', 'download file.zip (is param)' );
		equals( tracker.hook.test._getLinkType('something', 'file.zip?mirror=true', true), 'download', 'download file.zip (with param)' );

		tracker.setDownloadExtensions('pk');
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true), 'download', '[1] .pk == download extension' );
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true), 0, '.txt =! download extension' );

		tracker.addDownloadExtensions('xyz');
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true), 'download', '[2] .pk == download extension' );
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.xyz', true), 'download', '.xyz == download extension' );

		tracker.setDownloadClasses(['a', 'b']);
		equals( tracker.hook.test._getLinkType('abc piwik_download', 'piwiktest.ext', true), 'download', 'download (default)' );
		equals( tracker.hook.test._getLinkType('abc a', 'piwiktest.ext', true), 'download', 'download (a)' );
		equals( tracker.hook.test._getLinkType('b abc', 'piwiktest.ext', true), 'download', 'download (b)' );

		tracker.setLinkClasses(['c', 'd']);
		equals( tracker.hook.test._getLinkType('abc piwik_link', 'piwiktest.ext', true), 'link', 'link (default)' );
		equals( tracker.hook.test._getLinkType('abc c', 'piwiktest.ext', true), 'link', 'link (c)' );
		equals( tracker.hook.test._getLinkType('d abc', 'piwiktest.ext', true), 'link', 'link (d)' );
	});

	test("utf8_encode(), sha1()", function() {
		expect(6);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._utf8_encode, 'function', 'utf8_encode' );
		equals( tracker.hook.test._utf8_encode('hello world'), '<?php echo utf8_encode("hello world"); ?>', 'utf8_encode("hello world")' );
		equals( tracker.hook.test._utf8_encode('Gesamtgröße'), '<?php echo utf8_encode("Gesamtgröße"); ?>', 'utf8_encode("Gesamtgröße")' );
		equals( tracker.hook.test._utf8_encode('您好'), '<?php echo utf8_encode("您好"); ?>', 'utf8_encode("您好")' );

		equals( typeof tracker.hook.test._sha1, 'function', 'sha1' );
		equals( tracker.hook.test._sha1('hello world'), '<?php echo sha1("hello world"); ?>', 'sha1("hello world")' );
	});

	test("Tracking", function() {
		expect(<?php echo $sqlite ? 20 : 6; ?>);

		var tracker = Piwik.getTracker();

		ok( ! ( _paq instanceof Array ), "async tracker proxy not an array" );
		equals( typeof tracker, typeof _paq, "async tracker proxy" );

		var startTime, stopTime;

		equals( typeof tracker.setReferrerUrl, 'function', 'setReferrerUrl' );

		equals( typeof tracker.hook.test._beforeUnloadHandler, 'function', 'beforeUnloadHandler' );

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
<?php
if ($sqlite) {
	echo '
		tracker.setTrackerUrl("piwik.php");
		tracker.setSiteId(1);
		tracker.setCustomData({ "token" : getToken() });
		tracker.setDocumentTitle("PiwikTest");
		tracker.setReferrerUrl("http://referrer.example.com");

		tracker.enableLinkTracking();

		tracker.trackPageView();

		tracker.trackPageView("CustomTitleTest");

		tracker.trackLink("http://example.ca", "link", { "token" : getToken() });

		// async tracker proxy
		_paq.push(["trackLink", "http://example.fr/async.zip", "download",  { "token" : getToken() }]);

		// push function
		_paq.push([ function(t) {
			tracker.trackLink("http://example.de", "link", { "token" : t });
		}, getToken() ]);

		tracker.setRequestMethod("POST");
		tracker.trackGoal(42, 69, { "token" : getToken(), "boy" : "Michael", "girl" : "Mandy"});

		tracker.setRequestMethod("GET");
		var buttons = new Array("click1", "click2", "click3", "click4", "click5", "click6", "click7");
		for (var i=0; i < buttons.length; i++) {
			QUnit.triggerEvent( document.getElementById(buttons[i]), "click" );
		}

		var xhr = window.XMLHttpRequest ? new window.XMLHttpRequest() :
			window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") :
			null;

		stop();
		setTimeout(function() {
			xhr.open("GET", "piwik.php?results=" + getToken(), false);
			xhr.send(null);
			results = xhr.responseText;

			ok( /\<span\>11\<\/span\>/.test( results ), "count tracking events" );
			ok( /PiwikTest/.test( results ), "trackPageView()" );
			ok( /Asynchronous/.test( results ), "async trackPageView()" );
			ok( /CustomTitleTest/.test( results ), "trackPageView(customTitle)" );
			ok( /example.ca/.test( results ), "trackLink()" );
			ok( /example.fr/.test( results ), "async trackLink()" );
			ok( /example.de/.test( results ), "push function" );
			ok( /example.net/.test( results ), "click: implicit outlink (by outbound URL)" );
			ok( /example.html/.test( results ), "click: explicit outlink" );
			ok( /example.pdf/.test( results ), "click: implicit download (by file extension)" );
			ok( /example.word/.test( results ), "click: explicit download" );
			ok( ! /example.(org|php)/.test( results ), "click: ignored" );
			ok( /Michael.*?Mandy.*?idgoal=42.*?revenue=69/.test( results ), "trackGoal()" );
			ok( /referrer.example.com/.test( results ), "setReferrerUrl()" );

			start();
		}, 3000);
		';
}
?>
	});
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

</body>
</html>
