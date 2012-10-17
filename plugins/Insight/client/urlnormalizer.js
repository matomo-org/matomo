
/**
 * URL NORMALIZER
 * This utility preprocesses both the URLs in the document and
 * from the Piwik logs in order to make matching possible.
 */
var Piwik_Insight_UrlNormalizer = (function() {
	
	/** Base href of the current document */
	var baseHref = false;
	
	/** Url of current folder */
	var currentFolder;
	
	/** The current domain */
	var currentDomain;
	
	/**
	 * Basic normalizations for domain names
	 * - remove protocol and www from absolute urls
	 * - add a trailing slash to urls without a path
	 * 
	 * Returns array
	 * 0: normalized url
	 * 1: true, if url was absolute (if not, no normalization was performed)
	 */
	function normalizeDomain(url) {
		var absolute = false;
		
		// remove protocol
		if (url.substring(0, 7) == 'http://') {
			absolute = true;
			url = url.substring(7, url.length);
		} else if (url.substring(0, 8) == 'https://') {
			absolute = true;
			url = url.substring(8, url.length);
		}
		
		if (absolute) {
			// remove www.
			url = removeWww(url);
			
			// add slash to domain names
			if (url.indexOf('/') == -1) {
				url += '/';
			}
		}

		return [url, absolute];
	}
	
	/** Remove www. from a domain */
	function removeWww(domain) {
		if (domain.substring(0, 4) == 'www.') {
			return domain.substring(4, domain.length);
		}
		return domain;
	}
	
	return {
		
		initialize: function() {
			this.setCurrentDomain(document.location.hostname);
			this.setCurrentUrl(window.location.href);
			
			var head = document.getElementsByTagName('head');
			if (head.length) {
				var base = head[0].getElementsByTagName('base');
				if (base.length && base[0].href) {
					this.setBaseHref(base[0].href);
				}
			}
		},
		
		/** Explicitly set domain (for testing) */
		setCurrentDomain: function(pCurrentDomain) {
			currentDomain = removeWww(pCurrentDomain);
		},
		
		/** Explicitly set current url (for testing) */
		setCurrentUrl: function(url) {
			var index = url.lastIndexOf('/');
			if (index != url.length - 1) {
				currentFolder = url.substring(0, index + 1);
			} else {
				currentFolder = url;
			}
			currentFolder = normalizeDomain(currentFolder)[0];
		},
		
		/** Explicitly set base href (for testing) */
		setBaseHref: function(pBaseHref) {
			if (!pBaseHref) {
				baseHref = false;
			} else {
				baseHref = normalizeDomain(pBaseHref)[0];
			}
		},
		
		/**
		 * Normalize URL
		 * Can be an absolute or a reltive URL
		 */
		normalize: function(url) {
			if (!url) {
				return '';
			}
			
			// ignore urls starting with #
			if (url.substring(0, 1) == '#') {
				return '';
			}
			
			// basic normalizations for absolute urls
			var normalized = normalizeDomain(url);
			url = normalized[0];
			
			var absolute = normalized[1];
			
			if (!absolute) {
				/** relative url */
				if (url.substring(0, 1) == '/') {
					// relative to domain root
					url = currentDomain + url;
				} else if (baseHref) {
					// relative to base href
					url = baseHref + url;
				} else {
					// relative to current folder
					url = currentFolder + url;
				}
			}
			
			// remove #...
			var pos;
			if ((pos = url.indexOf('#')) != -1) {
				url = url.substring(0, pos);
			}
			
			// replace multiple / with a single /
			url = url.replace(/\/\/+/g, '/');
			
			// handle ./ and ../
			var parts = url.split('/');
			var url = [];
			for (var i = 0; i < parts.length; i++) {
				if (parts[i] == '.') {
					continue;
				}
				else if (parts[i] == '..') {
					url.pop();
				}
				else {
					url.push(parts[i]);
				}
			}
			url = url.join('/');
			
			// TODO: remove ignored paramters
			// TODO: handle order of parameters (?)
			
			return url;
		}
		
	};
	
})();


/* TESTS FOR DOMAIN NORMALIZER *

(function() {
	
	var success = true;
	
	function test(testCases) {
		for (var i = 0; i < testCases.length; i++) {
			var observed = Piwik_Insight_UrlNormalizer.normalize(testCases[i][0]);
			var expected = testCases[i][1];
			if (observed != expected) {
				alert("TEST FAIL!\nOriginal: " + testCases[i][0] +
						"\nObserved: " + observed + "\nExpected: " + expected);
				success = false;
			}
		}
	}
	
	
	Piwik_Insight_UrlNormalizer.initialize();
	
	Piwik_Insight_UrlNormalizer.setBaseHref(false);
	
	Piwik_Insight_UrlNormalizer.setCurrentDomain('example.com');
	Piwik_Insight_UrlNormalizer.setCurrentUrl('https://www.example.com/current/test.html?asdfasdf');
	
	test([
		[
			'relative/path/',
			'example.com/current/relative/path/'
		], [
			'http://www.example2.com/path/foo.html',
			'example2.com/path/foo.html'
		]
	]);
	
	
	Piwik_Insight_UrlNormalizer.setCurrentDomain('www.example3.com');
	Piwik_Insight_UrlNormalizer.setCurrentUrl('http://example3.com/current/folder/');
	
	test([[
		'relative.html',
		'example3.com/current/folder/relative.html'
	]]);
	
	
	Piwik_Insight_UrlNormalizer.setBaseHref('http://example.com/base/');
	
	test([
		[
			'http://www.example2.com/my/test/path.html?id=2#MyAnchor',
			'example2.com/my/test/path.html?id=2'
		], [
			'/my/test/foo/../path.html',
			'example3.com/my/test/path.html'
		], [
			'path/./test//test///foo.bar',
			'example.com/base/path/test/test/foo.bar'
		], [
			'https://example2.com//test.html#asdf',
			'example2.com/test.html'
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
	
	
	if (success) {
		alert('TEST SUCCESS');
	}
	
})(); // */
