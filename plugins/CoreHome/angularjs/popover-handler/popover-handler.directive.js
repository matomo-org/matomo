/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * When present in the page it listens to a popover URL parameter.
 *
 * If present it will try to load the related content in a popover or if the URL is empty it will close an
 * opened popover.
 *
 * Example:
 * <div piwik-popover-handler></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikPopoverHandler', piwikPopoverHandler);

    piwikPopoverHandler.$inject = ['$location', '$rootScope', 'piwik'];

    function piwikPopoverHandler($location, $rootScope, piwik){

        return {
            restrict: 'A',
            scope: {},
            controller: function () {

                function close()
                {
                    Piwik_Popover.close();
                }

                function open(popoverParam)
                {
                    // in case the $ was encoded (e.g. when using copy&paste on urls in some browsers)
                    popoverParam = decodeURIComponent(popoverParam);
                    // revert special encoding from broadcast.propagateNewPopoverParameter()
                    popoverParam = popoverParam.replace(/\$/g, '%');
                    popoverParam = decodeURIComponent(popoverParam);

                    var popoverParamParts = popoverParam.split(':');
                    var handlerName = popoverParamParts[0];
                    popoverParamParts.shift();
                    var param = popoverParamParts.join(':');
                    if (typeof piwik.broadcast.popoverHandlers[handlerName] != 'undefined'
                        && !piwik.broadcast.isLoginPage()) {
                        piwik.broadcast.popoverHandlers[handlerName](param);
                    }
                }

                function openOrClose()
                {
                    // should be rather done by routing
                    var popoverParam = $location.search().popover;
                    if (popoverParam) {
                        open(popoverParam);
                    } else {
                        close();
                    }
                }

                $rootScope.$on('$locationChangeSuccess', function () {
                    // should be rather done by routing
                    $(function () {
                        // make sure all popover handles were registered
                        openOrClose();
                    });
                });

            }
        };
    }
})();