/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *
 */
(function () {
    angular.module('piwikApp').directive('piwikReportExport', piwikReportExport);

    piwikReportExport.$inject = ['$document', 'piwik', '$compile', '$timeout', '$location'];

    function piwikReportExport($document, piwik, $compile, $timeout, $location){

        return {
            restrict: 'A',
            scope: {
                'reportTitle': '@',
                'requestParams': '@',
                'reportFormats': '@',
                'apiMethod': '@'
            },
            link: function(scope, element, attr) {

                var popoverParamBackup;

                scope.processExport = function() {

                    var dataTable = scope.dataTable;
                    var format    = scope.reportFormat;

                    if (!format) {
                        return;
                    }

                    var method = scope.apiMethod;
                    var limit  = scope.reportLimitAll == 'yes' ? -1 : scope.reportLimit;
                    var type   = scope.reportType;
                    var params = scope.requestParams;

                    if (params && typeof params == String) {
                        params = JSON.parse(params)
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

                    var str = 'index.php?module=API';

                    if (type == 'processed') {
                        var apiParams = method.split('.');
                        str += '&method=API.getProcessedReport';
                        str += '&apiModule=' + apiParams[0];
                        str += '&apiAction=' + apiParams[1];
                    } else {
                        str += '&method=' + method;
                    }

                    str += '&format=' + format
                        + '&idSite=' + dataTable.param.idSite
                        + '&period=' + period
                        + '&date=' + param_date
                        + ( typeof dataTable.param.filter_pattern != "undefined" ? '&filter_pattern=' + dataTable.param.filter_pattern : '')
                        + ( typeof dataTable.param.filter_pattern_recursive != "undefined" ? '&filter_pattern_recursive=' + dataTable.param.filter_pattern_recursive : '');

                    if ($.isPlainObject(params)) {
                        $.each(params, function (index, param) {
                            str += '&' + index + '=' + encodeURIComponent(param);
                        });
                    }

                    if (scope.optionFlat) {
                        str += '&flat=1';
                        if (typeof dataTable.param.include_aggregate_rows != "undefined" && dataTable.param.include_aggregate_rows == '1') {
                            str += '&include_aggregate_rows=1';
                        }
                    }

                    if (!scope.optionFlat && scope.optionExpanded) {
                        str += '&expanded=1';
                    }

                    if (dataTable.param.pivotBy) {
                        str += '&pivotBy=' + dataTable.param.pivotBy + '&pivotByColumnLimit=20';
                        if (dataTable.props.pivot_by_column) {
                            str += '&pivotByColumn=' + dataTable.props.pivot_by_column;
                        }
                    }
                    if (format == 'CSV' || format == 'TSV' || format == 'RSS') {
                        str += '&translateColumnNames=1&language=' + piwik.language;
                    }
                    if (typeof segment != 'undefined') {
                        str += '&segment=' + segment;
                    }
                    // Export Goals specific reports
                    if (typeof idGoal != 'undefined'
                        && idGoal != '-1') {
                        str += '&idGoal=' + idGoal;
                    }
                    // Export Dimension specific reports
                    if (typeof idDimension != 'undefined'
                        && idDimension != '-1') {
                        str += '&idDimension=' + idDimension;
                    }
                    if (label) {
                        label = label.split(',');

                        if (label.length > 1) {
                            for (var i = 0; i != label.length; ++i) {
                                str += '&label[]=' + encodeURIComponent(label[i]);
                            }
                        } else {
                            str += '&label=' + encodeURIComponent(label[0]);
                        }
                    }

                    var url = str + '&token_auth=' + piwik.token_auth;
                    url += '&filter_limit=' + limit;

                    window.open(url);
                };

                element.on('click', function () {

                    popoverParamBackup = broadcast.getValueFromHash('popover');

                    var dataTable = scope.dataTable = element.parents('[data-report]').data('uiControlObject');
                    var popover   = Piwik_Popover.showLoading('Export');
                    var formats   = JSON.parse(scope.reportFormats);

                    scope.reportType     = 'default';
                    scope.reportLimit    = dataTable.param.filter_limit > 0 ? dataTable.param.filter_limit : 100;
                    scope.reportLimitAll = dataTable.param.filter_limit == -1 ? 'yes' : 'no';
                    scope.optionFlat     = dataTable.param.flat;
                    scope.optionExpanded = 1;
                    scope.hasSubtables   = dataTable.param.flat == 1 || dataTable.numberOfSubtables > 0;

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

                    var elem = $document.find('#reportExport').eq(0);

                    if (!elem.length) {
                        elem = angular.element('<span ng-include="\'plugins/CoreHome/angularjs/report-export/reportexport.popover.html?cb=' + piwik.cacheBuster + '\'" id="reportExport"></span>');
                    }

                    $compile(elem)(scope, function (compiled){
                        Piwik_Popover.setTitle(_pk_translate('General_Export') + ' ' + scope.reportTitle);
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
                            popover.dialog({position: ['center', 'center']});
                        }, 100);
                    });
                });
            }
        };
    }
})();
