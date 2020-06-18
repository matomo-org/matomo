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
            scope: {
                'reportTitle': '@',
                'requestParams': '@',
                'reportFormats': '@',
                'apiMethod': '@',
                'maxFilterLimit': '@',
            },
            link: function(scope, element, attr) {

                var popoverParamBackup;

                scope.showUrl = false;

                scope.getExportLink = function() {

                    var dataTable = scope.dataTable;
                    var format    = scope.reportFormat;

                    if (!format) {
                        return;
                    }

                    var method = scope.apiMethod;
                    var limit  = scope.reportLimitAll == 'yes' ? -1 : scope.reportLimit;
                    var type   = scope.reportType;
                    var params = scope.requestParams;

                    if (params && typeof params == "string") {
                        params = JSON.parse(params);
                    } else {
                        params = {};
                    }

                    var segment     = dataTable.param.segment;
                    var label       = dataTable.param.label;
                    var idGoal      = dataTable.param.idGoal;
                    var idDimension = dataTable.param.idDimension;
                    var param_date  = dataTable.param.date;

                    if (format == 'RSS') {
                        param_date = 'last10';
                    }
                    if (typeof dataTable.param.dateUsedInGraph != 'undefined') {
                        param_date = dataTable.param.dateUsedInGraph;
                    }
                    var period = dataTable.param.period;

                    var formatsUseDayNotRange = piwik.config.datatable_export_range_as_day.toLowerCase();

                    if (formatsUseDayNotRange.indexOf(format.toLowerCase()) != -1
                        && dataTable.param.period == 'range') {
                        period = 'day';
                    }

                    // Below evolution graph, show daily exports
                    if(dataTable.param.period == 'range'
                        && dataTable.param.viewDataTable == "graphEvolution") {
                        period = 'day';
                    }

                    var exportUrlParams = {
                        module: 'API'
                    };

                    if (type == 'processed') {
                        var apiParams = method.split('.');
                        exportUrlParams.method = 'API.getProcessedReport';
                        exportUrlParams.apiModule = apiParams[0];
                        exportUrlParams.apiAction = apiParams[1];
                    } else {
                        exportUrlParams.method = method;
                    }

                    exportUrlParams.format = format;
                    exportUrlParams.idSite = dataTable.param.idSite;
                    exportUrlParams.period = period;
                    exportUrlParams.date = param_date;

                    if (dataTable.param.compareDates
                        && dataTable.param.compareDates.length
                    ) {
                        exportUrlParams.compareDates = dataTable.param.compareDates;
                        exportUrlParams.compare = '1';
                    }

                    if (dataTable.param.comparePeriods
                        && dataTable.param.comparePeriods.length
                    ) {
                        exportUrlParams.comparePeriods = dataTable.param.comparePeriods;
                        exportUrlParams.compare = '1';
                    }

                    if (dataTable.param.compareSegments
                        && dataTable.param.compareSegments.length
                    ) {
                        exportUrlParams.compareSegments = dataTable.param.compareSegments;
                        exportUrlParams.compare = '1';
                    }

                    if (typeof dataTable.param.filter_pattern != "undefined") {
                        exportUrlParams.filter_pattern = dataTable.param.filter_pattern;
                    }

                    if (typeof dataTable.param.filter_pattern_recursive != "undefined") {
                        exportUrlParams.filter_pattern_recursive = dataTable.param.filter_pattern_recursive;
                    }

                    if ($.isPlainObject(params)) {
                        $.each(params, function (index, param) {
                            if (param === true) {
                                param = 1;
                            } else if (param === false) {
                                param = 0;
                            }
                            exportUrlParams[index] = param;
                        });
                    }

                    if (scope.optionFlat) {
                        exportUrlParams.flat = 1;
                        if (typeof dataTable.param.include_aggregate_rows != "undefined" && dataTable.param.include_aggregate_rows == '1') {
                            exportUrlParams.include_aggregate_rows = 1;
                        }
                    }

                    if (!scope.optionFlat && scope.optionExpanded) {
                        exportUrlParams.expanded = 1;
                    }

                    if (scope.optionFormatMetrics) {
                        exportUrlParams.format_metrics = 1;
                    }

                    if (dataTable.param.pivotBy) {
                        exportUrlParams.pivotBy = dataTable.param.pivotBy;
                        exportUrlParams.pivotByColumnLimit = 20;

                        if (dataTable.props.pivot_by_column) {
                            exportUrlParams.pivotByColumn = dataTable.props.pivot_by_column;
                        }
                    }
                    if (format == 'CSV' || format == 'TSV' || format == 'RSS') {
                        exportUrlParams.translateColumnNames = 1;
                        exportUrlParams.language = piwik.language;
                    }
                    if (typeof segment != 'undefined') {
                        exportUrlParams.segment = decodeURIComponent(segment);
                    }
                    // Export Goals specific reports
                    if (typeof idGoal != 'undefined'
                        && idGoal != '-1') {
                        exportUrlParams.idGoal = idGoal;
                    }
                    // Export Dimension specific reports
                    if (typeof idDimension != 'undefined'
                        && idDimension != '-1') {
                        exportUrlParams.idDimension = idDimension;
                    }
                    if (label) {
                        label = label.split(',');

                        if (label.length > 1) {
                            exportUrlParams.label = label;
                        } else {
                            exportUrlParams.label = label[0];
                        }
                    }

                    exportUrlParams.token_auth = piwik.token_auth;
                    exportUrlParams.force_api_session = 1;
                    exportUrlParams.filter_limit = limit;

                    var currentUrl = $location.absUrl();
                    var urlParts = currentUrl.split('/');
                    urlParts.pop();
                    var url = urlParts.join('/');

                    return url + '/index.php?' + $httpParamSerializerJQLike(exportUrlParams);
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
                    scope.reportLimitAll      = reportLimit == -1 ? 'yes' : 'no';
                    scope.optionFlat          = dataTable.param.flat === true || dataTable.param.flat === 1 || dataTable.param.flat === "1";
                    scope.optionExpanded      = 1;
                    scope.optionFormatMetrics = 0;
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
                                scope.reportLimit = oldVal;
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
