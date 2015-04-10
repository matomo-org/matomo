/**
 * URL NORMALIZER
 * This utility preprocesses both the URLs in the document and
 * from the Piwik logs in order to make matching possible.
 */
var Piwik_Overlay_UrlNormalizer = (function () {

    /** Base href of the current document */
    var baseHref = false;

    /** Url of current folder */
    var currentFolder;

    /** The current domain */
    var currentDomain;

    /** Regular expressions for parameters to be excluded when matching links on the page */
    var excludedParamsRegEx = [];

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
        if (url === null) {
            return '';
        }

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

        initialize: function () {
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

        /**
         * Explicitly set domain (for testing)
         */
        setCurrentDomain: function (pCurrentDomain) {
            currentDomain = removeWww(pCurrentDomain);
        },

        /**
         * Explicitly set current url (for testing)
         */
        setCurrentUrl: function (url) {
            var index = url.lastIndexOf('/');
            if (index != url.length - 1) {
                currentFolder = url.substring(0, index + 1);
            } else {
                currentFolder = url;
            }
            currentFolder = normalizeDomain(currentFolder)[0];
        },

        /**
         * Explicitly set base href (for testing)
         */
        setBaseHref: function (pBaseHref) {
            if (!pBaseHref) {
                baseHref = false;
            } else {
                baseHref = normalizeDomain(pBaseHref)[0];
            }
        },

        /**
         * Set the parameters to be excluded when matching links on the page
         */
        setExcludedParameters: function (pExcludedParams) {
            excludedParamsRegEx = [];
            for (var i = 0; i < pExcludedParams.length; i++) {
                var paramString = pExcludedParams[i];
                excludedParamsRegEx.push(new RegExp('&' + paramString + '=([^&#]*)', 'ig'));
            }
        },

        /**
         * Remove the protocol and the prefix of a URL
         */
        removeUrlPrefix: function (url) {
            return normalizeDomain(url)[0];
        },

        /**
         * Normalize URL
         * Can be an absolute or a relative URL
         */
        normalize: function (url) {
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

            // replace multiple / with a single /
            url = url.replace(/\/\/+/g, '/');

            // handle ./ and ../
            var parts = url.split('/');
            var urlArr = [];
            for (var i = 0; i < parts.length; i++) {
                if (parts[i] == '.') {
                    // ignore
                }
                else if (parts[i] == '..') {
                    urlArr.pop();
                }
                else {
                    urlArr.push(parts[i]);
                }
            }
            url = urlArr.join('/');

            // remove ignored parameters
            url = url.replace(/\?/, '?&');
            for (i = 0; i < excludedParamsRegEx.length; i++) {
                var regEx = excludedParamsRegEx[i];
                url = url.replace(regEx, '');
            }
            url = url.replace(/\?&/, '?');
            url = url.replace(/\?#/, '#');
            url = url.replace(/\?$/, '');
            url = url.replace(/%5B/gi, '[');
            url = url.replace(/%5D/gi, ']');

            return url;
        }

    };

})();