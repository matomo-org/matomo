/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-live-widget-refresh>
 */
(function () {
    angular.module('piwikApp').directive('piwikLiveWidgetRefresh', piwikLiveWidgetRefresh);

    piwikLiveWidgetRefresh.$inject = ['piwik', '$timeout'];

    function piwikLiveWidgetRefresh(piwik, $timeout){

        return {
            restrict: 'A',
            scope: {
                liveRefreshAfterMs: '@'
            },
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    $timeout(function () {
                        var segment = broadcast.getValueFromHash('segment');
                        if (!segment) {
                            segment = broadcast.getValueFromUrl('segment');
                        }

                        $(element).find('#visitsLive').liveWidget({
                            interval: scope.liveRefreshAfterMs,
                            onUpdate: function () {
                                //updates the numbers of total visits in startbox
                                var ajaxRequest = new ajaxHelper();
                                ajaxRequest.setFormat('html');
                                ajaxRequest.addParams({
                                    module: 'Live',
                                    action: 'ajaxTotalVisitors',
                                    segment: segment
                                }, 'GET');
                                ajaxRequest.setCallback(function (r) {
                                    $(element).find("#visitsTotal").replaceWith(r);
                                });
                                ajaxRequest.send();
                            },
                            maxRows: 10,
                            fadeInSpeed: 600,
                            dataUrlParams: {
                                module: 'Live',
                                action: 'getLastVisitsStart',
                                segment: segment
                            }
                        });

                    });
                };
            }
        };
    }
})();
