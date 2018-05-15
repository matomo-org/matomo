/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * See https://github.com/piwik/piwik/issues/4795 "linking to #hash tag does not work after merging AngularJS"
 */
(function () {

    function scrollToAnchorNode($node)
    {
        $.scrollTo($node, 20);
    }

    function preventDefaultIfEventExists(event)
    {
        if (event) {
            event.preventDefault();
        }
    }

    function scrollToAnchorIfPossible(hash, event)
    {
        if (!hash) {
            return;
        }

        if (-1 !== hash.indexOf('&')) {
            return;
        }

        try {
            var $node = $('#' + hash);
        } catch (err) {
            // on jquery syntax error, ignore so nothing is logged to the console
            return;
        }

        if ($node && $node.length) {
            scrollToAnchorNode($node);
            preventDefaultIfEventExists(event);
            return;
        }

        $node = $('a[name='+ hash + ']');

        if ($node && $node.length) {
            scrollToAnchorNode($node);
            preventDefaultIfEventExists(event);
        }
    }

    function isLinkWithinSamePage(location, newUrl)
    {
        if (location && location.origin && -1 === newUrl.indexOf(location.origin)) {
            // link to different domain
            return false;
        }

        if (location && location.pathname && -1 === newUrl.indexOf(location.pathname)) {
            // link to different path
            return false;
        }

        if (location && location.search && -1 === newUrl.indexOf(location.search)) {
            // link with different search
            return false;
        }

        return true;
    }

    function handleScrollToAnchorIfPresentOnPageLoad()
    {
        if (location.hash.substr(0, 2) == '#/') {
            var hash = location.hash.substr(2);
            scrollToAnchorIfPossible(hash, null);
        }
    }

    function handleScrollToAnchorAfterPageLoad()
    {
        angular.module('piwikApp').run(['$rootScope', function ($rootScope) {

            $rootScope.$on('$locationChangeStart', onLocationChangeStart);

            function onLocationChangeStart (event, newUrl, oldUrl, $location) {

                if (!newUrl) {
                    return;
                }

                var hashPos = newUrl.indexOf('#/');
                if (-1 === hashPos) {
                    return;
                }

                if (!isLinkWithinSamePage(this.location, newUrl)) {
                    return;
                }

                var hash = newUrl.substr(hashPos + 2);

                scrollToAnchorIfPossible(hash, event);
            }
        }]);
    }

    handleScrollToAnchorAfterPageLoad();
    $(handleScrollToAnchorIfPresentOnPageLoad);

})();