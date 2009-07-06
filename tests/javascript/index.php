<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
                    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <title>piwik.js: Piwik Unit Tests</title>
 <script src="../../js/piwik.js" type="text/javascript"></script>
 <script src="piwiktest.js" type="text/javascript"></script>
 <script src="../../libs/jquery/jquery.js" type="text/javascript"></script>
 <link rel="stylesheet" href="assets/testsuite.css" type="text/css" media="screen" />
</head>
<body>
<?php
$sqlite = false;
if (file_exists("enable_sqlite")) {
	if (extension_loaded('sqlite')) {
		$sqlite = true;
	}
}
?>


 <h1>piwik.js: Piwik Unit Tests</h1>
 <h2 id="banner"></h2>
 <h2 id="userAgent"></h2>

 <div class="hidden">
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

 <ol id="tests"></ol>

 <div id="main"></div>

 <script src="assets/testrunner.js" type="text/javascript"></script>

 <script>
function getToken() {
	return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
}

$(document).ready(function () {

	test("Basic requirements", function() {
		expect(5);

		equals( typeof encodeURIComponent, 'function', 'encodeURIComponent' );
		ok( RegExp, "RegExp" );
		ok( Piwik, "Piwik" );
		ok( piwik_log, "piwik_log" );
		equals( typeof piwik_track, 'undefined', "piwk_track" );
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
	test("Tracker escape and unescape wrappers", function() {
		expect(4);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._escape, 'function', 'escapeWrapper' );
		equals( typeof tracker.hook.test._unescape, 'function', 'unescapeWrapper' );

		equals( tracker.hook.test._escape("&=?;/#"), '%26%3D%3F%3B%2F%23', 'escapeWrapper()' );
		equals( tracker.hook.test._unescape("%26%3D%3F%3B%2F%23"), '&=?;/#', 'unescapeWrapper()' );
	});

	test("Tracker setDomains() and isSiteHostName()", function() {
		expect(9);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._isSiteHostName, 'function', "isSiteHostName" );

		// test wildcards
		tracker.setDomains( ['*.example.com'] );
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

	test("Tracker setIgnoreClasses() and getIgnoreRegExp", function() {
		expect(15);

		var tracker = Piwik.getTracker();

		equals( typeof tracker.hook.test._getIgnoreRegExp, 'function', "getIgnoreRegExp" );

		var ignore = tracker.hook.test._getIgnoreRegExp();
		ok( ignore.test('piwik_ignore'), '[1] piwik_ignore' );
		ok( !ignore.test('pk_ignore'), '[1] !pk_ignore' );
		ok( !ignore.test('apiwik_ignore'), '!apiwik_ignore' );
		ok( !ignore.test('piwik_ignorez'), '!piwik_ignorez' );
		ok( ignore.test('abc piwik_ignore xyz'), 'abc piwik_ignore xyz' );

		tracker.setIgnoreClasses( 'my_download' );
		ignore = tracker.hook.test._getIgnoreRegExp();
		ok( ignore.test('piwik_ignore'), '[2] piwik_ignore' );
		ok( !ignore.test('pk_ignore'), '[2] !pk_ignore' );
		ok( ignore.test('my_download'), 'my_download' );
		ok( ignore.test('abc piwik_ignore xyz'), 'abc piwik_ignore xyz' );
		ok( ignore.test('abc my_download xyz'), 'abc my_download xyz' );

		tracker.setIgnoreClasses( ['my_download', 'my_outlink'] );
		ignore = tracker.hook.test._getIgnoreRegExp();
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

	test("Tracker setDownloadExtensions(), addDownloadExtensions(), setDownloadClass(), setLinkClass(), and getLinkType()", function() {
		expect(19);

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

		tracker.setDownloadExtensions('pk');
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true), 'download', '[1] .pk == download extension' );
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.txt', true), 0, '.txt =! download extension' );

		tracker.addDownloadExtensions('xyz');
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.pk', true), 'download', '[2] .pk == download extension' );
		equals( tracker.hook.test._getLinkType('something', 'piwiktest.xyz', true), 'download', '.xyz == download extension' );

		tracker.setDownloadClass('my_download');
		equals( tracker.hook.test._getLinkType('my_download', 'piwiktest.ext', true), 'download', 'my_download' );
		equals( tracker.hook.test._getLinkType('abc my_download xyz', 'piwiktest.ext', true), 'download', 'abc my_download xyz' );
		equals( tracker.hook.test._getLinkType('piwik_download', 'piwiktest.ext', true), 0, 'piwik_download != my_download' );

		tracker.setLinkClass('my_link');
		equals( tracker.hook.test._getLinkType('my_link', 'piwiktest.ext', true), 'link', 'my_link' );
		equals( tracker.hook.test._getLinkType('abc my_link xyz', 'piwiktest.ext', true), 'link', 'abc my_link xyz' );
		equals( tracker.hook.test._getLinkType('piwik_link', 'piwiktest.ext', true), 0, '[2] link default' );
	});

	test("JSON", function() {
		expect(10);

		var tracker = Piwik.getTracker(), dummy;

		equals( tracker.hook.test._stringify(true), 'true', 'Boolean (true)' );
		equals( tracker.hook.test._stringify(false), 'false', 'Boolean (false)' );
		equals( tracker.hook.test._stringify(42), '42', 'Number' );
		equals( tracker.hook.test._stringify("ABC"), '"ABC"', 'String' );

		var d = new Date();
		d.setTime(1240013340000);
		equals( tracker.hook.test._stringify(d), '"2009-04-18T00:09:00Z"', 'Date');

		equals( tracker.hook.test._stringify(null), 'null', 'null' );
		equals( typeof tracker.hook.test._stringify(dummy), 'undefined', 'undefined' );
		equals( tracker.hook.test._stringify([1, 2, 3]), '[1,2,3]', 'Array of numbers' );
		equals( tracker.hook.test._stringify({'key' : 'value'}), '{"key":"value"}', 'Object (members)' );
		equals( tracker.hook.test._stringify(
			[ {'domains' : ['example.com', 'example.ca']},
			  {'names' : ['Sean', 'Cathy'] } ]
		), '[{"domains":["example.com","example.ca"]},{"names":["Sean","Cathy"]}]', 'Nested members' );
	});

	test("Tracking", function() {
		expect(<?php echo $sqlite ? 11 : 3; ?>);

		var tracker = Piwik.getTracker();

		var startTime, stopTime;

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
		tracker.setCustomData({ "token" : "'. $token .'" });
		tracker.setDocumentTitle("PiwikTest");

		tracker.enableLinkTracking();

		tracker.trackPageView();

		tracker.trackLink("http://example.ca", "link", { "token" : "'. $token .'" });

		var buttons = q("click1", "click2", "click3", "click4", "click5", "click6", "click7");
		for (var i=0; i < buttons.length; i++) {
			triggerEvent( buttons[i], "click" );
		}

		tracker.trackGoal(42, 69, { "boy" : "Michael", "girl" : "Mandy" });

		stop();
		setTimeout(function() {
			jQuery.ajax({
				url: url("piwik.php?results='. $token .'"),
				success: function(results) {
//alert(results);
					ok( /\<span\>7\<\/span\>/.test( results ), "count tracking events" );
					ok( /PiwikTest/.test( results ), "trackPageView()" );
					ok( /example.ca/.test( results ), "trackLink()" );
					ok( /example.net/.test( results ), "click: implicit outlink (by outbound URL)" );
					ok( /example.html/.test( results ), "click: explicit outlink" );
					ok( /example.pdf/.test( results ), "click: implicit download (by file extension)" );
					ok( /example.word/.test( results ), "click: explicit download" );
					ok( /idgoal=42.*?revenue=69.*?Michael.*?Mandy/.test( results ), "trackGoal()" );

					start();
				}
			});
		}, 2000);
		';
}
?>
	});
});
 </script>

</body>
</html>

