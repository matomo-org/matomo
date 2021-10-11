/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {

    angular.module("piwikApp").directive('transitionExporter', transitionExporter);
    transitionExporter.$inject = ['$document', 'piwik', '$compile', '$timeout', '$location', '$httpParamSerializerJQLike', '$rootScope'];

    function transitionExporter($document, piwik, $compile, $timeout, $location, $httpParamSerializerJQLike, $rootScope) {

        return {
            restrict: 'A',
            link: function(scope, element) {

                scope.getExportLink = function() {

                    var exportUrlParams = {
                        module: 'API'
                    };

                    exportUrlParams.method = 'Transitions.getTransitionsForAction';
                    exportUrlParams.actionType = $rootScope.transitionExportParams['actionType'];
                    exportUrlParams.actionName = $rootScope.transitionExportParams['actionName'];

                    exportUrlParams.idSite = piwik.idSite;
                    exportUrlParams.period = piwik.period;
                    exportUrlParams.date = piwik.currentDateString;
                    exportUrlParams.format = scope.exportFormat;
                    exportUrlParams.token_auth = piwik.token_auth;
                    exportUrlParams.force_api_session = 1;

                    var currentUrl = $location.absUrl();
                    var urlParts = currentUrl.split('/');
                    urlParts.pop();
                    var url = urlParts.join('/');

                    return url + '/index.php?' + $httpParamSerializerJQLike(exportUrlParams);
                }

                $rootScope.$on('Transitions.dataChanged', function (event, params) {
                    $rootScope.transitionExportParams = params;
                });

                scope.onExportFormatChange = function (format) {
                    scope.exportFormat = format;
                }

                element.on('click', function () {

                    scope.exportFormat = 'JSON';
                    scope.exportFormatOptions = [
                        {key: 'JSON', value: 'JSON'},
                        {key: 'XML', value: 'XML'}
                    ];

                    if (!$rootScope.transitionExportParams) {
                        return;
                    }

                    scope.reportTitle = $rootScope.transitionExportParams['actionName'] + ' ' + _pk_translate('Transitions_Transitions');

                    this.popover = Piwik_Popover.showLoading(_pk_translate('General_Export'), self.actionName, 200);

                    var elem = $document.find('#transitionExport').eq(0);
                    if (!elem.length) {
                        elem = angular.element('<span id="transitionExport"></span>');
                        elem.attr('ng-include', "'plugins/Transitions/angularjs/transitionexporter/transitionexporter.popover.html?cb=' + encodeURIComponent(piwik.cacheBuster) + '\'");
                    }

                    $compile(elem)(scope, function (compiled){
                        Piwik_Popover.setTitle(_pk_translate('General_Export') + ' ' + piwikHelper.htmlEntities(scope.reportTitle));
                        Piwik_Popover.setContent(compiled);
                    });

                });
            }
        };
    }
})();
