/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        if (div.length && div.data('uiControlObject')) {
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

    DataTable_RowActions_SegmentVisitorLog.allowedExtraParamKeys = [
        'date',
        'period',
        'segment',
    ];

    // Filter a parsed extraParams object against the allowlist. Returns a new
    // object containing only keys in `allowedExtraParamKeys`. The visitor log
    // request only consumes these keys; everything else (idGoal, idDimension,
    // idSite, compare*, ...) is inert and is dropped so a crafted popover URL
    // cannot smuggle parameters into the Live.indexVisitorLog request.
    DataTable_RowActions_SegmentVisitorLog.filterAllowedExtraParams = function (parsed) {
        var allowed = DataTable_RowActions_SegmentVisitorLog.allowedExtraParamKeys;
        var result = {};

        Object.keys(parsed).forEach(function (key) {
            if (allowed.indexOf(key) !== -1) {
                result[key] = parsed[key];
            }
        });

        return result;
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.openPopover = function (apiMethod, segment, extraParams) {
        var urlParam = apiMethod + ':' + encodeURIComponent(segment) + ':' + encodeURIComponent(JSON.stringify(extraParams));

        broadcast.propagateNewPopoverParameter('RowAction', actionName + ':' + urlParam);
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.trigger = function (tr, e, subTableLabel) {
        var segment = getRawSegmentValueFromRow(tr);

        if (this.dataTable.param.segment) {
            segment = decodeURIComponent(this.dataTable.param.segment) + ';' + segment;
        }

        if (this.dataTable.props.segmented_visitor_log_segment_suffix) {
            segment = segment + ';' + this.dataTable.props.segmented_visitor_log_segment_suffix;
        }

        this.performAction(segment, tr, e);
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.performAction = function (segment, tr, e, originalRow) {

        var apiMethod = this.dataTable.param.module + '.' + this.dataTable.param.action;

        var extraParams = {};

        if (this.dataTable.param.date && this.dataTable.param.period) {
            extraParams = {date: this.dataTable.param.date, period: this.dataTable.param.period};
        }

        var paramOverride = $(originalRow || tr).data('param-override');
        if (typeof paramOverride !== 'object') {
            paramOverride = {};
        }
        $.extend(extraParams, DataTable_RowActions_SegmentVisitorLog.filterAllowedExtraParams(paramOverride));

        this.openPopover(apiMethod, segment, extraParams);
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.doOpenPopover = function (urlParam) {
        var urlParamParts = urlParam.split(':');

        var apiMethod = urlParamParts.shift();
        var segment = decodeURIComponent(urlParamParts.shift());

        var extraParamsString = urlParamParts.shift();
        var extraParams = {};

        try {
            extraParams = DataTable_RowActions_SegmentVisitorLog.filterAllowedExtraParams(
                JSON.parse(decodeURIComponent(extraParamsString))
            );
        } catch (e) {
            // Malformed payload: ignore and let defaults drive the popover.
        }

        SegmentedVisitorLog.show(apiMethod, segment, extraParams);
    };

    DataTable_RowActions_Registry.register({

        name: actionName,

        dataTableIcon: 'icon-segmented-visits-log',

        order: 30,

        dataTableIconTooltip: [
            _pk_translate('Live_RowActionTooltipTitle'),
            _pk_translate('Live_RowActionTooltipDefault')
        ],

        isAvailableOnReport: function (dataTableParams, undefined) {
            return !!piwik.visitorLogEnabled;
        },

        isAvailableOnRow: function (dataTableParams, tr) {
            var value = getRawSegmentValueFromRow(tr);
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
