var Piwik_Insight_FollowingPages = (function() {

	/** jQuery */
	var $ = jQuery;

	/** Info about the following pages */
	var followingPages = [];
	
	/** List of excluded get parameters */
	var excludedParams = [];

	/** Index of the links on the page */
	var linksOnPage = {};

	/** Reference to create element function */
	var c;

	/** Load the following pages */
	function load(callback) {
		// normalize current location
		var location = window.location.href;
		location = Piwik_Insight_UrlNormalizer.normalize(location);
		location = (("https:" == document.location.protocol) ? 'https' : 'http') + '://' + location;

		var excludedParamsLoaded = false;
		var followingPagesLoaded = false;
		
		// load excluded params
		Piwik_Insight_Client.api('getExcludedQueryParameters', function(data) {
			for (var i = 0; i < data.length; i++) {
				if (typeof data[i] == 'object') {
					data[i] = data[i][0];
				}
			}
			excludedParams = data;
			
			excludedParamsLoaded = true;
			if (followingPagesLoaded) {
				callback();
			}
		});
		
		// load following pages
		Piwik_Insight_Client.api('getFollowingPages', function(data) {
			followingPages = data;
			processFollowingPages();
			
			followingPagesLoaded = true;
			if (excludedParamsLoaded) {
				callback();
			}
		}, 'url=' + encodeURIComponent(location));
	}

	/** Normalize the URLs of following pages and aggregate some stats */
	function processFollowingPages() {
		var totalClicks = 0;
		for (var i = 0; i < followingPages.length; i++) {
			var page = followingPages[i];
			// though the following pages are returned without the prefix, downloads
			// and outlinks still have it.
			page.label = Piwik_Insight_UrlNormalizer.removeUrlPrefix(page.label);
			totalClicks += followingPages[i].referrals;
		}
		for (i = 0; i < followingPages.length; i++) {
			followingPages[i].clickRate = followingPages[i].referrals / totalClicks * 100;
		}
	}

	function build(callback) {
		var body = $('body');

		// build an index of all links on the page
		$('a').each(function() {
			var a = $(this);
			var href = a.attr('href');
			href = Piwik_Insight_UrlNormalizer.normalize(href);

			if (href) {
				if (typeof linksOnPage[href] == 'undefined') {
					linksOnPage[href] = [a];
				}
				else {
					linksOnPage[href].push(a);
				}
			}
		});

		// add tags to known following pages
		for (var i = 0; i < followingPages.length; i++) {
			var url = followingPages[i].label;
			if (typeof linksOnPage[url] != 'undefined') {
				for (var j = 0; j < linksOnPage[url].length; j++) {
					createLinkTag(linksOnPage[url][j], url, followingPages[i], body);
				}
			}
		}

		positionLinkTags();

		callback();

		// check on a regular basis whether new links have appeared.
		// we use a timeout instead of an interval to make sure one call is done before 
		// the next one is triggered
		var repositionAfterTimeout;
		repositionAfterTimeout = function() {
			window.setTimeout(function() {
				positionLinkTags(repositionAfterTimeout);
			}, 1800);
		};
		repositionAfterTimeout();
		
		// reposition link tags on window resize
		var timeout = false;
		$(window).resize(function() {
			if (timeout) {
				window.clearTimeout(timeout);
			}
			timeout = window.setTimeout(function() {
				positionLinkTags();
			}, 70);
		});
	}

	/** Create the link tag element */
	function createLinkTag(linkTag, linkUrl, data, body) {
		var rate = data.clickRate;
		if (rate < 10) {
			rate = Math.round(rate * 10) / 10;
		} else {
			rate = Math.round(rate);
		}

		var span = c('span').html(rate + '%');
		var tagElement = c('div', 'LinkTag').append(span).hide();
		body.prepend(tagElement);

		linkTag.add(tagElement).hover(function() {
			highlightLink(linkTag, linkUrl, data, tagElement);
		}, function() {
			unHighlightLink(linkTag, linkUrl, tagElement);
		});
		
		linkTag.data('piwik-tag', tagElement);
	}

	/** Position the link tags next to the links */
	function positionLinkTags(callback) {
		var url, linkTag, tagElement, offset, top, left, isRight;
		var tagWidth = 36, tagHeight = 21;

		for (var i = 0; i < followingPages.length; i++) {
			url = followingPages[i].label;
			if (typeof linksOnPage[url] != 'undefined') {
				for (var j = 0; j < linksOnPage[url].length; j++) {
					
					linkTag = linksOnPage[url][j];
					tagElement = linkTag.data('piwik-tag');
					
					if (!linkTag.is(':visible')) {
						tagElement.hide();
						continue;
					}
					
					tagElement.attr('class', 'PIS_LinkTag'); // reset class
					if (tagElement.data('piwik-highlighted')) {
						tagElement.addClass('PIS_Highlighted');
					}
					
					offset = linkTag.offset();
		
					top = offset.top - tagHeight + 6;
					left = offset.left - tagWidth + 10;
		
					if (isRight = (left < 2)) {
						tagElement.addClass('PIS_Right');
						left = offset.left + linkTag.outerWidth() - 10;
					}
		
					if (top < 2) {
						tagElement.addClass(isRight ? 'PIS_BottomRight' : 'PIS_Bottom');
						top = offset.top + linkTag.outerHeight() - 6;
					}
		
					tagElement.css({
						top: top + 'px',
						left: left + 'px'
					}).show();
					
				}
			}
		}
		
		if (typeof callback == 'function') {
			callback();
		}
	}

	/** Dom elements used for drawing a box around the link */
	var highlightElements = [];

	/** Highlight a link on hover */
	function highlightLink(linkTag, linkUrl, data, tagElement) {
		if (highlightElements.length == 0) {
			highlightElements.push(c('div', 'LinkHighlightBoxTop'));
			highlightElements.push(c('div', 'LinkHighlightBoxRight'));
			highlightElements.push(c('div', 'LinkHighlightBoxLeft'));
			
			highlightElements.push(c('div', 'LinkHighlightBoxText'));
			
			var body = $('body');
			for (var i = 0; i < highlightElements.length; i++) {
				body.prepend(highlightElements[i].css({display: 'none'}));
			}
		}

		var offset = linkTag.offset();
		var width = linkTag.outerWidth();
		var height = linkTag.outerHeight();

		highlightElements[0].width(width).css({top: offset.top - 2, left: offset.left}).show();
		highlightElements[1].height(height + 4).css({top: offset.top - 2, left: offset.left + width}).show();
		highlightElements[2].height(height + 4).css({top: offset.top - 2, left: offset.left - 2}).show();
		
		var numLinks = linksOnPage[linkUrl].length;
		var text;
		if (numLinks > 1) {
			text = Piwik_Insight_Translations.get('clicksFromXLinks')
				.replace(/%1\$s/, data.referrals)
				.replace(/%2\$s/, numLinks);
		} else if (data.referrals == 1) {
			text = Piwik_Insight_Translations.get('oneClick');
		} else { 
			text = Piwik_Insight_Translations.get('clicks')
				.replace(/%s/, data.referrals);
		}
	
		var padding = '&nbsp;&nbsp;';
		highlightElements[3].html(padding + text + padding).css({
			minWidth: (width + 4) + 'px',
			top: offset.top + height,
			left: offset.left - 2
		}).show();
		
		for (var j = 0; j < numLinks; j++) {
			var tag = linksOnPage[linkUrl][j].data('piwik-tag');
			tag.addClass('PIS_Highlighted');
			tag.data('piwik-highlighted', true);
		}
		
		linkTag.data('piwik-hideNotification', Piwik_Insight_Client.notification(
			Piwik_Insight_Translations.get('link') + ': ' + linkUrl));				
	}
	
	/** Remove highlight from link */
	function unHighlightLink(linkTag, linkUrl, tagElement) {
		for (var i = 0; i < highlightElements.length; i++) {
			highlightElements[i].hide();
		}
		
		var numLinks = linksOnPage[linkUrl].length;
		for (var j = 0; j < numLinks; j++) {
			var tag = linksOnPage[linkUrl][j].data('piwik-tag');
			tag.removeClass('PIS_Highlighted');
			tag.data('piwik-highlighted', false);
		}
		
		linkTag.data('piwik-hideNotification').apply();
	}

	
	return {
		
		/** The main method */
		initialize: function(finishCallback) {
			c = Piwik_Insight_Client.createElement;
			Piwik_Insight_Client.loadScript('plugins/Insight/client/urlnormalizer.js', function() {
				Piwik_Insight_UrlNormalizer.initialize();
				load(function() {
					Piwik_Insight_UrlNormalizer.setExcludedParameters(excludedParams);
					build(function() {
						finishCallback();
					})
				});
			});
		}

	};

})();
