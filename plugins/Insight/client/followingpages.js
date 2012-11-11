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

	/**
	 * Build an index of links on the page.
	 * This function is passed to $('a').each()
	 */
	var processLinkDelta = false;

	function processLink() {
		var a = $(this);
		a.addClass('piwik-discovered');

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

		if (href && processLinkDelta !== false) {
			if (typeof processLinkDelta[href] == 'undefined') {
				processLinkDelta[href] = [a];
			}
			else {
				processLinkDelta[href].push(a);
			}
		}
	}

	function build(callback) {
		// build an index of all links on the page
		$('a').each(processLink);

		// add tags to known following pages
		createLinkTags(linksOnPage);

		// position the tags
		positionLinkTags();

		callback();

		// check on a regular basis whether new links have appeared.
		// we use a timeout instead of an interval to make sure one call is done before 
		// the next one is triggered
		var repositionAfterTimeout;
		var repositionTimeout = false;
		repositionAfterTimeout = function() {
			repositionTimeout = window.setTimeout(function() {
				findNewLinks();
				positionLinkTags(repositionAfterTimeout);
			}, 1800);
		};
		repositionAfterTimeout();

		// reposition link tags on window resize
		var timeout = false;
		$(window).resize(function() {
			if (repositionTimeout) {
				window.clearTimeout(repositionTimeout);
			}
			if (timeout) {
				window.clearTimeout(timeout);
			}
			timeout = window.setTimeout(function() {
				positionLinkTags();
				repositionAfterTimeout();
			}, 70);
		});
	}

	/** Create a batch of link tags */
	function createLinkTags(links) {
		var body = $('body');
		for (var i = 0; i < followingPages.length; i++) {
			var url = followingPages[i].label;
			if (typeof links[url] != 'undefined') {
				for (var j = 0; j < links[url].length; j++) {
					createLinkTag(links[url][j], url, followingPages[i], body);
				}
			}
		}
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
			highlightLink(linkTag, linkUrl, data);
		}, function() {
			unHighlightLink(linkTag, linkUrl);
		});

		// attach the tag element to the link element. we can't use .data() because jquery
		// would remove it when removing the link from the dom. but we still need to find
		// the tag element to remove it as well.
		linkTag[0].piwikTag = tagElement;
	}

	/** Position the link tags next to the links */
	function positionLinkTags(callback) {
		var url, linkTag, tagElement, offset, top, left, isRight;
		var tagWidth = 36, tagHeight = 21;
		var tagsToRemove = [];
		
		for (var i = 0; i < followingPages.length; i++) {
			url = followingPages[i].label;
			if (typeof linksOnPage[url] != 'undefined') {
				for (var j = 0; j < linksOnPage[url].length; j++) {
					linkTag = linksOnPage[url][j];
					tagElement = linkTag[0].piwikTag;

					if (linkTag.closest('html').length == 0 || !tagElement) {
						// the link has been removed from the dom
						if (tagElement) {
							tagElement.hide();
						}
						// mark for deletion. don't delete it now because we
						// are iterating of the array it's in. it will be deleted
						// below this for loop.
						tagsToRemove.push({
							index1: url,
							index2: j
						});
						continue;
					}
					
					if (!linkTag.is(':visible') || linkTag.css('visibility') == 'hidden') {
						// link is not visible
						tagElement.hide();
						continue;
					}

					tagElement.attr('class', 'PIS_LinkTag'); // reset class
					if (tagElement.data('piwik-highlighted')) {
						tagElement.addClass('PIS_Highlighted');
					}

					if (linkTag.children().size() == 1 && linkTag.find('img').size() == 1) {
						// see comment in highlightLink()
						offset = linkTag.find('img').offset();
					} else {
						offset = linkTag.offset();
					}

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

		// walk tagsToRemove from back to front because it contains the indexes in ascending
		// order. removing something from the front will impact the indexes that come after-
		// wards. this can be avoided by starting in the back.
		for (var k = tagsToRemove.length - 1; k >= 0 ; k--) {
			var tagToRemove = tagsToRemove[k];
			linkTag = linksOnPage[tagToRemove.index1][tagToRemove.index2];
			// remove the tag element from the dom
			if (linkTag && linkTag[0] && linkTag[0].piwikTag) {
				tagElement = linkTag[0].piwikTag;
				if (tagElement.data('piwik-highlighted')) {
					unHighlightLink(linkTag, tagToRemove.index1);
				}
				tagElement.remove();
				delete linkTag[0].piwikTag;
			}
			// remove the link from the index
			linksOnPage[tagToRemove.index1].splice(tagToRemove.index2, 1);
			if (linksOnPage[tagToRemove.index1].length == 0) {
				delete linksOnPage[tagToRemove.index1];
			}
		}

		if (typeof callback == 'function') {
			callback();
		}
	}

	/** Check whether new links have been added to the dom */
	function findNewLinks() {
		var newLinks = $('a:not(.piwik-discovered)');

		if (newLinks.size() == 0) {
			return;
		}

		processLinkDelta = {};
		newLinks.each(processLink);
		createLinkTags(processLinkDelta);
		processLinkDelta = false;
	}

	/** Dom elements used for drawing a box around the link */
	var highlightElements = [];

	/** Highlight a link on hover */
	function highlightLink(linkTag, linkUrl, data) {
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

		var width = linkTag.outerWidth();

		var offset, height;
		if (linkTag.children().size() == 1 && linkTag.find('img').size() == 1) {
			// if the <a> tag contains only an <img>, the offset and height methods don't work properly.
			// as a result, the box around the image link would be wrong. we use the image to derive
			// the offset and height instead of the link to get correct values.
			var img = linkTag.find('img');
			offset = img.offset();
			height = img.outerHeight();
		} else {
			offset = linkTag.offset();
			height = linkTag.outerHeight();
		}

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
			var tag = linksOnPage[linkUrl][j][0].piwikTag;
			tag.addClass('PIS_Highlighted');
			tag.data('piwik-highlighted', true);
		}

		// we don't use .data() because jquery would remove the callback when the link tag is removed
		linkTag[0].piwikHideNotification = Piwik_Insight_Client.notification(
			Piwik_Insight_Translations.get('link') + ': ' + linkUrl);
	}

	/** Remove highlight from link */
	function unHighlightLink(linkTag, linkUrl) {
		for (var i = 0; i < highlightElements.length; i++) {
			highlightElements[i].hide();
		}

		var numLinks = linksOnPage[linkUrl].length;
		for (var j = 0; j < numLinks; j++) {
			var tag = linksOnPage[linkUrl][j][0].piwikTag;
			if (tag) {
				tag.removeClass('PIS_Highlighted');
				tag.data('piwik-highlighted', false);
			}
		}

		if (typeof linkTag[0].piwikHideNotification == 'function') {
			linkTag[0].piwikHideNotification.apply();
		}
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
