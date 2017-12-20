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

    piwikReportExport.$inject = ['$document', 'piwik', '$compile', '$timeout'];

    function piwikReportExport($document, piwik, $compile, $timeout){

        return {
            restrict: 'A',
            scope: {
                'reportTitle': '@',
                'requestParams': '@',
                'reportFormats': '@',
                'apiMethod': '@',
                'reportLimit': '@',
                'reportFormat': '@'
            },
            link: function(scope, element, attr) {

                scope.processExport = function() {

                    var dataTable = element.parents('[data-report]').data('uiControlObject');
                    var format = scope.reportFormat;
                    var method = scope.apiMethod;
                    var limit  = scope.reportLimit;
                    var params = scope.requestParams;

                    if (params && typeof params == String) {
                        params = JSON.parse(params)
                    } else {
                        params = {};
                    }

                    var segment = dataTable.param.segment;
                    var label = dataTable.param.label;
                    var idGoal = dataTable.param.idGoal;
                    var idDimension = dataTable.param.idDimension;
                    var param_date = dataTable.param.date;

                    if (format == 'RSS') {
                        param_date = 'last10';
                    }
                    if (typeof dataTable.param.dateUsedInGraph != 'undefined') {
                        param_date = dataTable.param.dateUsedInGraph;
                    }
                    var period = dataTable.param.period;

                    var formatsUseDayNotRange = piwik.config.datatable_export_range_as_day.toLowerCase();
                    if (!format) {
                        // eg export as image has no format
                        return;
                    }

                    if (formatsUseDayNotRange.indexOf(format.toLowerCase()) != -1
                        && dataTable.param.period == 'range') {
                        period = 'day';
                    }

                    // Below evolution graph, show daily exports
                    if(dataTable.param.period == 'range'
                        && dataTable.param.viewDataTable == "graphEvolution") {
                        period = 'day';
                    }

                    var str = 'index.php?module=API'
                        + '&method=' + method
                        + '&format=' + format
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

                    if (typeof dataTable.param.flat != "undefined") {
                        str += '&flat=' + (dataTable.param.flat == 0 ? '0' : '1');
                        if (typeof dataTable.param.include_aggregate_rows != "undefined" && dataTable.param.include_aggregate_rows == '1') {
                            str += '&include_aggregate_rows=1';
                        }
                        if (!dataTable.param.flat
                            && typeof dataTable.param.filter_pattern_recursive != "undefined"
                            && dataTable.param.filter_pattern_recursive) {
                            str += '&expanded=1';
                        }

                    } else {
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

                    Piwik_Popover.showLoading('Export', null, '600');
                    Piwik_Popover.setTitle(_pk_translate('General_Export') + ' ' + scope.reportTitle);

                    scope.availableReportFormats = JSON.parse(scope.reportFormats);

                    var elem = $document.find('#reportExport').eq(0);

                    if (!elem.length) {
                        elem = angular.element('<span ng-include="\'plugins/CoreHome/angularjs/report-export/reportexport.popover.html?cb=' + piwik.cacheBuster + '\'" id="reportExport"></span>');
                        $document.find('body').eq(0).append(elem);
                    }

                    $timeout(function(){
                        $compile(elem)(scope, function(compiled) {
                            Piwik_Popover.setContent(compiled);
                        });
                    }, 100);
                });
            }
        };
    }
})();
