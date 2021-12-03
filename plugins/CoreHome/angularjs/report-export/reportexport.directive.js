/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *
 */
(function () {
    angular.module('piwikApp').directive('piwikReportExport', piwikReportExport);

    piwikReportExport.$inject = ['$document', 'piwik', '$compile', '$timeout', '$location', '$httpParamSerializerJQLike'];

    function piwikReportExport($document, piwik, $compile, $timeout, $location, $httpParamSerializerJQLike){

        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                var popoverParamBackup;

                scope.showUrl = false;

                scope.getExportLinkWithoutToken = function() {
                    return scope.getExportLink(false);
                }

                scope.getExportLink = function(withToken) {
                };

                element.on('click', function () {

                    popoverParamBackup = broadcast.getValueFromHash('popover');

                    var dataTable = scope.dataTable = element.parents('[data-report]').data('uiControlObject');
                    var popover   = Piwik_Popover.showLoading('Export');
                    var formats   = JSON.parse(scope.reportFormats);

                    scope.reportType          = 'default';
                    var reportLimit = dataTable.param.filter_limit;
                    if (scope.maxFilterLimit > 0) {
                        reportLimit = Math.min(reportLimit, scope.maxFilterLimit);
                    }
                    scope.reportLimit         = reportLimit > 0 ? reportLimit : 100;
                    // ng-model seems to have trouble setting $parent.reportLimit, so this was changed
                    // to use a property in a new object, limitContainer. it works. i'm not sure why.
                    scope.limitContainer      = { reportLimit: reportLimit };
                    scope.reportLimitAll      = reportLimit == -1 ? 'yes' : 'no';
                    scope.optionFlat          = dataTable.param.flat === true || dataTable.param.flat === 1 || dataTable.param.flat === "1";
                    scope.optionExpanded      = true;
                    scope.optionFormatMetrics = false;
                    scope.hasSubtables        = scope.optionFlat || dataTable.numberOfSubtables > 0;

                    scope.availableReportFormats = {
                        default: formats,
                        processed: {
                            'XML': formats['XML'],
                            'JSON': formats['JSON']
                        }
                    };
                    scope.availableReportTypes = {
                        default: _pk_translate('CoreHome_StandardReport'),
                        processed: _pk_translate('CoreHome_ReportWithMetadata')
                    };
                    scope.limitAllOptions = {
                        yes: _pk_translate('General_All'),
                        no: _pk_translate('CoreHome_CustomLimit')
                    };

                    scope.$watch('reportType', function (newVal, oldVal) {
                        if (!scope.availableReportFormats[newVal].hasOwnProperty(scope.reportFormat)) {
                            scope.reportFormat = 'XML';
                        }
                    }, true);

                    if (scope.maxFilterLimit > 0) {
                        scope.$watch('reportLimit', function (newVal, oldVal) {
                            if (parseInt(newVal, 10) > parseInt(scope.maxFilterLimit, 10)) {
                                scope.limitContainer.reportLimit = oldVal;
                            }
                        }, true);
                    }

                    var elem = $document.find('#reportExport').eq(0);

                    if (!elem.length) {
                        elem = angular.element('<span ng-include="\'plugins/CoreHome/angularjs/report-export/reportexport.popover.html?cb=' + piwik.cacheBuster + '\'" id="reportExport"></span>');
                    }

                    $compile(elem)(scope, function (compiled){
                        Piwik_Popover.setTitle(_pk_translate('General_Export') + ' ' + piwikHelper.htmlEntities(scope.reportTitle));
                        Piwik_Popover.setContent(compiled);

                        if (popoverParamBackup != '') {
                            Piwik_Popover.onClose(function(){
                                $timeout(function(){
                                    $location.search('popover', popoverParamBackup);

                                    $timeout(function () {
                                        angular.element(document).injector().get('$rootScope').$apply();
                                    }, 10);
                                }, 100);
                            });
                        }

                        $timeout(function(){
                            popover.dialog();
                            $('.exportFullUrl, .btn', popover).tooltip({
                                track: true,
                                show: false,
                                hide: false
                            });
                        }, 100);
                    });
                });
            }
        };
    }
})();
