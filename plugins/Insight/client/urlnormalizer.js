
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
	
	/** The main domain of the website */
	var mainDomain;
	
	/** Array of alias domains */
	var aliases = [];
	
	/**
	 * Basic normalizations for domains
	 * Returns array
	 * 0: normalized url
	 * 1: true, if url was absolute (if not, no normalization was performed)
	 */
	function normalizeDomain(url) {
		absolute = false;
		
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
			if (url.substring(0, 4) == 'www.') {
				url = url.substring(4, url.length);
			}
			
			// add slash to domain names
			if (url.indexOf('/') == -1) {
				url += '/';
			}
		}

		return [url, absolute];
	}
	
	/** Replace domain aliases in normalized domains */
	function replaceDomainAliases(url) {
		var alias;
		for (var i = 0; i < aliases.length; i++) {
			alias = aliases[i];
			if (url.substring(0, alias.length) == alias) {
				url = mainDomain + url.substring(alias.length, url.length);
				break;
			}
		}
		return url;
	}
	
	return {
		
		/**
		 * Provide information about the main domain and its alias.
		 * First domain is main domain, others are aliases.
		 * Aliases will later be replaced with the main domain.
		 */
		initialize: function(urls) {
			mainDomain = normalizeDomain(urls[0])[0];
			
			for (var i = 1; i < urls.length; i++) {
				var alias = normalizeDomain(urls[i])[0];
				aliases.push(alias);
			}
			
			this.setCurrentUrl(window.location.href);
			
			var head = document.getElementsByTagName('head');
			if (head.length) {
				var base = head[0].getElementsByTagName('base');
				if (base.length && base[0].href) {
					this.setBaseHref(base[0].href);
				}
			}
		},
		
		/** Explicitly set base href (for testing) */
		setBaseHref: function(pBaseHref) {
			baseHref = normalizeDomain(pBaseHref)[0];
			baseHref = replaceDomainAliases(baseHref);
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
			currentFolder = replaceDomainAliases(currentFolder);
		},
		
		/**
		 * Normalize URL
		 * Can be an absolute or a reltive URL
		 */
		normalize: function(url, withoutMainDomain) {
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
			
			if (absolute) {
				/** absolute url */
				url = replaceDomainAliases(url);
			} else {
				/** relative url */
				if (url.substring(0, 1) == '/') {
					// relative to domain root
					url = mainDomain + url;
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
			
			// remove main domain ?
			if (withoutMainDomain && url.substring(0, mainDomain.length) == mainDomain) {
				url = url.substring(mainDomain.length, url.length);
			}
			
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
	
	
	Piwik_Insight_UrlNormalizer.initialize([
		'http://www.example.com/',
		'http://example2.com/',
		'https://www.example3.com'
	]);
	
	
	Piwik_Insight_UrlNormalizer.setCurrentUrl('https://www.example2.com/current/test.html?asdfasdf');
	
	test([[
		'relative/path/',
		'example.com/current/relative/path/'
	]]);
	
	
	Piwik_Insight_UrlNormalizer.setCurrentUrl('http://example3.com/current/folder/');
	
	test([[
		'relative.html',
		'example.com/current/folder/relative.html'
	]]);
	
	
	Piwik_Insight_UrlNormalizer.setBaseHref('http://example.com/base/');
	
	test([
		[
			'http://www.example2.com/my/test/path.html?id=2#MyAnchor',
			'example.com/my/test/path.html?id=2'
		], [
			'/my/test/foo/../path.html',
			'example.com/my/test/path.html'
		], [
			'path/./test//test///foo.bar',
			'example.com/base/path/test/test/foo.bar'
		], [
			'https://example2.com//test.html#asdf',
			'example.com/test.html'
		], [
			'#',
			''
		], [
			'#Anchor',
			''
		], [
			'/',
			'example.com/'
		]
	]);
	
	
	if (success) {
		alert('TEST SUCCESS');
	}
	
})(); // */
