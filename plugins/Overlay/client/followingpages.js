var Piwik_Overlay_FollowingPages = (function () {

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
        location = Piwik_Overlay_UrlNormalizer.normalize(location);
        location = (("https:" == document.location.protocol) ? 'https' : 'http') + '://' + location;

        var excludedParamsLoaded = false;
        var followingPagesLoaded = false;

        // load excluded params
        Piwik_Overlay_Client.api('getExcludedQueryParameters', function (data) {
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
        Piwik_Overlay_Client.api('getFollowingPages', function (data) {
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
            page.label = Piwik_Overlay_UrlNormalizer.removeUrlPrefix(page.label);
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
        a[0].piwikDiscovered = true;

        var href = a.attr('href');
        href = Piwik_Overlay_UrlNormalizer.normalize(href);

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

    var repositionTimeout = false;
    var resizeTimeout = false;

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
        repositionAfterTimeout = function () {
            repositionTimeout = window.setTimeout(function () {
                findNewLinks();
                positionLinkTags(repositionAfterTimeout);
            }, 1800);
        };
        repositionAfterTimeout();

        // reposition link tags on window resize
        $(window).resize(function () {
            if (repositionTimeout) {
                window.clearTimeout(repositionTimeout);
            }
            if (resizeTimeout) {
                window.clearTimeout(resizeTimeout);
            }
            resizeTimeout = window.setTimeout(function () {
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
        if (typeof linkTag[0].piwikTagElement != 'undefined' && linkTag[0].piwikTagElement !== null) {
            // this link tag already has a tag element. happens in rare cases.
            return;
        }

        linkTag[0].piwikTagElement = true;

        var rate = data.clickRate;

        if( rate < 0.001 ) {
            rate = '<0.001';
        } else if (rate < 1) {
            rate = Math.round( rate * 1000 ) / 1000;
        } else if (rate < 10) {
            rate = Math.round(rate * 10) / 10;
        } else {
            rate = Math.round(rate);
        }

        var span = c('span').html(rate + '%');
        var tagElement = c('div', 'LinkTag').append(span).hide();
        body.prepend(tagElement);

        linkTag.add(tagElement).hover(function () {
            highlightLink(linkTag, linkUrl, data);
        }, function () {
            unHighlightLink(linkTag, linkUrl);
        });

        // attach the tag element to the link element. we can't use .data() because jquery
        // would remove it when removing the link from the dom. but we still need to find
        // the tag element to remove it as well.
        linkTag[0].piwikTagElement = tagElement;
    }

    /** Position the link tags next to the links */
    function positionLinkTags(callback) {
        var url, linkTag, tagElement, offset, top, left, isRight, hasOneChild, inlineChild;
        var tagWidth = 36, tagHeight = 21;
        var tagsToRemove = [];

        for (var i = 0; i < followingPages.length; i++) {
            url = followingPages[i].label;
            if (typeof linksOnPage[url] != 'undefined') {
                for (var j = 0; j < linksOnPage[url].length; j++) {
                    linkTag = linksOnPage[url][j];
                    tagElement = linkTag[0].piwikTagElement;

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

                    hasOneChild = checkHasOneChild(linkTag);
                    inlineChild = false;
                    if (hasOneChild && linkTag.css('display') != 'block') {
                        inlineChild = linkTag.children().eq(0);
                    }

                    if (getVisibility(linkTag) == 'hidden' || (
                        // in case of hasOneChild: jquery always returns linkTag.is(':visible')=false
                        !linkTag.is(':visible') && !(hasOneChild && inlineChild && inlineChild.is(':visible'))
                        )) {
                        // link is not visible
                        tagElement.hide();
                        continue;
                    }

                    tagElement.attr('class', 'PIS_LinkTag'); // reset class
                    if (tagElement[0].piwikHighlighted) {
                        tagElement.addClass('PIS_Highlighted');
                    }

                    // see comment in highlightLink()
                    if (hasOneChild && linkTag.find('> img').size() == 1) {
                        offset = linkTag.find('> img').offset();
                        if (offset.left == 0 && offset.top == 0) {
                            offset = linkTag.offset();
                        }
                    } else if (inlineChild !== false) {
                        offset = inlineChild.offset();
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
        for (var k = tagsToRemove.length - 1; k >= 0; k--) {
            var tagToRemove = tagsToRemove[k];
            linkTag = linksOnPage[tagToRemove.index1][tagToRemove.index2];
            // remove the tag element from the dom
            if (linkTag && linkTag[0] && linkTag[0].piwikTagElement) {
                tagElement = linkTag[0].piwikTagElement;
                if (tagElement[0].piwikHighlighted) {
                    unHighlightLink(linkTag, tagToRemove.index1);
                }
                tagElement.remove();
                linkTag[0].piwikTagElement = null;
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

    /** Get the visibility of an element */
    function getVisibility(el) {
        var visibility = el.css('visibility');
        if (visibility == 'inherit') {
            el = el.parent();
            if (el.size() > 0) {
                return getVisibility(el);
            }
        }
        return visibility;
    }

    /**
     * Find out whether a link has only one child. Using .children().size() == 1 doesn't work
     * because it doesn't take additional text nodes into account.
     */
    function checkHasOneChild(linkTag) {
        var hasOneChild = (linkTag.children().size() == 1);
        if (hasOneChild) {
            // if the element contains one tag and some text, hasOneChild is set incorrectly
            var contents = linkTag.contents();
            if (contents.size() > 1) {
                // find non-empty text nodes
                contents = contents.filter(function () {
                    return this.nodeType == 3 && // text node
                        $.trim(this.data).length > 0; // contains more than whitespaces
                });
                if (contents.size() > 0) {
                    hasOneChild = false;
                }
            }
        }
        return hasOneChild;
    }

    /** Check whether new links have been added to the dom */
    function findNewLinks() {
        var newLinks = $('a').filter(function () {
            return typeof this.piwikDiscovered == 'undefined' || this.piwikDiscovered === null;
        });

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
        var hasOneChild = checkHasOneChild(linkTag);
        if (hasOneChild && linkTag.find('img').size() == 1) {
            // if the <a> tag contains only an <img>, the offset and height methods don't work properly.
            // as a result, the box around the image link would be wrong. we use the image to derive
            // the offset and height instead of the link to get correct values.
            var img = linkTag.find('img');
            offset = img.offset();
            height = img.outerHeight();
        }
        if (hasOneChild && linkTag.css('display') != 'block') {
            // if the <a> tag is not displayed as block and has only one child, using the child to
            // derive the offset and dimensions is more robust.
            var child = linkTag.children().eq(0);
            offset = child.offset();
            height = child.outerHeight();
            width = child.outerWidth();
        } else {
            offset = linkTag.offset();
            height = linkTag.outerHeight();
        }

        var numLinks = linksOnPage[linkUrl].length;

        putBoxAroundLink(offset, width, height, numLinks, data.referrals);

        // highlight tags
        for (var j = 0; j < numLinks; j++) {
            var tag = linksOnPage[linkUrl][j][0].piwikTagElement;
            tag.addClass('PIS_Highlighted');
            tag[0].piwikHighlighted = true;
        }

        // Sometimes it fails to remove the notification when the hovered element is removed.
        // To make sure we don't display more than one location at a time, we hide all before showing the new one.
        Piwik_Overlay_Client.hideNotifications('LinkLocation');

        // we don't use .data() because jquery would remove the callback when the link tag is removed
        linkTag[0].piwikHideNotification = Piwik_Overlay_Client.notification(
            Piwik_Overlay_Translations.get('link') + ': ' + linkUrl, 'LinkLocation');
    }

    function putBoxAroundLink(offset, width, height, numLinks, numReferrals) {
        var borderWidth = 2;
        var padding = 4; // the distance between the link and the border

        // top border
        highlightElements[0]
            .width(width + 2 * padding)
            .css({
                top: offset.top - borderWidth - padding,
                left: offset.left - padding
            }).show();

        // right border
        highlightElements[1]
            .height(height + 2 * borderWidth + 2 * padding)
            .css({
                top: offset.top - borderWidth - padding,
                left: offset.left + width + padding
            }).show();

        // left border
        highlightElements[2]
            .height(height + 2 * borderWidth + 2 * padding)
            .css({
                top: offset.top - borderWidth - padding,
                left: offset.left - borderWidth - padding
            }).show();

        // bottom box text
        var text;
        if (numLinks > 1) {
            text = Piwik_Overlay_Translations.get('clicksFromXLinks')
                .replace(/%1\$s/, numReferrals)
                .replace(/%2\$s/, numLinks);
        } else if (numReferrals == 1) {
            text = Piwik_Overlay_Translations.get('oneClick');
        } else {
            text = Piwik_Overlay_Translations.get('clicks')
                .replace(/%s/, numReferrals);
        }

        // bottom box position and dimension
        var textPadding = '&nbsp;&nbsp;&nbsp;';
        highlightElements[3].html(textPadding + text + textPadding).css({
            width: 'auto',
            top: offset.top + height + padding,
            left: offset.left - borderWidth - padding
        }).show();

        var minBoxWidth = width + 2 * borderWidth + 2 * padding;
        if (highlightElements[3].width() < minBoxWidth) {
            // we cannot use minWidth because of IE7
            highlightElements[3].width(minBoxWidth);
        }
    }

    /** Remove highlight from link */
    function unHighlightLink(linkTag, linkUrl) {
        for (var i = 0; i < highlightElements.length; i++) {
            highlightElements[i].hide();
        }

        var numLinks = linksOnPage[linkUrl].length;
        for (var j = 0; j < numLinks; j++) {
            var tag = linksOnPage[linkUrl][j][0].piwikTagElement;
            if (tag) {
                tag.removeClass('PIS_Highlighted');
                tag[0].piwikHighlighted = false;
            }
        }

        if ((typeof linkTag[0].piwikHideNotification) == 'function') {
            linkTag[0].piwikHideNotification();
            linkTag[0].piwikHideNotification = null;
        }
    }

    return {

        /**
         * The main method
         */
        initialize: function (finishCallback) {
            c = Piwik_Overlay_Client.createElement;
            Piwik_Overlay_Client.loadScript('plugins/Overlay/client/urlnormalizer.js', function () {
                Piwik_Overlay_UrlNormalizer.initialize();
                load(function () {
                    Piwik_Overlay_UrlNormalizer.setExcludedParameters(excludedParams);
                    build(function () {
                        finishCallback();
                    })
                });
            });
        },

        /**
         * Remove everything from the dom and terminate timeouts.
         * This can be used from the console in order to load a new implementation for debugging afterwards.
         * If you add `Piwik_Overlay_FollowingPages.remove();` to the beginning and
         * `Piwik_Overlay_FollowingPages.initialize(function(){});` to the end of this file, you can just
         * paste it into the console to inject the new implementation.
         */
        remove: function () {
            for (var i = 0; i < followingPages.length; i++) {
                var url = followingPages[i].label;
                if (typeof linksOnPage[url] != 'undefined') {
                    for (var j = 0; j < linksOnPage[url].length; j++) {
                        var linkTag = linksOnPage[url][j];
                        var tagElement = linkTag[0].piwikTagElement;
                        if (tagElement) {
                            tagElement.remove();
                        }
                        linkTag[0].piwikTagElement = null;

                        $(linkTag).unbind('mouseenter').unbind('mouseleave');
                    }
                }
            }
            for (i = 0; i < highlightElements.length; i++) {
                highlightElements[i].remove();
            }
            if (repositionTimeout) {
                window.clearTimeout(repositionTimeout);
            }
            if (resizeTimeout) {
                window.clearTimeout(resizeTimeout);
            }
            $(window).unbind('resize');
        }

    };

})();