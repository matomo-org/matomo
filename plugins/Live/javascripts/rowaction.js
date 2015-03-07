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

    function getLabelFromTr ($tr, apiMethod) {
        var label;

        if (apiMethod && 0 === apiMethod.indexOf('Actions.')) {
            // for now only use this for Actions... I know a hack :( Otherwise in Search Engines
            // it would show "http://www.searchenginename.org" instead of "SearchEngineName"
            label = $tr.attr('data-url-label');
        }

        if (!label) {
            label = $tr.find('.label .value').text();
        }

        if (label) {
            label = $.trim(label);
        }

        return label;
    }

    function getRawSegmentValueFromRow(tr)
    {
        return $(tr).attr('data-segment-filter');
    }

    function findTitleOfRowHavingRawSegmentValue(apiMethod, rawSegmentValue)
    {
        var $tr = $('[data-report="' + apiMethod + '"] tr[data-segment-filter="' + rawSegmentValue + '"]').first();

        return getLabelFromTr($tr, apiMethod);
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

        this.segmentComparison = '==';
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

        this.showVisitorLog(apiMethod, segment, extraParams);
    };

    DataTable_RowActions_SegmentVisitorLog.prototype.showVisitorLog = function (apiMethod, segment, extraParams) {

        var self = this;

        // open the popover
        var box = Piwik_Popover.showLoading('Segmented Visitor Log');
        box.addClass('segmentedVisitorLogPopover');

        function setPopoverTitle(apiMethod, index)
        {
            var dataTable = getDataTableFromApiMethod(apiMethod);

            if (!dataTable) {
                if (index < 15) {
                    // this is needed when the popover is opened before the dataTable is there which can often
                    // happen when opening the popover directly via URL (broadcast.popoverHandler)
                    setTimeout(function () {
                        setPopoverTitle(apiMethod, index + 1);
                    }, 150);
                }
                return;
            }

            var segmentName  = getDimensionFromApiMethod(apiMethod);
            var segmentValue = findTitleOfRowHavingRawSegmentValue(apiMethod, segment);

            segmentName  = piwikHelper.escape(segmentName);
            segmentName  = piwikHelper.htmlEntities(segmentName);
            segmentValue = piwikHelper.escape(segmentValue);
            segmentValue = piwikHelper.htmlEntities(segmentValue);
            segmentName  = segmentName.replace(/(&amp;)(#[0-9]{2,5};)/g, '&$2')
            segmentValue = segmentValue.replace(/(&amp;)(#[0-9]{2,5};)/g, '&$2')

            var title = _pk_translate('Live_SegmentedVisitorLogTitle', [segmentName, segmentValue]);

            Piwik_Popover.setTitle(title);
        }

        var callback = function (html) {
            Piwik_Popover.setContent(html);

            // remove title returned from the server
            var title = box.find('h2[piwik-enriched-headline]');
            var defaultTitle = title.text();

            if (title.size() > 0) {
                title.remove();
            }

            Piwik_Popover.setTitle(defaultTitle);

            setPopoverTitle(apiMethod, 0);
        };

        // prepare loading the popover contents
        var requestParams = {
            module: 'Live',
            action: 'indexVisitorLog',
            segment: segment,
            disableLink: 1,
            small: 1,
            hideProfileLink: 1
        };

        $.extend(requestParams, extraParams);

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams(requestParams, 'get');
        ajaxRequest.setCallback(callback);
        ajaxRequest.setFormat('html');
        ajaxRequest.send(false);
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