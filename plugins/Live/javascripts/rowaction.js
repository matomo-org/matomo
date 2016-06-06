/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This file registers the Overlay row action on the pages report.
 */

(function () {
    
    var actionName = 'SegmentVisitorLog';

    function getRawSegmentValueFromRow(tr)
    {
        return $(tr).attr('data-segment-filter');
    }

    function getDataTableFromApiMethod(apiMethod)
    {
        var div = $(require('piwik/UI').DataTable.getDataTableByReport(apiMethod));
        if (div.size() > 0 && div.data('uiControlObject')) {
            return div.data('uiControlObject');
        }
    }

    function getMetadataFromDataTable(dataTable)
    {
        if (dataTable) {

            return dataTable.getReportMetadata();
        }
    }

    function getDimensionFromApiMethod(apiMethod)
    {
        if (!apiMethod) {
            return;
        }

        var dataTable = getDataTableFromApiMethod(apiMethod);
        var metadata  = getMetadataFromDataTable(dataTable);

        if (metadata && metadata.dimension) {
            return metadata.dimension;
        }
    }

    function DataTable_RowActions_SegmentVisitorLog(dataTable) {
        this.dataTable = dataTable;
        this.actionName = actionName;

        // has to be overridden in subclasses
        this.trEventName = 'piwikTriggerSegmentVisitorLogAction';
    }

    DataTable_RowActions_SegmentVisitorLog.prototype = new DataTable_RowAction();

    DataTable_RowActions_SegmentVisitorLog.prototype.openPopover = function (apiMethod, segment, extraParams) {
        var urlParam = apiMethod + ':' + encodeURIComponent(segment) + ':' + encodeURIComponent(JSON.stringify(extraParams));

        broadcast.propagateNewPopoverParameter('RowAction', actionName + ':' + urlParam);
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.trigger = function (tr, e, subTableLabel) {
        var segment = getRawSegmentValueFromRow(tr);

        this.performAction(segment, tr, e);
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.performAction = function (segment, tr, e) {

        var apiMethod = this.dataTable.param.module + '.' + this.dataTable.param.action;

        this.openPopover(apiMethod, segment, {});
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.doOpenPopover = function (urlParam) {
        var urlParamParts = urlParam.split(':');

        var apiMethod = urlParamParts.shift();
        var segment = decodeURIComponent(urlParamParts.shift());

        var extraParamsString = urlParamParts.shift(),
            extraParams = {}; // 0/1 or "0"/"1"

        try {
            extraParams = JSON.parse(decodeURIComponent(extraParamsString));
        } catch (e) {
            // assume the parameter is an int/string describing whether to use multi row evolution
        }

        SegmentedVisitorLog.show(apiMethod, segment, extraParams);
    };

    DataTable_RowActions_Registry.register({

        name: actionName,

        dataTableIcon: 'plugins/Live/images/visitorlog.png',
        dataTableIconHover: 'plugins/Live/images/visitorlog-hover.png',

        order: 30,

        dataTableIconTooltip: [
            _pk_translate('Live_RowActionTooltipTitle'),
            _pk_translate('Live_RowActionTooltipDefault')
        ],

        isAvailableOnReport: function (dataTableParams, undefined) {
            return true;
        },

        isAvailableOnRow: function (dataTableParams, tr) {
            var value = getRawSegmentValueFromRow(tr)
            if ('undefined' === (typeof value)) {
                return false;
            }

            var reportTitle = null;

            var apiMethod = $(tr).parents('div.dataTable').last().attr('data-report');
            var dimension = getDimensionFromApiMethod(apiMethod);

            if (dimension) {
                reportTitle = _pk_translate('Live_RowActionTooltipWithDimension', [dimension])
            } else {
                reportTitle = _pk_translate('Live_RowActionTooltipDefault');
            }

            this.dataTableIconTooltip[1] = reportTitle;

            return true;
        },

        createInstance: function (dataTable, param) {
            if (dataTable !== null && typeof dataTable.segmentVisitorLogInstance != 'undefined') {
                return dataTable.segmentVisitorLogInstance;
            }

            if (dataTable === null && param) {
                // when segmented visitor log is triggered from the url (not a click on the data table)
                // we look for the data table instance in the dom
                var report = param.split(':')[0];
                var tempTable = getDataTableFromApiMethod(report);
                if (tempTable) {
                    dataTable = tempTable;
                    if (typeof dataTable.segmentVisitorLogInstance != 'undefined') {
                        return dataTable.segmentVisitorLogInstance;
                    }
                }
            }

            var instance = new DataTable_RowActions_SegmentVisitorLog(dataTable);
            if (dataTable !== null) {
                dataTable.segmentVisitorLogInstance = instance;
            }

            return instance;
        }

    });

})();