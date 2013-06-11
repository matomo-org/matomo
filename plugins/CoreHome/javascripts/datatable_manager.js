/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

    /**
     * The DataTableManager class manages the initialization of JS dataTable
     * instances. It's main purpose is to give each dataTable div a unique ID.
     * This is done in the browser since it's possible for any report to be
     * loaded via AJAX, some more than once.
     *
     * The singleton instance can be accessed via piwik.DataTableManager.
     */
    var DataTableManager = function () {
        this.nextId = 0;
    };

    DataTableManager.prototype = {

        /**
         * Gets the next available dataTable ID.
         *
         * @return {string}
         */
        getNextId: function () {
            this.nextId += 1;
            return 'dataTable_' + this.nextId;
        },

        /**
         * Gets the ID used for the last table or 0 if a DataTable hasn't been
         * initialized yet.
         */
        getLastId: function () {
            return 'dataTable_' + this.nextId;
        },

        /**
         * Initializes all uninitialized datatable elements. Uninitialized
         * datatable elements do not have an ID set.
         */
        initNewDataTables: function () {
            var self = this;

            // find each datatable that hasn't been initialized (has no id attribute),
            // and initialize it
            $('div.dataTable').each(function () {
                if (!$(this).attr('id')) {
                    var params = JSON.parse($(this).attr('data-params') || '{}');
                    var tableType = $(this).attr('data-table-type');
                    if (!tableType) {
                        tableType = 'dataTable';
                    }

                    // convert values in params that are arrays to comma separated string lists
                    for (var key in params) {
                        if (params[key] instanceof Array) {
                            params[key] = params[key].join(',');
                        }
                    }

                    self.initSingleDataTable(this, window[tableType], params);
                }
            });
        },

        /**
         * Initializes a single datatable element.
         *
         * @param {Element} domElem The DataTable div element.
         * @param {Function} klass The DataTable's JS class.
         * @param {Object} params The request params used.
         */
        initSingleDataTable: function (domElem, klass, params) {
            var newId = this.getNextId();

            $(domElem).attr('id', newId);

            var table = new klass();
            $(domElem).data('dataTableInstance', table);

            table.param = params;
            table.init(newId);

            // if the datatable has a graph, init the graph
            var graphElement = $('.piwik-graph', domElem);
            if (graphElement[0]) {
                this.initJQPlotGraph(graphElement, newId);
            }
        },

        /**
         * Initializes and renders a JQPlot graph contained in a
         * dataTable.
         *
         * @param {Element} graphElement The empty graph div element. Will
         *                               usually have the .piwik-graph class.
         * @param {String} dataTableId The ID of the containing datatable.
         */
        initJQPlotGraph: function (graphElement, dataTableId) {
            graphElement = $(graphElement);

            // set a unique ID for the graph element
            var graphId = dataTableId + 'Chart';
            graphElement.attr('id', graphId);

            var graphData;
            try {
                graphData = JSON.parse(graphElement.attr('data-data'));
            } catch (e) {
                console.error('JSON.parse Error: "' + e + "\" in:\n" + graphElement.attr('data-data'));
                return;
            }

            var plot = new JQPlot(graphData, dataTableId);

            // add external series toggle if it should be added
            var externalSeriesToggle = graphElement.attr('data-external-series-toggle');
            if (externalSeriesToggle) {
                plot.addExternalSeriesToggle(
                    window[externalSeriesToggle], // get the function w/ string name
                    graphId,
                    graphElement.attr('data-external-series-show-all') == 1
                );
            }

            // render the graph (setTimeout is required, otherwise the graph will not
            // render initially)
            setTimeout(function () {
                plot.render(graphElement.attr('data-graph-type'), graphId, {
                    noData: _pk_translate('General_NoDataForGraph_js'),
                    exportTitle: _pk_translate('General_ExportAsImage_js'),
                    exportText: _pk_translate('General_SaveImageOnYourComputer_js'),
                    metricsToPlot: _pk_translate('General_MetricsToPlot_js'),
                    metricToPlot: _pk_translate('General_MetricToPlot_js'),
                    recordsToPlot: _pk_translate('General_RecordsToPlot_js')
                });
            }, 1);
        },

        /**
         * Returns the first datatable div displaying a specific report.
         *
         * @param {string} report  The report, eg, UserSettings.getWideScreen
         * @return {Element} The datatable div displaying the report, or undefined if
         *                   it cannot be found.
         */
        getDataTableByReport: function (report) {
            var reportWithoutDot = report.replace('.', '');

            var result = undefined;
            $('.dataTable').each(function () {
                if ($(this).attr('data-report') == reportWithoutDot) {
                    result = this;
                    return false;
                }
            });
            return result;
        },

        /**
         * Returns the datatable instance of the first datatable div displaying
         * a specific report.
         *
         * @param {string} report  The report, eg, UserSettings.getWideScrren
         * @return {dataTable} The DataTable instance created for the element, if
         *                     the element can be found. undefined, if it can't be found.
         */
        getDataTableInstanceByReport: function (report) {
            var dataTableElement = this.getDataTableByReport(report);
            return dataTableElement ? $(dataTableElement).data('dataTableInstance') : undefined;
        }
    };

    piwik.DataTableManager = new DataTableManager();

}(jQuery));
