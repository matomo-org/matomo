/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

//-----------------------------------------------------------------------------
//								DataTable
//-----------------------------------------------------------------------------

(function ($, require) {

var exports = require('piwik/UI'),
    UIControl = exports.UIControl;

/**
 * This class contains the client side logic for viewing and interacting with
 * Piwik datatables.
 *
 * The id attribute for DataTables is set dynamically by the initNewDataTables
 * method, and this class instance is stored using the jQuery $.data function
 * with the 'uiControlObject' key.
 *
 * To find a datatable element by report (ie, 'DevicesDetection.getBrowsers'),
 * use piwik.DataTable.getDataTableByReport.
 *
 * To get the dataTable JS instance (an instance of this class) for a
 * datatable HTML element, use $(element).data('uiControlObject').
 *
 * @constructor
 */
function DataTable(element) {
    UIControl.call(this, element);

    this.init();
}

DataTable._footerIconHandlers = {};

DataTable.initNewDataTables = function () {
    $('div.dataTable').each(function () {
        if (!$(this).attr('id')) {
            var tableType = $(this).attr('data-table-type') || 'DataTable',
                klass = require('piwik/UI')[tableType] || require(tableType);

            if (klass && $.isFunction(klass)) {
                var table = new klass(this);
            }
        }
    });
};

DataTable.registerFooterIconHandler = function (id, handler) {
    var handlers = DataTable._footerIconHandlers;

    if (handlers[id]) {
        setTimeout(function () { // fail gracefully
            throw new Exception("DataTable footer icon handler '" + id + "' is already being used.")
        }, 1);
        return;
    }

    handlers[id] = handler;
};

/**
 * Returns the first datatable div displaying a specific report.
 *
 * @param {string} report  The report, eg, UserLanguage.getLanguage
 * @return {Element} The datatable div displaying the report, or undefined if
 *                   it cannot be found.
 */
DataTable.getDataTableByReport = function (report) {
    var result = undefined;
    $('div.dataTable').each(function () {
        if ($(this).attr('data-report') == report) {
            result = this;
            return false;
        }
    });
    return result;
};

$.extend(DataTable.prototype, UIControl.prototype, {

    _init: function (domElem) {
        // initialize your dataTable in your plugin
    },

    //initialisation function
    init: function () {
        var domElem = this.$element;

        this.workingDivId = this._createDivId();
        domElem.attr('id', this.workingDivId);

        this.loadedSubDataTable = {};
        this.isEmpty = $('.pk-emptyDataTable', domElem).length > 0;
        this.bindEventsAndApplyStyle(domElem);
        this._init(domElem);
        this.initialized = true;
    },

    //function triggered when user click on column sort
    onClickSort: function (domElem) {
        var self = this;
        var newColumnToSort = $(domElem).attr('id');
        // we lookup if the column to sort was already this one, if it is the case then we switch from desc <-> asc
        if (self.param.filter_sort_column == newColumnToSort) {
            // toggle the sorted order
            if (this.param.filter_sort_order == 'asc') {
                self.param.filter_sort_order = 'desc';
            }
            else {
                self.param.filter_sort_order = 'asc';
            }
        }
        self.param.filter_offset = 0;
        self.param.filter_sort_column = newColumnToSort;

        if (!self.isDashboard()) {
            self.notifyWidgetParametersChange(domElem, {
                filter_sort_column: newColumnToSort,
                filter_sort_order: self.param.filter_sort_order
            });
        }

        self.reloadAjaxDataTable();
    },

    setGraphedColumn: function (columnName) {
        this.param.columns = columnName;
    },

    isWithinDialog: function (domElem) {
        return !!$(domElem).parents('.ui-dialog').length;
    },

    isDashboard: function () {
        return !!$('#dashboardWidgetsArea').length;
    },

    getReportMetadata: function () {
        return JSON.parse(this.$element.attr('data-report-metadata') || '{}');
    },

    //Reset DataTable filters (used before a reload or view change)
    resetAllFilters: function () {
        var self = this;
        var FiltersToRestore = {};
        var filters = [
            'filter_column',
            'filter_pattern',
            'filter_column_recursive',
            'filter_pattern_recursive',
            'enable_filter_excludelowpop',
            'filter_offset',
            'filter_limit',
            'filter_sort_column',
            'filter_sort_order',
            'disable_generic_filters',
            'columns',
            'flat',
            'include_aggregate_rows',
            'totalRows',
            'pivotBy',
            'pivotByColumn'
        ];

        for (var key = 0; key < filters.length; key++) {
            var value = filters[key];
            FiltersToRestore[value] = self.param[value];
            delete self.param[value];
        }

        return FiltersToRestore;
    },

    //Restores the filters to the values given in the array in parameters
    restoreAllFilters: function (FiltersToRestore) {
        var self = this;

        for (var key in FiltersToRestore) {
            self.param[key] = FiltersToRestore[key];
        }
    },

    //Translate string parameters to javascript builtins
    //'true' -> true, 'false' -> false
    //it simplifies condition tests in the code
    cleanParams: function () {
        var self = this;
        for (var key in self.param) {
            if (self.param[key] == 'true') self.param[key] = true;
            if (self.param[key] == 'false') self.param[key] = false;
        }
    },

    // Function called to trigger the AJAX request
    // The ajax request contains the function callback to trigger if the request is successful or failed
    // displayLoading = false When we don't want to display the Loading... DIV .loadingPiwik
    // for example when the script add a Loading... it self and doesn't want to display the generic Loading
    reloadAjaxDataTable: function (displayLoading, callbackSuccess) {
        var self = this;

        if (typeof displayLoading == "undefined") {
            displayLoading = true;
        }
        if (typeof callbackSuccess == "undefined") {
            callbackSuccess = function (response) {
                self.dataTableLoaded(response, self.workingDivId);
            };
        }

        if (displayLoading) {
            $('#' + self.workingDivId + ' .loadingPiwik').last().css('display', 'block');
        }

        $('#loadingError').hide();

        // when switching to display graphs, reset limit
        if (self && self.param && self.param.viewDataTable && String(self.param.viewDataTable).indexOf('graph') === 0) {
            delete self.param.filter_offset;
            delete self.param.filter_limit;
        }

        var container = $('#' + self.workingDivId + ' .piwik-graph');

        var params = {};
        for (var key in self.param) {
            if (typeof self.param[key] != "undefined" && self.param[key] != '')
                params[key] = self.param[key];
        }

        var ajaxRequest = new ajaxHelper();

        ajaxRequest.addParams(params, 'get');

        ajaxRequest.setCallback(
            function (response) {
                container.trigger('piwikDestroyPlot');
                container.off('piwikDestroyPlot');
                callbackSuccess(response);
            }
        );
        ajaxRequest.setErrorCallback(function (deferred, status) {
            if (status == 'abort' || !deferred || deferred.status < 400 || deferred.status >= 600) {
                return;
            }

            $('#' + self.workingDivId + ' .loadingPiwik').last().css('display', 'none');
            $('#loadingError').show();
        });
        ajaxRequest.setFormat('html');

        ajaxRequest.send(false);
    },

    // Function called when the AJAX request is successful
    // it looks for the ID of the response and replace the very same ID
    // in the current page with the AJAX response
    dataTableLoaded: function (response, workingDivId) {
        var content = $(response);

        var idToReplace = workingDivId || $(content).attr('id');
        var dataTableSel = $('#' + idToReplace);

        // if the current dataTable is located inside another datatable
        table = $(content).parents('table.dataTable');
        if (dataTableSel.parents('.dataTable').is('table')) {
            // we add class to the table so that we can give a different style to the subtable
            $(content).find('table.dataTable').addClass('subDataTable');
            $(content).find('.dataTableFeatures').addClass('subDataTable');

            //we force the initialisation of subdatatables
            dataTableSel.replaceWith(content);
        }
        else {
            dataTableSel.find('object').remove();
            dataTableSel.replaceWith(content);
        }

        content.trigger('piwik:dataTableLoaded');

        piwikHelper.lazyScrollTo(content[0], 400);

        return content;
    },

    /* This method is triggered when a new DIV is loaded, which happens
     - at the first loading of the page
     - after any AJAX loading of a DataTable

     This method basically add features to the DataTable,
     - such as column sorting, searching in the rows, displaying Next / Previous links, etc.
     - add styles to the cells and rows (odd / even styles)
     - modify some rows to add images if a span img is found, or add a link if a span urlLink is found
     - bind new events onclick / hover / etc. to trigger AJAX requests,
     nice hovertip boxes for truncated cells
     */
    bindEventsAndApplyStyle: function (domElem) {
        var self = this;
        self.cleanParams();
        self.handleSort(domElem);
        self.handleLimit(domElem);
        self.handleSearchBox(domElem);
        self.handleOffsetInformation(domElem);
        self.handleAnnotationsButton(domElem);
        self.handleEvolutionAnnotations(domElem);
        self.handleExportBox(domElem);
        self.applyCosmetics(domElem);
        self.handleSubDataTable(domElem);
        self.handleConfigurationBox(domElem);
        self.handleColumnDocumentation(domElem);
        self.handleRowActions(domElem);
		self.handleCellTooltips(domElem);
        self.handleRelatedReports(domElem);
        self.handleTriggeredEvents(domElem);
        self.handleColumnHighlighting(domElem);
        self.handleExpandFooter(domElem);
        self.setFixWidthToMakeEllipsisWork(domElem);
        self.handleSummaryRow(domElem);
    },

    setFixWidthToMakeEllipsisWork: function (domElem) {
        var self = this;

        function isWidgetized()
        {
            return -1 !== location.search.indexOf('module=Widgetize');
        }

        function getTableWidth(domElem)
        {
            var totalWidth      = $(domElem).width();
            var totalWidthTable = $('table.dataTable', domElem).width(); // fixes tables in dbstats, referrers, ...

            if (totalWidthTable < totalWidth) {
                totalWidth = totalWidthTable;
            }

            if (!totalWidth) {
                totalWidth = 0;
            }

            return parseInt(totalWidth, 10);
        }

        function setMaxTableWidthIfNeeded (domElem, maxTableWidth)
        {
            var tableWidth = getTableWidth(domElem);

            if (tableWidth <= maxTableWidth) {
                return;
            }

            if (isWidgetized() || self.isDashboard()) {
                return;
            }

            $(domElem).width(maxTableWidth);

            var parentDataTable = $(domElem).parent('.dataTable');
            if (parentDataTable && parentDataTable.length) {
                // makes sure dataTableWrapper and DataTable has same size => makes sure maxLabelWidth does not get
                // applied in getLabelWidth() since they will have the same size.
                parentDataTable.width(maxTableWidth);
            }
        }

        function getLabelWidth(domElem, tableWidth, minLabelWidth, maxLabelWidth)
        {
            var labelWidth = minLabelWidth;

            var columnsInFirstRow = $('tr:nth-child(1) td:not(.label)', domElem);

            var widthOfAllColumns = 0;
            columnsInFirstRow.each(function (index, column) {
                widthOfAllColumns += $(column).outerWidth();
            });

            if (tableWidth - widthOfAllColumns >= minLabelWidth) {
                labelWidth = tableWidth - widthOfAllColumns;
            } else if (widthOfAllColumns >= tableWidth) {
                labelWidth = tableWidth * 0.5;
            }

            var innerWidth = 0;
            var innerWrapper = domElem.find('.dataTableWrapper');
            if (innerWrapper && innerWrapper.length) {
                innerWidth = innerWrapper.width();
            }

            if (labelWidth > maxLabelWidth
                && !isWidgetized()
                && innerWidth !== domElem.width()
                && !self.isDashboard()) {
                labelWidth = maxLabelWidth; // prevent for instance table in Actions-Pages is not too wide
            }

            return parseInt(labelWidth, 10);
        }

        function getLabelColumnMinWidth(domElem)
        {
            var minWidth = 0;
            var minWidthHead = $('thead .first.label', domElem).css('minWidth');

            if (minWidthHead) {
                minWidth = parseInt(minWidthHead, 10);
            }

            var minWidthBody = $('tbody tr:nth-child(1) td.label', domElem).css('minWidth');

            if (minWidthBody) {
                minWidthBody = parseInt(minWidthBody, 10);
                if (minWidthBody && minWidthBody > minWidth) {
                    minWidth = minWidthBody;
                }
            }

            return parseInt(minWidth, 10);
        }

        function removePaddingFromWidth(domElem, labelWidth) {
            var maxPaddingLeft  = 0;
            var maxPaddingRight = 0;

            $('tbody tr td.label', domElem).each(function (i, node) {
                $node = $(node);

                var paddingLeft  = $node.css('paddingLeft');
                paddingLeft      = paddingLeft ? Math.round(parseFloat(paddingLeft)) : 0;
                var paddingRight = $node.css('paddingRight');
                paddingRight     = paddingRight ? Math.round(parseFloat(paddingLeft)) : 0;

                if (paddingLeft > maxPaddingLeft) {
                    maxPaddingLeft = paddingLeft;
                }
                if (paddingRight > maxPaddingRight) {
                    maxPaddingRight = paddingRight;
                }
            });

            labelWidth = labelWidth - maxPaddingLeft - maxPaddingRight;

            return labelWidth;
        }

        var minLabelWidth = 125;
        var maxLabelWidth = 440;

        setMaxTableWidthIfNeeded(domElem, 1200);
        var tableWidth = getTableWidth(domElem);
        var labelColumnMinWidth = getLabelColumnMinWidth(domElem);
        var labelColumnWidth    = getLabelWidth(domElem, tableWidth, 125, 440);

        if (labelColumnMinWidth > labelColumnWidth) {
            labelColumnWidth = labelColumnMinWidth;
        }

        labelColumnWidth = removePaddingFromWidth(domElem, labelColumnWidth);

        if (labelColumnWidth) {
            $('td.label', domElem).width(labelColumnWidth);
        }

        $('td span.label', domElem).each(function () { self.tooltip($(this)); });
    },

    handleLimit: function (domElem) {
            var tableRowLimits = piwik.config.datatable_row_limits,
            evolutionLimits =
            {
                day: [30, 60, 90, 180, 365, 500],
                week: [4, 12, 26, 52, 104, 500],
                month: [3, 6, 12, 24, 36, 120],
                year: [3, 5, 10]
            };

        var self = this;
        if (typeof self.parentId != "undefined" && self.parentId != '') {
            // no limit selector for subtables
            $('.limitSelection', domElem).remove();
            return;
        }

        // configure limit control
        var setLimitValue, numbers, limitParamName;
        if (self.param.viewDataTable == 'graphEvolution') {
            limitParamName = 'evolution_' + self.param.period + '_last_n';
            numbers = evolutionLimits[self.param.period] || tableRowLimits;

            setLimitValue = function (params, limit) {
                params[limitParamName] = limit;
            };
        }
        else {
            numbers = tableRowLimits;
            limitParamName = 'filter_limit';

            setLimitValue = function (params, value) {
                params.filter_limit = value;
                params.filter_offset = 0;
            };
        }

        function getFilterLimitAsString(limit) {
            if (limit == '-1') {
                return _pk_translate('General_All').toLowerCase();
            }
            return limit;
        }

        // setup limit control
        $('.limitSelection', domElem).append('<div><span value="'+ self.param[limitParamName] +'">' + getFilterLimitAsString(self.param[limitParamName]) + '</span></div><ul></ul>');

        if (self.props.show_limit_control) {
            $('.limitSelection ul', domElem).hide();
            for (var i = 0; i < numbers.length; i++) {
                $('.limitSelection ul', domElem).append('<li value="' + numbers[i] + '"><span>' + getFilterLimitAsString(numbers[i]) + '</span></li>');
            }
            $('.limitSelection ul li:last', domElem).addClass('last');

            if (!self.isEmpty) {
                var show = function() {
                    $('.limitSelection ul', domElem).show();
                    $('.limitSelection', domElem).addClass('visible');
                    $(document).on('mouseup.limitSelection', function(e) {
                        if (!$(e.target).closest('.limitSelection').length) {
                            hide();
                        }
                    });
                };
                var hide = function () {
                    $('.limitSelection ul', domElem).hide();
                    $('.limitSelection', domElem).removeClass('visible');
                    $(document).off('mouseup.limitSelection');
                };
                $('.limitSelection div', domElem).on('click', function () {
                    $('.limitSelection', domElem).is('.visible') ? hide() : show();
                });
                $('.limitSelection ul li', domElem).on('click', function (event) {
                    var limit = parseInt($(event.target).closest('li').attr('value'));

                    hide();
                    if (limit != self.param[limitParamName]) {
                        setLimitValue(self.param, limit);
                        $('.limitSelection>div>span', domElem).text( getFilterLimitAsString(limit)).attr('value', limit);
                        self.reloadAjaxDataTable();

                        var data = {};
                        data[limitParamName] = self.param[limitParamName];
                        self.notifyWidgetParametersChange(domElem, data);
                    }
                });
            }
            else {
                $('.limitSelection', domElem).toggleClass('disabled');
            }
        }
        else {
            $('.limitSelection', domElem).hide();
        }
    },

    // if sorting the columns is enabled, when clicking on a column,
    // - if this column was already the one used for sorting, we revert the order desc<->asc
    // - we send the ajax request with the new sorting information
    handleSort: function (domElem) {
        var self = this;

        if (self.props.enable_sort) {
            $('.sortable', domElem).off('click.dataTableSort').on('click.dataTableSort',
                function () {
                    $(this).off('click.dataTableSort');
                    self.onClickSort(this);
                }
            );
        }

        if (self.param.filter_sort_column) {
            // are we in a subdatatable?
            var currentIsSubDataTable = $(domElem).parent().hasClass('cellSubDataTable');
            var imageSortClassType = currentIsSubDataTable ? 'sortSubtable' : ''
            var imageSortWidth = 16;
            var imageSortHeight = 16;

            var sortOrder = self.param.filter_sort_order || 'desc';

            // we change the style of the column currently used as sort column
            // adding an image and the class columnSorted to the TD
            var head = $('th', domElem).filter(function () {
                return $(this).attr('id') == self.param.filter_sort_column;
            }).addClass('columnSorted');

            var sortIconHtml = '<span class="sortIcon ' + sortOrder + ' ' + imageSortClassType +'" width="' + imageSortWidth + '" height="' + imageSortHeight + '" />';

            var div = head.find('.thDIV');
            if (head.hasClass('first') || head.attr('id') == 'label') {
                div.append(sortIconHtml);
            } else {
                div.prepend(sortIconHtml);
            }
        }
    },

    //behaviour for the DataTable 'search box'
    handleSearchBox: function (domElem, callbackSuccess) {
        var self = this;

        var currentPattern = self.param.filter_pattern;
        if (typeof self.param.filter_pattern != "undefined"
            && self.param.filter_pattern.length > 0) {
            currentPattern = self.param.filter_pattern;
        }
        else if (typeof self.param.filter_pattern_recursive != "undefined"
            && self.param.filter_pattern_recursive.length > 0) {
            currentPattern = self.param.filter_pattern_recursive;
        }
        else {
            currentPattern = '';
        }
        currentPattern = piwikHelper.htmlDecode(currentPattern);

        var patternsToReplace = [{from: '?', to: '\\?'}, {from: '+', to: '\\+'}, {from: '*', to: '\\*'}]

        $.each(patternsToReplace, function (index, pattern) {
            if (0 === currentPattern.indexOf(pattern.to)) {
                currentPattern = pattern.from + currentPattern.substr(2);
            }
        });

        $('.dataTableSearchPattern', domElem)
            .css({display: 'block'})
            .each(function () {
                // when enter is pressed in the input field we submit the form
                $('.searchInput', this)
                    .on("keyup",
                    function (e) {
                        if (isEnterKey(e)) {
                            $(this).siblings(':submit').submit();
                        }
                    }
                )
                    .val(currentPattern)
                ;

                $(':submit', this).submit(
                    function () {
                        var keyword = $(this).siblings('.searchInput').val();
                        self.param.filter_offset = 0;

                        $.each(patternsToReplace, function (index, pattern) {
                            if (0 === keyword.indexOf(pattern.from)) {
                                keyword = pattern.to + keyword.substr(1);
                            }
                        });

                        if (self.param.search_recursive) {
                            self.param.filter_column_recursive = 'label';
                            self.param.filter_pattern_recursive = keyword;
                        }
                        else {
                            self.param.filter_column = 'label';
                            self.param.filter_pattern = keyword;
                        }

						delete self.param.totalRows;

                        self.reloadAjaxDataTable(true, callbackSuccess);
                    }
                );

                $(':submit', this)
                    .click(function () { $(this).submit(); })
                ;

                // in the case there is a searched keyword we display the RESET image
                if (currentPattern) {
                    var target = this;
                    var clearImg = $('<span class="searchReset">\
							<img src="plugins/CoreHome/images/reset_search.png" title="Clear" />\
							</span>')
                        .click(function () {
                            $('.searchInput', target).val('');
                            $(':submit', target).submit();
                        });
                    $('.searchInput', this).after(clearImg);

                }
            }
        );

        if (this.isEmpty && !currentPattern) {
            $('.dataTableSearchPattern', domElem).hide();
        }
    },

    //behaviour for '< prev' 'next >' links and page count
    handleOffsetInformation: function (domElem) {
        var self = this;

        $('.dataTablePages', domElem).each(
            function () {
                var offset = 1 + Number(self.param.filter_offset);
                var offsetEnd = Number(self.param.filter_offset) + Number(self.param.filter_limit);
                var totalRows = Number(self.param.totalRows);
                var offsetEndDisp = offsetEnd;

                if (self.param.keep_summary_row == 1) --totalRows;

                if (offsetEnd > totalRows) offsetEndDisp = totalRows;

                // only show this string if there is some rows in the datatable
                if (totalRows != 0) {
                    var str = sprintf(_pk_translate('CoreHome_PageOf'), offset + '-' + offsetEndDisp, totalRows);
                    $(this).text(str);
                } else {
                    $(this).hide();
                }
            }
        );

        var $next = $('.dataTableNext', domElem);

        // Display the next link if the total Rows is greater than the current end row
        $next.each(function () {
            var offsetEnd = Number(self.param.filter_offset)
                + Number(self.param.filter_limit);
            var totalRows = Number(self.param.totalRows);
            if (self.param.keep_summary_row == 1) --totalRows;
            if (offsetEnd < totalRows) {
                $(this).css('display', 'inline');
            }
        });
        // bind the click event to trigger the ajax request with the new offset
        $next.off('click');
        $next.click(function () {
            $(this).off('click');
            self.param.filter_offset = Number(self.param.filter_offset) + Number(self.param.filter_limit);
            self.reloadAjaxDataTable();
        });

        var $prev = $('.dataTablePrevious', domElem);

        // Display the previous link if the current offset is not zero
        $prev.each(function () {
            var offset = 1 + Number(self.param.filter_offset);
            if (offset != 1) {
                $(this).css('display', 'inline');
            }
        });

        // bind the click event to trigger the ajax request with the new offset
        // take care of the negative offset, we setup 0
        $prev.off('click');
        $prev.click(function () {
            $(this).off('click');
            var offset = Number(self.param.filter_offset) - Number(self.param.filter_limit);
            if (offset < 0) { offset = 0; }
            self.param.filter_offset = offset;
            self.param.previous = 1;
            self.reloadAjaxDataTable();
        });
    },

    handleEvolutionAnnotations: function (domElem) {
        var self = this;
        if (self.param.viewDataTable == 'graphEvolution'
            && $('.annotationView', domElem).length > 0) {
            // get dates w/ annotations across evolution period (have to do it through AJAX since we
            // determine placement using the elements created by jqplot)

            $('.dataTableFeatures', domElem).addClass('hasEvolution');

            piwik.annotations.api.getEvolutionIcons(
                self.param.idSite,
                self.param.date,
                self.param.period,
                self.param['evolution_' + self.param.period + '_last_n'],
                function (response) {
                    var annotations = $(response),
                        datatableFeatures = $('.dataTableFeatures', domElem),
                        noteSize = 16,
                        annotationAxisHeight = 30 // css height + padding + margin
                        ;

                    var annotationsCss = {left: 6}; // padding-left of .jqplot-graph element (in _dataTableViz_jqplotGraph.tpl)

                    // set position of evolution annotation icons
                    annotations.css(annotationsCss);

                    piwik.annotations.placeEvolutionIcons(annotations, domElem);

                    // add new section under axis
                    annotations.insertAfter($('.datatableRelatedReports', domElem));

                    // reposition annotation icons every time the graph is resized
                    $('.piwik-graph', domElem).on('resizeGraph', function () {
                        piwik.annotations.placeEvolutionIcons(annotations, domElem);
                    });

                    // on hover of x-axis, show note icon over correct part of x-axis
                    datatableFeatures.on('mouseenter', '.evolution-annotations>span', function () {
                        $(this).css('opacity', 1);
                    });

                    datatableFeatures.on('mouseleave', '.evolution-annotations>span', function () {
                        if ($(this).attr('data-count') == 0) // only hide if there are no annotations for this note
                        {
                            $(this).css('opacity', 0);
                        }
                    });

                    // when clicking an annotation, show the annotation viewer for that period
                    datatableFeatures.on('click', '.evolution-annotations>span', function () {
                        var spanSelf = $(this),
                            date = spanSelf.attr('data-date'),
                            oldDate = $('.annotation-manager', domElem).attr('data-date');
                        if (date) {
                            var period = self.param.period;
                            if (period == 'range') {
                                period = 'day';
                            }

                            piwik.annotations.showAnnotationViewer(
                                domElem,
                                self.param.idSite,
                                date,
                                period,
                                undefined, // lastN
                                function (manager) {
                                    manager.attr('data-is-range', 0);
                                    $('.annotationView img', domElem)
                                        .attr('title', _pk_translate('Annotations_IconDesc'));

                                    var viewAndAdd = _pk_translate('Annotations_ViewAndAddAnnotations'),
                                        hideNotes = _pk_translate('Annotations_HideAnnotationsFor');

                                    // change the tooltip of the previously clicked evolution icon (if any)
                                    if (oldDate) {
                                        $('span', annotations).each(function () {
                                            if ($(this).attr('data-date') == oldDate) {
                                                $(this).attr('title', sprintf(viewAndAdd, oldDate));
                                                return false;
                                            }
                                        });
                                    }

                                    // change the tooltip of the clicked evolution icon
                                    if (manager.is(':hidden')) {
                                        spanSelf.attr('title', sprintf(viewAndAdd, date));
                                    }
                                    else {
                                        spanSelf.attr('title', sprintf(hideNotes, date));
                                    }
                                }
                            );
                        }
                    });

                    // when hover over annotation in annotation manager, highlight the annotation
                    // icon
                    var runningAnimation = null;
                    domElem.on('mouseenter', '.annotation', function (e) {
                        var date = $(this).attr('data-date');

                        // find the icon for this annotation
                        var icon = $();
                        $('span', annotations).each(function () {
                            if ($(this).attr('data-date') == date) {
                                icon = $('img', this);
                                return false;
                            }
                        });

                        if (icon[0] == runningAnimation) // if the animation is already running, do nothing
                        {
                            return;
                        }

                        // stop ongoing animations
                        $('span', annotations).each(function () {
                            $('img', this).removeAttr('style');
                        });

                        // start a bounce animation
                        icon.effect("bounce", {times: 1, distance: 10}, 1000);
                        runningAnimation = icon[0];
                    });

                    // reset running animation item when leaving annotations list
                    domElem.on('mouseleave', '.annotations', function (e) {
                        runningAnimation = null;
                    });

                    self.$element.trigger('piwik:annotationsLoaded');
                }
            );
        }
    },

    handleAnnotationsButton: function (domElem) {
        var self = this;
        if (self.param.idSubtable) // no annotations for subtables, just whole reports
        {
            return;
        }

        // show the annotations view on click
        $('.annotationView', domElem).click(function () {
            var annotationManager = $('.annotation-manager', domElem);

            if (annotationManager.length > 0
                && annotationManager.attr('data-is-range') == 1) {
                if (annotationManager.is(':hidden')) {
                    annotationManager.slideDown('slow'); // showing
                    $('img', this).attr('title', _pk_translate('Annotations_IconDescHideNotes'));
                }
                else {
                    annotationManager.slideUp('slow'); // hiding
                    $('img', this).attr('title', _pk_translate('Annotations_IconDesc'));
                }
            }
            else {
                // show the annotation viewer for the whole date range
                var lastN = self.param['evolution_' + self.param.period + '_last_n'];
                piwik.annotations.showAnnotationViewer(
                    domElem,
                    self.param.idSite,
                    self.param.date,
                    self.param.period,
                    lastN,
                    function (manager) {
                        manager.attr('data-is-range', 1);
                    }
                );

                // change the tooltip of the view annotation icon
                $('img', this).attr('title', _pk_translate('Annotations_IconDescHideNotes'));
            }
        });
    },

    // DataTable view box (simple table, all columns table, Goals table, pie graph, tag cloud, graph, ...)
    handleExportBox: function (domElem) {
        var self = this;
        if (self.param.idSubtable) {
            // no view box for subtables
            return;
        }

        // When the (+) image is hovered, the export buttons are displayed
        $('.dataTableFooterIconsShow', domElem)
            .show()
            .hover(function () {
                $(this).fadeOut('slow');
                $('.exportToFormatIcons', $(this).parent()).show('slow');
            }, function () {}
        );

        //footer arrow position element name
        self.jsViewDataTable = self.param.viewDataTable;

        $('.tableAllColumnsSwitch a', domElem).show();

        $('.dataTableFooterIcons .tableIcon', domElem).click(function () {
            var id = $(this).attr('data-footer-icon-id');
            if (!id) {
                return;
            }

            var handler = DataTable._footerIconHandlers[id];
            if (!handler) {
                handler = DataTable._footerIconHandlers['table'];
            }

            handler(self, id);
        });

        //Graph icon Collapsed functionality
        self.currentGraphViewIcon = 0;
        self.graphViewEnabled = 0;
        self.graphViewStartingThreads = 0;
        self.graphViewStartingKeep = false; //show keep flag

        //define collapsed icons
        $('.tableGraphCollapsed a', domElem)
            .each(function (i) {
                if (self.jsViewDataTable == $(this).attr('data-footer-icon-id')) {
                    self.currentGraphViewIcon = i;
                    self.graphViewEnabled = true;
                }
            })
            .each(function (i) {
                if (self.currentGraphViewIcon != i) $(this).hide();
            });

        $('.tableGraphCollapsed', domElem).hover(
            function () {
                //Graph icon onmouseover
                if (self.graphViewStartingThreads > 0) return self.graphViewStartingKeep = true; //exit if animation is not finished
                $(this).addClass('tableIconsGroupActive');
                $('a', this).each(function (i) {
                    if (self.currentGraphViewIcon != i || self.graphViewEnabled) {
                        self.graphViewStartingThreads++;
                    }
                    if (self.currentGraphViewIcon != i) {
                        //show other icons
                        $(this).show('fast', function () {self.graphViewStartingThreads--});
                    }
                    else if (self.graphViewEnabled) {
                        self.graphViewStartingThreads--;
                    }
                });
                self.exportToFormatHide(domElem);
            },
            function() {
                //Graph icon onmouseout
                if (self.graphViewStartingKeep) return self.graphViewStartingKeep = false; //exit while icons animate
                $('a', this).each(function (i) {
                    if (self.currentGraphViewIcon != i) {
                        //hide other icons
                        $(this).hide('fast');
                    }
                });
                $(this).removeClass('tableIconsGroupActive');
            }
        );

        //handle exportToFormat icons
        self.exportToFormat = null;
        $('.exportToFormatIcons a', domElem).click(function () {
            self.exportToFormat = {};
            self.exportToFormat.lastActiveIcon = this;
            self.exportToFormat.target = $(this).parent().siblings('.exportToFormatItems').show('fast');
            self.exportToFormat.obj = $(this).hide();
        });

        //close exportToFormat onClickOutside
        $('body').on('mouseup', function (e) {
            if (self.exportToFormat) {
                self.exportToFormatHide(domElem);
            }
        });

        $('.exportToFormatItems a', domElem)
            // prevent click jacking attacks by dynamically adding the token auth when the link is clicked
            .click(function () {
                $(this).attr('href', function () {
                    var url = $(this).attr('href') + '&token_auth=' + piwik.token_auth;

                    var limit = $('.limitSelection>div>span', domElem).attr('value');
                    var defaultLimit = $(this).attr('filter_limit');
                    if (!limit || 'undefined' === limit || defaultLimit == -1) {
                        limit = defaultLimit;
                    }
                    url += '&filter_limit=' + limit;

                    return url;
                })
            })
            .attr('href', function () {
                var format = $(this).attr('format');
                var method = $(this).attr('methodToCall');
                var params = $(this).attr('requestParams');

                if (params) {
                    params = JSON.parse(params)
                } else {
                    params = {};
                }

                var segment = self.param.segment;
                var label = self.param.label;
                var idGoal = self.param.idGoal;
                var idDimension = self.param.idDimension;
                var param_date = self.param.date;
                var date = $(this).attr('date');
                if (typeof date != 'undefined') {
                    param_date = date;
                }
                if (typeof self.param.dateUsedInGraph != 'undefined') {
                    param_date = self.param.dateUsedInGraph;
                }
                var period = self.param.period;

                var formatsUseDayNotRange = piwik.config.datatable_export_range_as_day.toLowerCase();
                if (formatsUseDayNotRange.indexOf(format.toLowerCase()) != -1
                    && self.param.period == 'range') {
                    period = 'day';
                }

                // Below evolution graph, show daily exports
                if(self.param.period == 'range'
                    && self.param.viewDataTable == "graphEvolution") {
                    period = 'day';
                }

                var str = 'index.php?module=API'
                    + '&method=' + method
                    + '&format=' + format
                    + '&idSite=' + self.param.idSite
                    + '&period=' + period
                    + '&date=' + param_date
                    + ( typeof self.param.filter_pattern != "undefined" ? '&filter_pattern=' + self.param.filter_pattern : '')
                    + ( typeof self.param.filter_pattern_recursive != "undefined" ? '&filter_pattern_recursive=' + self.param.filter_pattern_recursive : '');

                if ($.isPlainObject(params)) {
                    $.each(params, function (index, param) {
                        str += '&' + index + '=' + encodeURIComponent(param);
                    });
                }

                if (typeof self.param.flat != "undefined") {
                    str += '&flat=' + (self.param.flat == 0 ? '0' : '1');
                    if (typeof self.param.include_aggregate_rows != "undefined" && self.param.include_aggregate_rows == '1') {
                        str += '&include_aggregate_rows=1';
                    }
                    if (!self.param.flat
                        && typeof self.param.filter_pattern_recursive != "undefined"
                        && self.param.filter_pattern_recursive) {
                        str += '&expanded=1';
                    }

                } else {
                    str += '&expanded=1';
                }
                if (self.param.pivotBy) {
                    str += '&pivotBy=' + self.param.pivotBy + '&pivotByColumnLimit=20';
                    if (self.props.pivot_by_column) {
                        str += '&pivotByColumn=' + self.props.pivot_by_column;
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
                return str;
            }
        );
    },

    exportToFormatHide: function (domElem, noAnimation) {
        var self = this;
        if (self.exportToFormat) {
            var animationSpeed = noAnimation ? 0 : 'fast';
            self.exportToFormat.target.hide(animationSpeed);
            self.exportToFormat.obj.show(animationSpeed);
            self.exportToFormat = null;
        }
    },

    handleConfigurationBox: function (domElem, callbackSuccess) {
        var self = this;

        if (typeof self.parentId != "undefined" && self.parentId != '') {
            // no manipulation when loading subtables
            return;
        }

        if ((typeof self.numberOfSubtables == 'undefined' || self.numberOfSubtables == 0)
            && (typeof self.param.flat == 'undefined' || self.param.flat != 1)) {
            // if there are no subtables, remove the flatten action
            $('.dataTableFlatten', domElem).parent().remove();
        }

        var ul = $('div.tableConfiguration ul', domElem);
        function hideConfigurationIcon() {
            // hide the icon when there are no actions available or we're not in a table view
            $('div.tableConfiguration', domElem).remove();
        }

        if (ul.find('li').size() == 0) {
            hideConfigurationIcon();
            return;
        }

        var icon = $('a.tableConfigurationIcon', domElem);
        icon.click(function () { return false; });
        var iconHighlighted = false;

        ul.find('li:first').addClass('first');
        ul.find('li:last').addClass('last');
        ul.prepend('<li class="firstDummy"></li>');

        // open and close the box
        var open = function () {
            self.exportToFormatHide(domElem, true);
            ul.addClass('open');
            icon.css('opacity', 1);
        };
        var close = function () {
            ul.removeClass('open');
            icon.css('opacity', icon.hasClass('highlighted') ? .85 : .6);
        };
        $('div.tableConfiguration', domElem).hover(open, close);

        var generateClickCallback = function (paramName, callbackAfterToggle, setParamCallback) {
            return function () {
                close();
                if (setParamCallback) {
                    var data = setParamCallback();
                } else {
                    self.param[paramName] = (1 - self.param[paramName]) + '';
                    var data = {};
                }
                self.param.filter_offset = 0;
                delete self.param.totalRows;
                if (callbackAfterToggle) callbackAfterToggle();
                self.reloadAjaxDataTable(true, callbackSuccess);
                data[paramName] = self.param[paramName];
                self.notifyWidgetParametersChange(domElem, data);
            };
        };

        var getText = function (text, addDefault, replacement) {
            if (/(%(.\$)?s+)/g.test(_pk_translate(text))) {
                var values = ['<br /><span class="action">&raquo; '];
                if(replacement) {
                    values.push(replacement);
                }
                text = _pk_translate(text, values);
                if (addDefault) text += ' (' + _pk_translate('CoreHome_Default') + ')';
                text += '</span>';
                return text;
            }
            return _pk_translate(text);
        };

        var setText = function (el, paramName, textA, textB) {
            if (typeof self.param[paramName] != 'undefined' && self.param[paramName] == 1) {
                $(el).html(getText(textA, true));
                iconHighlighted = true;
            }
            else {
                self.param[paramName] = 0;
                $(el).html(getText(textB));
            }
        };

        // handle low population
        $('.dataTableExcludeLowPopulation', domElem)
            .each(function () {
                // Set the text, either "Exclude low pop" or "Include all"
                if (typeof self.param.enable_filter_excludelowpop == 'undefined') {
                    self.param.enable_filter_excludelowpop = 0;
                }
                if (Number(self.param.enable_filter_excludelowpop) != 0) {
                    var string = getText('CoreHome_IncludeRowsWithLowPopulation', true);
                    self.param.enable_filter_excludelowpop = 1;
                    iconHighlighted = true;
                }
                else {
                    var string = getText('CoreHome_ExcludeRowsWithLowPopulation');
                    self.param.enable_filter_excludelowpop = 0;
                }
                $(this).html(string);
            })
            .click(generateClickCallback('enable_filter_excludelowpop'));

        // handle flatten
        $('.dataTableFlatten', domElem)
            .each(function () {
                setText(this, 'flat', 'CoreHome_UnFlattenDataTable', 'CoreHome_FlattenDataTable');
            })
            .click(generateClickCallback('flat'));

        $('.dataTableIncludeAggregateRows', domElem)
            .each(function () {
                setText(this, 'include_aggregate_rows', 'CoreHome_DataTableExcludeAggregateRows',
                    'CoreHome_DataTableIncludeAggregateRows');
            })
            .click(generateClickCallback('include_aggregate_rows', function () {
                if (self.param.include_aggregate_rows == 1) {
                    // when including aggregate rows is enabled, we remove the sorting
                    // this way, the aggregate rows appear directly before their children
                    self.param.filter_sort_column = '';
                    self.notifyWidgetParametersChange(domElem, {filter_sort_column: ''});
                }
            }));

        // handle pivot by
        $('.dataTablePivotBySubtable', domElem)
            .each(function () {
                if (self.param.pivotBy
                    && self.param.pivotBy != '0'
                ) {
                    $(this).html(getText('CoreHome_UndoPivotBySubtable', true));
                    iconHighlighted = true;
                } else {
                    var optionLabelText = getText('CoreHome_PivotBySubtable', false, self.props.pivot_dimension_name);
                    $(this).html(optionLabelText);
                }
            })
            .click(generateClickCallback('pivotBy', null, function () {
                if (self.param.pivotBy
                    && self.param.pivotBy != '0'
                ) {
                    self.param.pivotBy = '0'; // set to '0' so it will be sent in the request and override the saved param
                    self.param.pivotByColumn = '0';
                } else {
                    self.param.pivotBy = self.props.pivot_by_dimension;
                    if (self.props.pivot_by_column) {
                        self.param.pivotByColumn = self.props.pivot_by_column;
                    }
                }

                // remove sorting so it will default to first column in table
                self.param.filter_sort_column = '';
                return {filter_sort_column: ''};
            }));

        // handle highlighted icon
        if (iconHighlighted) {
            icon.addClass('highlighted');
        }
        close();

        if (!iconHighlighted
            && !(self.param.viewDataTable == 'table'
            || self.param.viewDataTable == 'tableAllColumns'
            || self.param.viewDataTable == 'tableGoals')) {
            hideConfigurationIcon();
            return;
        }

        // fix a css bug of ie7
        if (document.all && !window.opera && window.XMLHttpRequest) {
            window.setTimeout(function () {
                open();
                var width = 0;
                ul.find('li').each(function () {
                    width = Math.max(width, $(this).width());
                });
                if (width > 0) {
                    ul.find('li').width(width);
                }
                close();
            }, 400);
        }
    },

    // Tell parent widget that the parameters of this table was updated,
    notifyWidgetParametersChange: function (domWidget, parameters) {
        var widget = $(domWidget).closest('[widgetId]');
        // trigger setParameters event on base element

        if (widget && widget.length) {
            widget.trigger('setParameters', parameters);
        } else {

            var reportId = $(domWidget).closest('[data-report]').attr('data-report');

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams({
                module: 'CoreHome',
                action: 'saveViewDataTableParameters',
                report_id: reportId
            }, 'get');
            ajaxRequest.addParams({
                parameters: JSON.stringify(parameters)
            }, 'post');
            ajaxRequest.setCallback(function () {});
            ajaxRequest.setFormat('html');
            ajaxRequest.send(false);
        }
    },

    tooltip: function (domElement) {

        function isTextEllipsized($element)
        {
            return !($element && $element[0] && $element.outerWidth() >= $element[0].scrollWidth);
        }

        var $domElement = $(domElement);

        if ($domElement.data('tooltip') == 'enabled') {
            return;
        }

        $domElement.data('tooltip', 'enabled');

        if (!isTextEllipsized($domElement)) {
            return;
        }

        var customToolTipText = $domElement.attr('title') || $domElement.text();

        if (customToolTipText) {
            $domElement.attr('title', customToolTipText);
        }

        $domElement.tooltip({
            track: true,
            show: false,
            hide: false
        });
    },

    //Apply some miscelleaneous style to the DataTable
    applyCosmetics: function (domElem) {
        var self = this;

        // Add some styles on the cells even/odd
        // label (first column of a data row) or not
        $("th:first-child", domElem).addClass('label');
        $("td:first-child", domElem).addClass('label');
        $("tr td", domElem).addClass('column');
    },

    handleExpandFooter: function (domElem) {
        var footerIcons = $('.dataTableFooterIcons', domElem);

        if (!footerIcons.length) {
            return;
        }

        if (this.isWithinDialog(domElem)) {
            $('.dataTableFeatures', domElem).addClass('expanded');
        }

        var self = this;
        function toggleFooter(event)
        {
            if (self.isWithinDialog(domElem)) {
                return;
            }

            var icons = $('.dataTableFooterIcons', domElem);
            $('.dataTableFeatures', domElem).toggleClass('expanded');

            if (event && event.doNotNotifyChange) {
                return;
            }

            self.notifyWidgetParametersChange(domElem, {
                isFooterExpandedInDashboard: icons.is(':visible')
            });
        }

        var moveNode = $('.datatableFooterMessage', domElem);
        if (!moveNode.length) {
            moveNode = $('.datatableRelatedReports', domElem);
        }

        footerIcons.after(moveNode);

        $('.expandDataTableFooterDrawer', domElem).after(footerIcons);

        var controls   = $('.controls', domElem);
        var footerWrap = $('.dataTableFooterWrap', domElem);
        if (controls.length && footerWrap.length) {
            $('.dataTableFooterWrap', domElem).before(controls);
        }

        var loadingPiwikBelow = $('.loadingPiwikBelow', domElem);
        if (loadingPiwikBelow.length) {
            loadingPiwikBelow.insertBefore(moveNode);
        }

        if (this.param.isFooterExpandedInDashboard) {
            toggleFooter({doNotNotifyChange: true});
        }

        var $nodes = $('.foldDataTableFooterDrawer, .expandDataTableFooterDrawer', domElem);
        $nodes.off('click');
        $nodes.on('click', toggleFooter);
    },

    handleColumnHighlighting: function (domElem) {

        var maxWidth = {};
        var currentNthChild = null;
        var self = this;

        // higlight all columns on hover
        $('td', domElem).hover(
            function() {

                if ($(this).hasClass('label')) {
                    return;
                }

                var table    = $(this).closest('table');
                var nthChild = $(this).parent('tr').children().index($(this)) + 1;
                var rows     = $('> tbody > tr', table);

                if (!maxWidth[nthChild]) {
                    maxWidth[nthChild] = 0;
                    rows.find("td:nth-child(" + (nthChild) + ").column .value").each(function (index, element) {
                        var width = $(element).width();
                        if (width > maxWidth[nthChild]) {
                            maxWidth[nthChild] = width;
                        }
                    });
                    rows.find("td:nth-child(" + (nthChild) + ").column .value").each(function (index, element) {
                        $(element).css({width: maxWidth[nthChild], display: 'inline-block'});
                    });
                }

                if (currentNthChild === nthChild) {
                    return;
                }

                currentNthChild = nthChild;

                rows.children("td:nth-child(" + (nthChild) + ")").addClass('highlight');
                self.repositionRowActions($(this).parent('tr'));
            },
            function(event) {

                var table    = $(this).closest('table');
                var tr       = $(this).parent('tr').children();
                var nthChild = $(this).parent('tr').children().index($(this));
                var targetTd = $(event.relatedTarget).closest('td');
                var nthChildTarget = targetTd.parent('tr').children().index(targetTd);

                if (nthChild == nthChildTarget) {
                    return;
                }

                currentNthChild = null;

                var rows     = $('tr', table);
                rows.find("td:nth-child(" + (nthChild + 1) + ")").removeClass('highlight');
            }
        );
    },

    //behaviour for 'nested DataTable' (DataTable loaded on a click on a row)
    handleSubDataTable: function (domElem) {
        var self = this;
        // When the TR has a subDataTable class it means that this row has a link to a subDataTable
        self.numberOfSubtables = $('tr.subDataTable', domElem)
            .click(
            function () {
                // get the idSubTable
                var idSubTable = $(this).attr('id');
                var divIdToReplaceWithSubTable = 'subDataTable_' + idSubTable;

                // if the subDataTable is not already loaded
                if (typeof self.loadedSubDataTable[divIdToReplaceWithSubTable] == "undefined") {
                    var numberOfColumns = $(this).children().length;

                    // at the end of the query it will replace the ID matching the new HTML table #ID
                    // we need to create this ID first
                    $(this).after(
                        '<tr>' +
                            '<td colspan="' + numberOfColumns + '" class="cellSubDataTable">' +
                            '<div id="' + divIdToReplaceWithSubTable + '">' +
                            '<span class="loadingPiwik" style="display:inline"><img src="plugins/Morpheus/images/loading-blue.gif" />' + _pk_translate('General_Loading') + '</span>' +
                            '</div>' +
                            '</td>' +
                            '</tr>'
                    );

                    var savedActionVariable = self.param.action;

                    // reset all the filters from the Parent table
                    var filtersToRestore = self.resetAllFilters();
                    // do not ignore the exclude low population click
                    self.param.enable_filter_excludelowpop = filtersToRestore.enable_filter_excludelowpop;

                    self.param.idSubtable = idSubTable;
                    self.param.action = self.props.subtable_controller_action;

					delete self.param.totalRows;

                    self.reloadAjaxDataTable(false, function(response) {
                        self.dataTableLoaded(response, divIdToReplaceWithSubTable);
                    });

                    self.param.action = savedActionVariable;
                    delete self.param.idSubtable;
                    self.restoreAllFilters(filtersToRestore);

                    self.loadedSubDataTable[divIdToReplaceWithSubTable] = true;

                    $(this).next().toggle();

                    // when "loading..." is displayed, hide actions
                    // repositioning after loading is not easily possible
                    $(this).find('div.dataTableRowActions').hide();
                }

                $(this).next().toggle();
                $(this).toggleClass('expanded');
                self.repositionRowActions($(this));
            }
        ).size();
    },

    // tooltip for column documentation
    handleColumnDocumentation: function (domElem) {
        if (this.isDashboard()) {
            // don't display column documentation in dashboard
            // it causes trouble in full screen view
            return;
        }

        $('th:has(.columnDocumentation)', domElem).each(function () {
            var th = $(this);
            var tooltip = th.find('.columnDocumentation');

            tooltip.next().hover(function () {
                    var left = (-1 * tooltip.outerWidth() / 2) + th.width() / 2;
                    var top = -1 * (tooltip.outerHeight() + 10);

                    if (th.next().size() == 0) {
                        left = (-1 * tooltip.outerWidth()) + th.width() +
                            parseInt(th.css('padding-right'), 10);
                    }

                    tooltip.css({
                        marginLeft: left,
                        marginTop: top
                    });

                    tooltip.stop(true, true).fadeIn(250);
                },
                function () {
                    $(this).prev().stop(true, true).fadeOut(400);
                });
        });
    },

    handleRowActions: function (domElem) {
        this.doHandleRowActions(domElem.find('table > tbody > tr'));
    },

	handleCellTooltips: function(domElem) {
		domElem.find('span.cell-tooltip').tooltip({
			track: true,
			items: 'span',
			content: function() {
				return $(this).parent().data('tooltip');
			},
			show: false,
			hide: false,
			tooltipClass: 'small'
		});
	},

    handleRelatedReports: function (domElem) {
        var self = this,
            hideShowRelatedReports = function (thisReport) {
                $('span', $(thisReport).parent().parent()).each(function () {
                    if (thisReport == this)
                        $(this).hide();
                    else
                        $(this).show();
                });
            },
        // 'this' report must be hidden in datatable output
            thisReport = $('.datatableRelatedReports span:hidden', domElem)[0];

        hideShowRelatedReports(thisReport);

        var relatedReports = $('.datatableRelatedReports span', domElem);

        if (!relatedReports.length) {
            $('.datatableRelatedReports', domElem).hide();
        }

        relatedReports.each(function () {
            var clicked = this;
            $(this).unbind('click').click(function (e) {
                var url = $(this).attr('href');

                // if this url is also the url of a menu item, better to click that menu item instead of
                // doing AJAX request
                var menuItem = null;
                $("#root").find(">.nav a").each(function () {
                    if ($(this).attr('href') == url) {
                        menuItem = this;
                        return false
                    }
                });

                if (menuItem) {
                    $(menuItem).click();
                    return;
                }

                // modify parameters
                self.resetAllFilters();
                var newParams = broadcast.getValuesFromUrl(url);

                for (var key in newParams) {
                    self.param[key] = decodeURIComponent(newParams[key]);
                }

                delete self.param.pivotBy;
                delete self.param.pivotByColumn;

                // do ajax request
                self.reloadAjaxDataTable(true, function (newReport) {
                    var newDomElem = self.dataTableLoaded(newReport, self.workingDivId);
                    hideShowRelatedReports(clicked);
                });
            });
        });
    },

    /**
     * Handle events that other code triggers on this table.
     *
     * You can trigger one of these events to get the datatable to do things,
     * such as reload its data.
     *
     * Events handled:
     *  - reload: Triggering 'reload' on a datatable DOM element will
     *            reload the datatable's data. You can pass in an object mapping
     *            parameters to set before reloading data.
     *
     *    $(datatableDomElem).trigger('reload', {columns: 'nb_visits,nb_actions', idSite: 2});
     */
    handleTriggeredEvents: function (domElem) {
        var self = this;

        // reload datatable w/ new params if desired (NOTE: must use 'bind', not 'on')
        $(domElem).bind('reload', function (e, paramOverride) {
            paramOverride = paramOverride || {};
            for (var name in paramOverride) {
                self.param[name] = paramOverride[name];
            }

            self.reloadAjaxDataTable(true);
        });
    },

    handleSummaryRow: function (domElem) {
        var details = _pk_translate('General_LearnMore', [' (<a href="http://piwik.org/faq/how-to/faq_54/" rel="noreferrer"  target="_blank">', '</a>)']);

        domElem.find('tr.summaryRow').each(function () {
            var labelSpan = $(this).find('.label .value');
            var defaultLabel = labelSpan.text();

            $(this).hover(function() {
                    labelSpan.html(defaultLabel + details);
                },
                function() {
                    labelSpan.text(defaultLabel);
                });
        });
    },

    // also used in action data table
    doHandleRowActions: function (trs) {
        var self = this;

        var merged = $.extend({}, self.param, self.props);
        var availableActionsForReport = DataTable_RowActions_Registry.getAvailableActionsForReport(merged);

        if (availableActionsForReport.length == 0) {
            return;
        }

        var actionInstances = {};
        for (var i = 0; i < availableActionsForReport.length; i++) {
            var action = availableActionsForReport[i];
            actionInstances[action.name] = action.createInstance(self);
        }

        trs.each(function () {
            var tr = $(this);
            var td = tr.find('td:first');

            // call initTr on all actions that are available for the report
            for (var i = 0; i < availableActionsForReport.length; i++) {
                var action = availableActionsForReport[i];
                actionInstances[action.name].initTr(tr);
            }

            // if there are row actions, make sure the first column is not too narrow
            td.css('minWidth', '145px');

            // show actions that are available for the row on hover
            var actionsDom = null;

            tr.hover(function () {
                    if (actionsDom === null) {
                        // create dom nodes on the fly
                        actionsDom = self.createRowActions(availableActionsForReport, tr, actionInstances);
                        td.prepend(actionsDom);
                    }
                    // reposition and show the actions
                    self.repositionRowActions(tr);
                    if ($(window).width() >= 600) {
                        actionsDom.show();
                    }
                },
                function () {
                    if (actionsDom !== null) {
                        actionsDom.hide();
                    }
                });
        });
    },

    createRowActions: function (availableActionsForReport, tr, actionInstances) {
        var container = $(document.createElement('div')).addClass('dataTableRowActions');

        for (var i = availableActionsForReport.length - 1; i >= 0; i--) {
            var action = availableActionsForReport[i];

            if (!action.isAvailableOnRow(this.param, tr)) {
                continue;
            }

            var actionEl = $(document.createElement('a')).attr({href: '#'}).addClass('action' + action.name);
            actionEl.append($(document.createElement('img')).attr({src: action.dataTableIcon}));
            container.append(actionEl);

            if (i == availableActionsForReport.length - 1) {
                actionEl.addClass('leftmost');
            }
            if (i == 0) {
                actionEl.addClass('rightmost');
            }

            actionEl.click((function (action, el) {
                return function (e) {
                    $(this).blur().tooltip('close');
                    container.hide();
                    if (typeof actionInstances[action.name].onClick == 'function') {
                        return actionInstances[action.name].onClick(el, tr, e);
                    }
                    actionInstances[action.name].trigger(tr, e);
                    return false;
                }
            })(action, actionEl));

            if (typeof action.dataTableIconHover != 'undefined') {
                actionEl.append($(document.createElement('img')).attr({src: action.dataTableIconHover}).hide());

                actionEl.hover(function () {
                        var img = $(this).find('img');
                        img.eq(0).hide();
                        img.eq(1).show();
                    },
                    function () {
                        var img = $(this).find('img');
                        img.eq(1).hide();
                        img.eq(0).show();
                    });
            }

            if (typeof action.dataTableIconTooltip != 'undefined') {
                actionEl.tooltip({
                    track: true,
                    items: 'a',
                    content: '<h3>'+action.dataTableIconTooltip[0]+'</h3>'+action.dataTableIconTooltip[1],
                    tooltipClass: 'rowActionTooltip',
                    show: false,
                    hide: false
                });
            }
        }

        return container;
    },

    repositionRowActions: function (tr) {
        if (!tr) {
            return;
        }

        var td = tr.find('td:first');
        var actions = tr.find('div.dataTableRowActions');

        if (!actions) {
            return;
        }

        actions.height(tr.innerHeight() - 2);
        actions.css('marginLeft', (td.width() + 3 - actions.outerWidth()) + 'px');
    },

    _findReportHeader: function (domElem) {
        var h2 = false;
        if (domElem.prev().is('h2')) {
            h2 = domElem.prev();
        }
        else if (this.param.viewDataTable == 'tableGoals') {
            h2 = $('#titleGoalsByDimension');
        }
        else if ($('h2', domElem)) {
            h2 = $('h2', domElem);
        }
        return h2;
    },

    _createDivId: function () {
        return 'dataTable_' + this._controlId;
    }
});

// handle switch to All Columns/Goals/HtmlTable DataTable visualization
var switchToHtmlTable = function (dataTable, viewDataTable) {
    // we only reset the limit filter, in case switch to table view from cloud view where limit is custom set to 30
    // this value is stored in config file General->datatable_default_limit but this is more an edge case so ok to set it to 10

    dataTable.param.viewDataTable = viewDataTable;

    // when switching to display simple table, do not exclude low pop by default
    delete dataTable.param.enable_filter_excludelowpop;
    delete dataTable.param.filter_sort_column;
    delete dataTable.param.filter_sort_order;
    delete dataTable.param.columns;
    dataTable.reloadAjaxDataTable();
    dataTable.notifyWidgetParametersChange(dataTable.$element, {viewDataTable: viewDataTable});
};

var switchToEcommerceView = function (dataTable, viewDataTable) {
    if (viewDataTable == 'ecommerceOrder') {
        dataTable.param.abandonedCarts = '0';
    } else {
        dataTable.param.abandonedCarts = '1';
    }

    var viewDataTable = dataTable.param.viewDataTable;
    if (viewDataTable == 'ecommerceOrder' || viewDataTable == 'ecommerceAbandonedCart') {
        viewDataTable = 'table';
    }

    switchToHtmlTable(dataTable, viewDataTable);
};

DataTable.registerFooterIconHandler('table', switchToHtmlTable);
DataTable.registerFooterIconHandler('tableAllColumns', switchToHtmlTable);
DataTable.registerFooterIconHandler('tableGoals', switchToHtmlTable);
DataTable.registerFooterIconHandler('ecommerceOrder', switchToEcommerceView);
DataTable.registerFooterIconHandler('ecommerceAbandonedCart', switchToEcommerceView);

// generic function to handle switch to graph visualizations
DataTable.switchToGraph = function (dataTable, viewDataTable) {
    var filters = dataTable.resetAllFilters();
    dataTable.param.flat = filters.flat;
    dataTable.param.columns = filters.columns;

    dataTable.param.viewDataTable = viewDataTable;
    dataTable.reloadAjaxDataTable();
    dataTable.notifyWidgetParametersChange(dataTable.$element, {viewDataTable: viewDataTable});
};

DataTable.registerFooterIconHandler('cloud', DataTable.switchToGraph);

exports.DataTable = DataTable;

})(jQuery, require);
