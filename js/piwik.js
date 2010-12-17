/*!
 * Piwik - Web Analytics
 *
 * JavaScript tracking client
 *
 * @link http://piwik.org
 * @source http://dev.piwik.org/trac/browser/trunk/js/piwik.js
 * @license http://www.opensource.org/licenses/bsd-license.php Simplified BSD
 */

// Refer to README for build instructions when minifying this file for distribution.

/*jslint browser:true, forin:true, plusplus:false, onevar:false, eqeqeq:false, strict:false */
/*global window escape unescape ActiveXObject _paq:true */
/*jslint evil:true */

/*
 * Browser [In]Compatibility
 *
 * This version of piwik.js is known to not work with:
 * - IE4 - try..catch and for..in introduced in IE5
 * - IE5 - named anonymous functions and array.push introduced in IE5.5
 */

var
	// asynchronous tracker (or proxy)
	_paq = _paq || [],

	// Piwik singleton and namespace
	Piwik =	Piwik || (function() {
		/************************************************************
		 * Private data
		 ************************************************************/

		var expireDateTime,

		/* plugins */
		plugins = {},

		/* alias frequently used globals for added minification */
		documentAlias = document,
		navigatorAlias = navigator,
		screenAlias = screen,
		windowAlias = window,

		/* DOM Ready */
		hasLoaded = false,
		registeredOnLoadHandlers = [],

		/*
		 * encode or escape
		 * - encodeURIComponent added in IE5.5
		 */
		escapeWrapper = windowAlias.encodeURIComponent || escape,

		/*
		 * decode or unescape
		 * - decodeURIComponent added in IE5.5
		 */
		unescapeWrapper = windowAlias.decodeURIComponent || unescape,

		/* asynchronous tracker */
		asyncTracker;

		/************************************************************
		 * Private methods
		 ************************************************************/

		/*
		 * Is property (or variable) defined?
		 */
		function isDefined(property) {
			return (property = typeof property) !== 'undefined' && property !== 'unknown';
		}

		/*
		 * apply wrapper
		 *
		 * @param array parameterArray An array comprising either:
		 *      [ 'methodName', optional_parameters ]
		 * or:
		 *      [ functionObject, optional_parameters ] 
		 */
		function apply(parameterArray) {
			var f = parameterArray.shift();

			if (typeof f == 'string') {
				asyncTracker[f].apply(this, parameterArray);
			} else {
				f.apply(this, parameterArray);
			}
		}

		/*
		 * Cross-browser helper function to add event handler
		 */
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

		/*
		 * Call plugin hook methods
		 */
		function executePluginMethod(methodName, callback) {
			var result = '', i, pluginMethod;

			for (i in plugins) {
				pluginMethod = plugins[i][methodName];
				if (typeof pluginMethod === 'function') {
					result += pluginMethod(callback);
				}
			}

			return result;
		}

		/*
		 * Handle beforeunload event
		 */
		function beforeUnloadHandler(unloadEvent /* not used */) {
			executePluginMethod('unload');

			/*
			 * Delay/pause (blocks UI)
			 */
			if (isDefined(expireDateTime)) {
				var now;

				do {
					now = new Date();
				} while (now.getTime() < expireDateTime);
			}
		}

		/*
		 * Handler for onload event
		 */
		function loadHandler(loadEvent /* unused */) {
			if (!hasLoaded) {
				hasLoaded = true;
				executePluginMethod('load');
				for (var i = 0; i < registeredOnLoadHandlers.length; i++) {
					registeredOnLoadHandlers[i]();
				}
			}
			return true;
		}

		/*
		 * Add onload or DOM ready handler
		 */
		function addReadyListener() {
			if (documentAlias.addEventListener) {
				addEventListener(documentAlias, 'DOMContentLoaded', function ready() {
                        documentAlias.removeEventListener('DOMContentLoaded', ready, false);
						loadHandler();
					});
			} else if (documentAlias.attachEvent) {
				documentAlias.attachEvent('onreadystatechange', function ready() {
					if (documentAlias.readyState === 'complete') {
						documentAlias.detachEvent('onreadystatechange', ready);
						loadHandler();
					}
				});

				if (documentAlias.documentElement.doScroll && windowAlias == windowAlias.top) {
					(function ready() {
						if (!hasLoaded) {
							try {
								documentAlias.documentElement.doScroll('left');
							} catch (error) {
								setTimeout(ready, 0);
								return;
							}
							loadHandler();
						}
					}());
				}
			}
			// fallback
			addEventListener(windowAlias, 'load', loadHandler, false);
		}

		/*
		 * Get page referrer
		 */
		function getReferrer() {
			var referrer = '';
			try {
				referrer = top.document.referrer;
			} catch (e) {
				if (parent) {
					try {
						referrer = parent.document.referrer;
					} catch (e2) {
						referrer = '';
					}
				}
			}
			if (referrer === '') {
				referrer = documentAlias.referrer;
			}

			return referrer;
		}

		/*
		 * Extract hostname from URL
		 */
		function getHostname(url) {
			// scheme : // [username [: password] @] hostame [: port] [/ [path] [? query] [# fragment]]
			var e = new RegExp('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)'),
				matches = e.exec(url);
			return matches ? matches[1] : url;
		}

		/*
		 * Extract parameter from URL
		 */
		function getParameter(url, varName) {
			// scheme : // [username [: password] @] hostame [: port] [/ [path] [? query] [# fragment]]
			var e = new RegExp('^(?:https?|ftp)(?::/*(?:[^?]+)[?])([^#]+)'),
				matches = e.exec(url),
				f = new RegExp('(?:^|&)'+varName+'=([^&]*)'),
				result = matches ? f.exec(matches[1]) : 0;
				return result ? unescapeWrapper(result[1]) : '';
		}

		/*
		 * Fix-up URL when page rendered from search engine cache or translated page
		 */
		function urlFixup(hostname, href, referrer) {
			if (hostname == 'webcache.googleusercontent.com' || // Google
					hostname == 'cc.bingj.com' ||				// Bing
					hostname.substr(0, 5) == '74.6.')			// Yahoo (via Inktomi 74.6.0.0/16)
			{
				href = documentAlias.links[0].href;
				hostname = getHostname(href);
			}
			else if(hostname == 'translate.googleusercontent.com')	// Google
			{
				if (referrer === '') {
					referrer = href;
				}
				href = getParameter(href, 'u');
				hostname = getHostname(href);
			}
			return [hostname, href, referrer];
		}

		/*
		 * Piwik Tracker class
		 *
		 * trackerUrl and trackerSiteId are optional arguments to the constructor
		 *
		 * See: Tracker.setTrackerUrl() and Tracker.setSiteId()
		 */
		function Tracker(trackerUrl, siteId) {

			/************************************************************
			 * Private members
			 ************************************************************/

			var
/*<DEBUG>*/
			/*
			 * registered test hooks
			 */
			registeredHooks = {},
/*</DEBUG>*/

			// Current URL and Referrer URL
			locationArray = urlFixup(windowAlias.location.hostname, windowAlias.location.href, getReferrer()),
			locationHostnameAlias = locationArray[0],
			locationHrefAlias = locationArray[1],
			configReferrerUrl = locationArray[2],

			// Request method (GET or POST)
			configRequestMethod = 'GET',

			// Tracker URL
			configTrackerUrl = trackerUrl || '',

			// Site ID
			configTrackerSiteId = siteId || '',

			// Document URL
			configCustomUrl,

			// Document title
			configTitle = documentAlias.title,

			// Extensions to be treated as download links
			configDownloadExtensions = '7z|aac|ar[cj]|as[fx]|avi|bin|csv|deb|dmg|doc|exe|flv|gif|gz|gzip|hqx|jar|jpe?g|js|mp(2|3|4|e?g)|mov(ie)?|ms[ip]|od[bfgpst]|og[gv]|pdf|phps|png|ppt|qtm?|ra[mr]?|rpm|sea|sit|tar|t?bz2?|tgz|torrent|txt|wav|wm[av]|wpd||xls|xml|z|zip',

			// Hosts or alias(es) to not treat as outlinks
			configHostsAlias = [locationHostnameAlias],

			// Anchor classes to not track
			configIgnoreClasses = [],

			// Download class name
			configDownloadClasses = [],

			// (Out) Link class name
			configLinkClasses = [],

			// Maximum delay to wait for web bug image to be fetched (in milliseconds)
			configTrackerPause = 500,

			// Custom data
			configCustomData,

			// Client-side data collection
			browserHasCookies = '0',

			// Plugin, Parameter name, MIME type, detected
			pluginMap = {
				// document types
				pdf:         ['pdf',   'application/pdf',               '0'],
				// media players
				quicktime:   ['qt',    'video/quicktime',               '0'],
				realplayer:  ['realp', 'audio/x-pn-realaudio-plugin',   '0'],
				wma:         ['wma',   'application/x-mplayer2',        '0'],
				// interactive multimedia 
				director:    ['dir',   'application/x-director',        '0'],
				flash:       ['fla',   'application/x-shockwave-flash', '0'],
				// RIA
				java:        ['java',  'application/x-java-vm',         '0'],
				gears:       ['gears', 'application/x-googlegears',     '0'],
				silverlight: ['ag',    'application/x-silverlight',     '0']
			},

			// Guard against installing the link tracker more than once per Tracker instance
			linkTrackingInstalled = false,

			/*
			 * stringify
			 * - based on public domain JSON implementation at http://www.json.org/json2.js (2009-04-16)
			 */
			stringify = function (value) {

				var escapable = new RegExp('[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]', 'g'),
					// table of character substitutions
					meta = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};

				// If the string contains no control characters, no quote characters, and no
				// backslash characters, then we can safely slap some quotes around it.
				// Otherwise we must also replace the offending characters with safe escape
				// sequences.
				function quote(string) {
					escapable.lastIndex = 0;
					return escapable.test(string) ?
						'"' + string.replace(escapable, function (a) {
							var c = meta[a];
							return typeof c === 'string' ? c :
								'\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
						}) + '"' :
						'"' + string + '"';
				}

				function f(n) {
					return n < 10 ? '0' + n : n;
				}

				// Produce a string from holder[key].
				function str(key, holder) {
					var i,          // The loop counter.
						k,          // The member key.
						v,          // The member value.
						partial,
						value = holder[key];

					if (value === null) {
						return 'null';
					}

					// If the value has a toJSON method, call it to obtain a replacement value.
					if (value && typeof value === 'object' && typeof value.toJSON === 'function') {
						value = value.toJSON(key);
					}

					// What happens next depends on the value's type.
					switch (typeof value) {
					case 'string':
						return quote(value);

					case 'number':
						// JSON numbers must be finite. Encode non-finite numbers as null.
						return isFinite(value) ? String(value) : 'null';

					case 'boolean':
					case 'null':
						// If the value is a boolean or null, convert it to a string. Note:
						// typeof null does not produce 'null'. The case is included here in
						// the remote chance that this gets fixed someday.
						return String(value);

					case 'object':
						// Make an array to hold the partial results of stringifying this object value.
						partial = [];

						// Is the value an array?
						// if (Object.prototype.toString.call(value)=="[object Array]") {	// call added in IE5.5
						if (value instanceof Array) {
							// The value is an array. Stringify every element. Use null as a placeholder
							// for non-JSON values.
							for (i = 0; i < value.length; i++) {
								partial[i] = str(i, value) || 'null';
							}

							// Join all of the elements together, separated with commas, and wrap them in
							// brackets.
							v = partial.length === 0 ? '[]' : '[' + partial.join(',') + ']';
							return v;
						}

						// if (Object.prototype.toString.call(value)=="[object Date]") {	// call added in IE5.5
						if (value instanceof Date) {
							return quote(value.getUTCFullYear()   + '-' +
								f(value.getUTCMonth() + 1) + '-' +
								f(value.getUTCDate())      + 'T' +
								f(value.getUTCHours())     + ':' +
								f(value.getUTCMinutes())   + ':' +
								f(value.getUTCSeconds())   + 'Z');
						}

						// Otherwise, iterate through all of the keys in the object.
						for (k in value) {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + ':' + v);
							}
						}

						// Join all of the member texts together, separated with commas,
						// and wrap them in braces.
						v = partial.length === 0 ? '{}' : '{' + partial.join(',') + '}';
						return v;
					}
				}

				return str('', {'': value});
			};

			/*
			 * Set cookie value
			 */
			function setCookie(cookieName, value, daysToExpire, path, domain, secure) {
				var expiryDate;

				if (daysToExpire) {
					// time is in milliseconds
					expiryDate = new Date();
					// there are 1000 * 60 * 60 * 24 milliseconds in a day (i.e., 86400000 or 8.64e7)
					expiryDate.setTime(expiryDate.getTime() + daysToExpire * 8.64e7);
				}

				documentAlias.cookie = cookieName + '=' + escapeWrapper(value) +
					(daysToExpire ? ';expires=' + expiryDate.toGMTString() : '') +
					';path=' + (path ? path : '/') +
					(domain ? ';domain=' + domain : '') +
					(secure ? ';secure' : '');
			}

			/*
			 * Get cookie value
			 */
			function getCookie(cookieName) {
				var cookiePattern = new RegExp('(^|;)[ ]*' + cookieName + '=([^;]*)'),

					cookieMatch = cookiePattern.exec(documentAlias.cookie);

				return cookieMatch ? unescapeWrapper(cookieMatch[2]) : 0;
			}

			/*
			 * Discard cookie
			 */
/*			// NOT CURRENTLY USED
			function dropCookie(cookieName, path, domain) {
				// browser may not delete cookie until browser closed (session ends)
				if (getCookie(cookieName)) {
					// clear value, set expires in the past
					setCookie(cookieName, '', -1, path, domain);
				}
			}
*/

			/*
			 * Send image request to Piwik server using GET.
			 * The infamous web bug is a transparent, single pixel (1x1) image
			 */
			function getImage(request) {
				var image = new Image(1, 1);
				image.onLoad = function () { };
				image.src = configTrackerUrl + '?' + request;
			}

			/*
			 * POST request to Piwik server using XMLHttpRequest.
			 */
			function sendXmlHttpRequest(request) {
				try {
					// we use the progid Microsoft.XMLHTTP because
					// IE5.5 included MSXML 2.5; the progid MSXML2.XMLHTTP
					// is pinned to MSXML2.XMLHTTP.3.0
					var xhr = windowAlias.XMLHttpRequest ? new windowAlias.XMLHttpRequest() :
						windowAlias.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') :
						null;
					xhr.open('POST', configTrackerUrl, true);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
					xhr.setRequestHeader('Content-Length', request.length);
					xhr.setRequestHeader('Connection', 'close');
					xhr.send(request);
				} catch(e) {
					// fallback
					getImage(request);
				}
			}

			/*
			 * Send request
			 */
			function sendRequest(request, delay)
			{
				var now = new Date();
				expireDateTime = now.getTime() + delay;

				if (configRequestMethod == 'POST') {
					sendXmlHttpRequest(request);
				} else {
					getImage(request);
				}
			}

			/*
			 * Browser plugin tests
			 */
			function detectBrowserPlugins() {
				var i, mimeType;

				// Safari and Opera
				// IE6: typeof navigator.javaEnabled == 'unknown'
				if (isDefined(navigatorAlias.javaEnabled) && navigatorAlias.javaEnabled()) {
					pluginMap.java[2] = '1';
				}

				// Firefox
				if (typeof windowAlias.GearsFactory === 'function') {
					pluginMap.gears[2] = '1';
				}

				if (navigatorAlias.mimeTypes && navigatorAlias.mimeTypes.length) {
					for (i in pluginMap) {
						mimeType = navigatorAlias.mimeTypes[pluginMap[i][1]];
						if (mimeType && mimeType.enabledPlugin) {
							pluginMap[i][2] = '1';
						}
					}
				}
			}

			/*
			 * Does browser have cookies enabled (for this site)?
			 */
			function hasCookies() {
				var testCookieName = '_pk_testcookie';
				if (!isDefined(navigatorAlias.cookieEnabled)) {
					setCookie(testCookieName, '1');
					return getCookie(testCookieName) == '1' ? '1' : '0';
				}

				return navigatorAlias.cookieEnabled ? '1' : '0';
			}

			/*
			 * Returns the URL to call piwik.php, 
			 * with the standard parameters (plugins, resolution, url, referer, etc.).
			 * Sends the pageview and browser settings with every request in case of race conditions.
			 */
			function getRequest(customData, pluginMethod) {
				var i,
				now = new Date(),
				request = 'idsite=' + configTrackerSiteId +
					'&rec=1' + 
					'&rand=' + Math.random() +
					'&h=' + now.getHours() + '&m=' + now.getMinutes() + '&s=' + now.getSeconds() +

					'&url=' + escapeWrapper(isDefined(configCustomUrl) ? configCustomUrl : locationHrefAlias) +
					'&urlref=' + escapeWrapper(configReferrerUrl) +
					'&res=' + screenAlias.width + 'x' + screenAlias.height +
					'&cookie=' + browserHasCookies;

				// browser plugin data
				for (i in pluginMap) {
					request += '&' + pluginMap[i][0] + '=' + pluginMap[i][2];
				}

				// custom data
				if (isDefined(customData)) {
					if (customData !== null) {
						request += '&data=' + escapeWrapper(stringify(customData));
					}
				} else if (isDefined(configCustomData)) {
					request += '&data=' + escapeWrapper(stringify(configCustomData));
				}

				// tracker plugin hook
				request += executePluginMethod(pluginMethod);

				return request;
			}

			/*
			 * Log the page view / visit
			 */
			function logPageView(customTitle, customData) {
				var request = getRequest(customData, 'log') +
					'&action_name=' + escapeWrapper(isDefined(customTitle) ? customTitle : configTitle); // refs #530;

				sendRequest(request, configTrackerPause);
			}
			
			/*
			 * Log the goal with the server
			 */
			function logGoal(idGoal, customRevenue, customData) {
				var request = getRequest(customData, 'goal') +
					'&idgoal=' + idGoal;

				// custom revenue
				if (isDefined(customRevenue) && customRevenue !== null) {
					request += '&revenue=' + customRevenue;
				}

				sendRequest(request, configTrackerPause);
			}
			
			/*
			 * Log the link or click  with the server
			 */
			function logLink(url, linkType, customData) {
				var request = getRequest(customData, 'click') +
					'&' + linkType + '=' + escapeWrapper(url) +
					'&redirect=0';

				sendRequest(request, configTrackerPause);
			}

			/*
			 * Is the host local?  (i.e., not an outlink)
			 */
			function isSiteHostName(hostName) {
				var i, alias, offset;

				for (i = 0; i < configHostsAlias.length; i++) {
					alias = configHostsAlias[i].toLowerCase();

					if (hostName == alias) {
						return true;
					}

					if (alias.substr(0, 2) == '*.') {
						if ((hostName) == alias.substr(2)) {
							return true;
						}

						offset = hostName.length - alias.length + 1;
						if ((offset > 0) && (hostName.substr(offset) == alias.substr(1))) {
							return true;
						}
					}
				}

				return false;
			}

			/*
			 * Construct regular expression of classes
			 */
			function getClassesRegExp(configClasses, defaultClass) {
				var i, classesRegExp = '(^| )(piwik[_-]' + defaultClass;

				if (isDefined(configClasses)) {
					for (i = 0; i < configClasses.length; i++) {
						classesRegExp += '|' + configClasses[i];
					}
				}
				classesRegExp += ')( |$)';

				return new RegExp(classesRegExp);
			}

			/*
			 * Link or Download?
			 */
			function getLinkType(className, href, isInLink) {
				// outlinks
				if (!isInLink) {
					return 'link';
				}

				// does class indicate whether it is an (explicit/forced) outlink or a download?
				var downloadPattern = getClassesRegExp(configDownloadClasses, 'download'),
				linkPattern = getClassesRegExp(configLinkClasses, 'link'),

				// does file extension indicate that it is a download?
				downloadExtensionsPattern = new RegExp('\\.(' + configDownloadExtensions + ')([?&#]|$)', 'i');

				// optimization of the if..elseif..else construct below
				return linkPattern.test(className) ? 'link' : (downloadPattern.test(className) || downloadExtensionsPattern.test(href) ? 'download' : 0);

/*
				var linkType;

				if (linkPattern.test(className)) {
					// class attribute contains 'piwik_link' (or user's override)
					linkType = 'link';
				} else if (downloadPattern.test(className)) {
					// class attribute contains 'piwik_download' (or user's override)
					linkType = 'download';
				} else if (downloadExtensionsPattern.test(sourceHref)) {
					// file extension matches a defined download extension
					linkType = 'download';
				} else {
					// otherwise none of the above
					linkType = 0;
				}

				return linkType;
 */
			}

			/*
			 * Handle click event
			 */
			function clickHandler(clickEvent) {
				var sourceElement, parentElement, tag, linkType;

				if (!isDefined(clickEvent)) {
					clickEvent = windowAlias.event;
				}

				if (isDefined(clickEvent.target))  {
					sourceElement = clickEvent.target;
				} else if (isDefined(clickEvent.srcElement)) {
					sourceElement = clickEvent.srcElement;
				} else {
					return;
				}

				while ((parentElement = sourceElement.parentNode) &&
						((tag = sourceElement.tagName) != 'A' && tag != 'AREA')) {
					sourceElement = parentElement;
				}

				if (isDefined(sourceElement.href)) {
					// browsers, such as Safari, don't downcase hostname and href
					var originalSourceHostName = sourceElement.hostname,
						sourceHostName = originalSourceHostName.toLowerCase(),
						sourceHref = sourceElement.href.replace(originalSourceHostName, sourceHostName),
						scriptProtocol = new RegExp('^(javascript|vbscript|jscript|mocha|livescript|ecmascript): *', 'i');

					// ignore script pseudo-protocol links
					if (!scriptProtocol.test(sourceHref)) {
						// track outlinks and all downloads
						linkType = getLinkType(sourceElement.className, sourceHref, isSiteHostName(sourceHostName));
						if (linkType) {
							logLink(sourceHref, linkType);
						}
					}
				}
			}

			/*
			 * Add click listener to a DOM element
			 */
			function addClickListener(element) {
				addEventListener(element, 'click', clickHandler, false);
			}

			/*
			 * Add click handlers to anchor and AREA elements, except those to be ignored
			 */
			function addClickListeners() {
				if (!linkTrackingInstalled) {
					linkTrackingInstalled = true;

					// iterate through anchor elements with href and AREA elements

					var i, ignorePattern = getClassesRegExp(configIgnoreClasses, 'ignore'), linkElements = documentAlias.links;

					if (linkElements) {
						for (i = 0; i < linkElements.length; i++) {
							if (!ignorePattern.test(linkElements[i].className)) {
								addClickListener(linkElements[i]);
							}
						}
					}
				}
			}

/*<DEBUG>*/
			/*
			 * Register a test hook.  Using eval() permits access to otherwise
			 * privileged membmers.
			 */
			function registerHook(hookName, userHook) {
				var hookObj = null;

				if (typeof hookName == 'string' && !isDefined(registeredHooks[hookName])) {
					if (typeof userHook == 'object') {
						hookObj = userHook;
					} else if (typeof userHook == 'string') {
						try {
							eval('hookObj =' + userHook);
						} catch (e) { }
					}

					registeredHooks[hookName] = hookObj;
				}
				return hookObj;
			}
/*</DEBUG>*/

			/************************************************************
			 * Constructor
			 ************************************************************/

			/*
			 * initialize tracker
			 */
			browserHasCookies = hasCookies();
			detectBrowserPlugins();

/*<DEBUG>*/
			/*
			 * initialize test plugin
			 */
			executePluginMethod('run', registerHook);
/*</DEBUG>*/

			/************************************************************
			 * Public data and methods
			 ************************************************************/

			return {
/*<DEBUG>*/
				/*
				 * Test hook accessors
				 */
				hook: registeredHooks,
				getHook: function (hookName) {
					return registeredHooks[hookName];
				},
/*</DEBUG>*/

				/*
				 * Specify the Piwik server URL
				 */
				setTrackerUrl: function (trackerUrl) {
					if (isDefined(trackerUrl)) {
						configTrackerUrl = trackerUrl;
					}
				},

				/*
				 * Specify the site ID
				 */
				setSiteId: function (siteId) {
					if (isDefined(siteId)) {
						configTrackerSiteId = siteId;
					}
				},

				/*
				 * Pass custom data to the server
				 */
				setCustomData: function (customData) {
					if (isDefined(customData)) {
						configCustomData = customData;
					}
				},

				/*
				 * Set delay for link tracking (in milliseconds)
				 */
				setLinkTrackingTimer: function (delay) {
					if (isDefined(delay)) {
						configTrackerPause = delay;
					}
				},

				/*
				 * Set list of file extensions to be recognized as downloads
				 */
				setDownloadExtensions: function (extensions) {
					if (isDefined(extensions)) {
						configDownloadExtensions = extensions;
					}
				},

				/*
				 * Specify additional file extensions to be recognized as downloads
				 */
				addDownloadExtensions: function (extensions) {
					if (isDefined(extensions)) {
						configDownloadExtensions += '|' + extensions;
					}
				},

				/*
				 * Set array of domains to be treated as local
				 */
				setDomains: function (hostsAlias) {
					if (typeof hostsAlias == 'object' && hostsAlias instanceof Array) {
						configHostsAlias = hostsAlias;
						configHostsAlias.push(locationHostnameAlias);
					} else if (typeof hostsAlias == 'string') {
						configHostsAlias = [hostsAlias, locationHostnameAlias];
					}
				},

				/*
				 * Set array of classes to be ignored if present in link
				 */
				setIgnoreClasses: function (ignoreClasses) {
					if (typeof ignoreClasses == 'object' && ignoreClasses instanceof Array) {
						configIgnoreClasses = ignoreClasses;
					} else if (typeof ignoreClasses == 'string') {
						configIgnoreClasses = [ignoreClasses];
					}
				},

				/*
				 * Set request method (GET or POST; default is GET)
				 */
				setRequestMethod: function (method) {
					configRequestMethod = method || 'GET';
				},

				/*
				 * Override referrer
				 */
				setReferrerUrl: function (url) {
					if (isDefined(url)) {
						configReferrerUrl = url;
					}
				},

				/*
				 * Override url
				 */
				setCustomUrl: function (url) {
					if (isDefined(url)) {
						configCustomUrl = url;
					}
				},

				/*
				 * Override document.title
				 */
				setDocumentTitle: function (title) {
					if (isDefined(title)) {
						configTitle = title;
					}
				},

				/*
				 * Set array of classes to be treated as downloads
				 */
				setDownloadClasses: function (downloadClasses) {
					if (typeof downloadClasses == 'object' && downloadClasses instanceof Array) {
						configDownloadClasses = downloadClasses;
					} else if (typeof downloadClasses == 'string') {
						configDownloadClasses = [downloadClasses];
					}
				},

				/*
				 * Set download class name (i.e., override default: piwik_download)
				 * (deprecated)
				 */
				setDownloadClass: function (className) {
					if (typeof className == 'string') {
						configDownloadClasses = [className];
					}
				},

				/*
				 * Set array of classes to be treated as outlinks
				 */
				setLinkClasses: function (linkClasses) {
					if (typeof linkClasses == 'object' && linkClasses instanceof Array) {
						configLinkClasses = linkClasses;
					} else if (typeof linkClasses == 'string') {
						configLinkClasses = [linkClasses];
					}
				},

				/*
				 * Set outlink class name (i.e., override default: piwik_link)
				 * (deprecated)
				 */
				setLinkClass: function (className) {
					if (typeof className == 'string') {
						configLinkClasses = [className];
					}
				},

				/*
				 * Add click listener to a specific link element.
				 * When clicked, Piwik will log the click automatically.
				 */
				addListener: function (element) {
					if (isDefined(element)) {
						addClickListener(element);
					}
				},

				/*
				 * Install link tracker
				 */
				enableLinkTracking: function () {
					if (hasLoaded) {
						// the load event has already fired, add the click listeners now
						addClickListeners();
					} else {
						// defer until page has loaded
						registeredOnLoadHandlers[registeredOnLoadHandlers.length] = function () {
							addClickListeners();
						};
					}
				},

				/*
				 * Trigger a goal
				 */
				trackGoal: function (idGoal, customRevenue, customData) {
					logGoal(idGoal, customRevenue, customData);
				},

				/*
				 * Manually log a click from your own code
				 */
				trackLink: function (sourceUrl, linkType, customData) {
					logLink(sourceUrl, linkType, customData);
				},

				/*
				 * Log visit to this page
				 */
				trackPageView: function (customTitle, customData) {
					logPageView(customTitle, customData);
				}
			};
		}
		
		/************************************************************
		 * Proxy object
		 ************************************************************/

		function TrackerProxy() {
			return {
				push: apply
			};
		}

		/************************************************************
		 * Constructor
		 ************************************************************/

		// initialize the Piwik singleton
		addEventListener(windowAlias, 'beforeunload', beforeUnloadHandler, false);
		addReadyListener();

		asyncTracker = new Tracker();

		for (var i = 0; i < _paq.length; i++) {
			apply(_paq[i]);
		}

		// replace initialization array with proxy object
		_paq = new TrackerProxy();

		/************************************************************
		 * Public data and methods
		 ************************************************************/

		return {
			/*
			 * Add plugin
			 */
			addPlugin: function (pluginName, pluginObj) {
				plugins[pluginName] = pluginObj;
			},

			/*
			 * Get Tracker (factory method)
			 */
			getTracker: function (piwikUrl, siteId) {
				return new Tracker(piwikUrl, siteId);
			}
		};
	}()),

	/************************************************************
	 * Deprecated functionality below
	 * - for legacy piwik.js compatibility
	 ************************************************************/

	/*
	 * Piwik globals
	 *
	 *   var piwik_install_tracker, piwik_tracker_pause, piwik_download_extensions, piwik_hosts_alias, piwik_ignore_classes;
	 */

	piwik_track,

	/*
	 * Track page visit
	 */
	piwik_log = function (documentTitle, siteId, piwikUrl, customData) {

		function getOption(optionName) {
			try {
				return eval('piwik_' + optionName);
			} catch (e) { }

			return; /* undefined */
		}

		// instantiate the tracker
		var piwikTracker = Piwik.getTracker(piwikUrl, siteId);

		// initializer tracker
		piwikTracker.setDocumentTitle(documentTitle);
		piwikTracker.setCustomData(customData);

		// handle Piwik globals
		piwikTracker.setLinkTrackingTimer(getOption('tracker_pause'));
		piwikTracker.setDownloadExtensions(getOption('download_extensions'));
		piwikTracker.setDomains(getOption('hosts_alias'));
		piwikTracker.setIgnoreClasses(getOption('ignore_classes'));

		// track this page view
		piwikTracker.trackPageView();

		// default is to install the link tracker
		if (getOption('install_tracker') !== false) {

			/*
			 * Track click manually (function is defined below)
			 */
			piwik_track = function (sourceUrl, siteId, piwikUrl, linkType) {
				piwikTracker.setSiteId(siteId);
				piwikTracker.setTrackerUrl(piwikUrl);
				piwikTracker.trackLink(sourceUrl, linkType);
			};

			// set-up link tracking
			piwikTracker.enableLinkTracking();
		}
	};
