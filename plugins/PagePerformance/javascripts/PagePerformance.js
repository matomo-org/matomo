var PagePerformance = function() {

    function getDataTableFromApiMethod(apiMethod)
    {
        var div = $(require('piwik/UI').DataTable.getDataTableByReport(apiMethod));
        if (div.length && div.data('uiControlObject')) {
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

    function setPopoverTitle(apiMethod, label, index) {
        var dataTable = getDataTableFromApiMethod(apiMethod);

        if (!dataTable) {
            if (index < 15) {
                // this is needed when the popover is opened before the dataTable is there which can often
                // happen when opening the popover directly via URL (broadcast.popoverHandler)
                setTimeout(function () {
                    setPopoverTitle(apiMethod, label, index + 1);
                }, 150);
            }
            return;
        }

        var type = getDimensionFromApiMethod(apiMethod);

        var separator = ' > '; // LabelFilter::SEPARATOR_RECURSIVE_LABEL
        var labelParts = label.split(separator);
        for (var i = 0; i < labelParts.length; i++) {
            var labelPart = labelParts[i].replace('@', '');
            labelParts[i] = $.trim(decodeURIComponent(labelPart));
        }
        var delimiter = piwik.config.action_url_category_delimiter;
        if(apiMethod.indexOf('PageTitles') >= 0) {
            delimiter = piwik.config.action_title_category_delimiter;
        }
        label = labelParts.join(delimiter);

        // encode label for usage in .html()
        label = piwikHelper.htmlEntities(label);
        label = piwikHelper.escape(label);
        label = label.replace(/(&amp;)(#[0-9]{2,5};)/g, '&$2');

        var title = _pk_translate('PagePerformance_PagePerformanceTitle', [type, label]);

        Piwik_Popover.setTitle(title);
    }

    function show(apiMethod, label, isReportFlat) {

        // open the popover
        var box = Piwik_Popover.showLoading('Page performance report');
        box.addClass('pagePerformancePopover');


        var callback = function (html) {
            Piwik_Popover.setContent(html);

            // remove title returned from the server
            var title = box.find('.enrichedHeadline').closest('h2');
            var defaultTitle = title.text();

            if (title.length) {
                title.remove();
            }

            Piwik_Popover.setTitle(defaultTitle);

            setPopoverTitle(apiMethod, label, 0);
        };

        // prepare loading the popover contents
        var requestParams = {
            module: 'PagePerformance',
            action: 'indexPagePerformance',
            apiMethod: apiMethod,
            label: encodeURIComponent(label),
            flat: isReportFlat,
        };

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams(requestParams, 'get');
        ajaxRequest.setCallback(callback);
        ajaxRequest.setFormat('html');
        ajaxRequest.send();
    }

    return {
        show: show
    }
}();

