var SegmentedVisitorLog = function() {

    function getDataTableFromApiMethod(apiMethod)
    {
        var div = $(require('piwik/UI').DataTable.getDataTableByReport(apiMethod));
        if (div.size() > 0 && div.data('uiControlObject')) {
            return div.data('uiControlObject');
        }
    }

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

    function getMetadataFromDataTable(dataTable)
    {
        if (dataTable) {

            return dataTable.getReportMetadata();
        }
    }

    function findTitleOfRowHavingRawSegmentValue(apiMethod, rawSegmentValue)
    {
        var $tr = $('[data-report="' + apiMethod + '"] tr[data-segment-filter="' + rawSegmentValue + '"]').first();

        return getLabelFromTr($tr, apiMethod);
    }

    function setPopoverTitle(apiMethod, segment, index) {
        var dataTable = getDataTableFromApiMethod(apiMethod);

        if (!dataTable) {
            if (index < 15) {
                // this is needed when the popover is opened before the dataTable is there which can often
                // happen when opening the popover directly via URL (broadcast.popoverHandler)
                setTimeout(function () {
                    setPopoverTitle(apiMethod, segment, index + 1);
                }, 150);
            }
            return;
        }

        var segmentName = getDimensionFromApiMethod(apiMethod);
        var segmentValue = findTitleOfRowHavingRawSegmentValue(apiMethod, segment);

        if (!segmentName || (segment && segment.indexOf(';') > 0)) {
            segmentName = _pk_translate('General_Segment');
            var segmentParts = segment.split(';');
            segmentValue = segmentParts.join(' ' + _pk_translate('General_And') + ' ');
        }

        segmentName = piwikHelper.escape(segmentName);
        segmentName = piwikHelper.htmlEntities(segmentName);
        segmentValue = piwikHelper.escape(segmentValue);
        segmentValue = piwikHelper.htmlEntities(segmentValue);
        segmentName = segmentName.replace(/(&amp;)(#[0-9]{2,5};)/g, '&$2');
        segmentValue = segmentValue.replace(/(&amp;)(#[0-9]{2,5};)/g, '&$2');

        var title = _pk_translate('Live_SegmentedVisitorLogTitle', [segmentName, segmentValue]);

        Piwik_Popover.setTitle(title);
    }

    function show(apiMethod, segment, extraParams) {

        // open the popover
        var box = Piwik_Popover.showLoading('Segmented Visitor Log');
        box.addClass('segmentedVisitorLogPopover');


        var callback = function (html) {
            Piwik_Popover.setContent(html);

            // remove title returned from the server
            var title = box.find('h2[piwik-enriched-headline]');
            var defaultTitle = title.text();

            if (title.size() > 0) {
                title.remove();
            }

            Piwik_Popover.setTitle(defaultTitle);

            setPopoverTitle(apiMethod, segment, 0);
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
    }

    return {
        show: show
    }
}();

