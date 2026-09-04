
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

//-----------------------------------------------------------------------------
//								DataTable
//-----------------------------------------------------------------------------

(function ($, require) {

var exports = require('piwik/UI'),
    UIControl = exports.UIControl;

/**
 * This class contains the client side logic for viewing and interacting with
 * Matomo datatables.
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

DataTable.initNewDataTables = function (reportId) {
    var selector = typeof reportId === 'string' ? '[data-report='+JSON.stringify(reportId)+']' : 'div.dataTable';
    $(selector).each(function () {
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

    _destroy: function() {
      UIControl.prototype._destroy.call(this);
      // remove handlers to avoid memory leaks
      if (this.windowResizeTableAttached) {
        $(window).off('resize', this._resizeDataTable);
      }
      if (this._bodyMouseUp) {
        $('body').off('mouseup', this._bodyMouseUp);
      }
    },

    //initialisation function
    init: function () {
        var domElem = this.$element;

        this.workingDivId = this._createDivId();
        domElem.attr('id', this.workingDivId);

        this.loadedSubDataTable = {};
        this.isEmpty = $('.pk-emptyDataTable', domElem).length > 0;
        window.Vue.nextTick().then(() => {
          this.bindEventsAndApplyStyle(domElem);
          this._init(domElem);
          this.enableStickHead(domElem);
          this.initialized = true;
        });
    },

    enableStickHead: function (domElem) {
      var resizeTimeout = null;
      var resize = function(domElem) {
        var tableScroller = $(domElem).find('.dataTableScroller');
        var tableScrollerWidth = tableScroller.width();
        var tableWidth = $(domElem).find('table').width();
        if (tableScrollerWidth < tableWidth) {
          tableScroller.css('overflow-x', 'scroll');
        } else {
          tableScroller.css('overflow-x', '');
        }
      };
      // Bind to the resize event of the window object
      $(window).on('resize', function () {
        resize(domElem);
        // trigger another check after a certain delay as during fast resizing
        // the width is sometimes reported incorrectly
        if (resizeTimeout) {
          window.clearTimeout(resizeTimeout);
        }
        resizeTimeout = window.setTimeout(function(){
          resize(domElem);
        }, 500);
        // Invoke the resize event immediately
      }).resize();
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
            'totals',
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
    reloadAjaxDataTable: function (displayLoading, callbackSuccess, extraParams) {
        var self = this;

        if (typeof displayLoading == "undefined") {
            displayLoading = true;
        }
        if (typeof callbackSuccess == "undefined") {
            callbackSuccess = function (response) {
                self.dataTableLoaded(response, self.workingDivId);
            };
        }

        // Each reload of the root table supersedes any still-pending one: tag it and apply only the
        // latest response, so an out-of-order response (e.g. a slow search-as-you-type reload)
        // cannot overwrite a newer reload - another search, a sort, paging, a period or a
        // visualization change. Errors from a superseded reload are dropped too.
        //
        // Loading a subtable is deliberately left out of that sequence. It reuses the parent's
        // instance but renders into its own div, so it neither supersedes a pending root reload nor
        // is superseded by one. Guarding it would strand the row for good: handleSubDataTable()
        // marks it loaded synchronously, so a dropped response never gets refetched.
        var isSubtableLoad = !!self.param.idSubtable;
        var reloadGeneration = null;

        if (!isSubtableLoad) {
            self._reloadGeneration = (self._reloadGeneration || 0) + 1;
            reloadGeneration = self._reloadGeneration;
        }

        var isSupersededReload = function () {
            return reloadGeneration !== null && reloadGeneration !== self._reloadGeneration;
        };

        if (displayLoading) {
            $('#' + self.workingDivId + ' .loadingPiwik').last().css('display', 'block');
        }

        $('#loadingError').hide();

        // when switching to display graphs, reset limit
        if (self && self.param && self.param.viewDataTable && String(self.param.viewDataTable).indexOf('graph') === 0) {
            delete self.param.filter_offset;
            delete self.param.filter_limit;
        }

        delete self.param.showtitle;

        var container = $('#' + self.workingDivId + ' .piwik-graph');

        var ajaxRequest = new ajaxHelper();

        if (self.param.totalRows) {
            ajaxRequest.addParams({'totalRows': self.param.totalRows}, 'post');
            delete self.param.totalRows;
        }

        var params = {};
        for (var key in self.param) {
            if (typeof self.param[key] != "undefined" && self.param[key] !== null && self.param[key] !== '') {
                if (key == 'filter_column' || key == 'filter_column_recursive' ) {
                    // search in (metadata) `combinedLabel` when dimensions are shown separately in flattened tables
                    // needs to be overwritten for each request as switching a searched table might return no results
                    // otherwise, as search column doesn't fit anymore
                    if (self.param.flat == "1" && self.param.show_dimensions == "1") {
                        params[key] = 'combinedLabel';
                    } else {
                        params[key] = 'label';
                    }
                    continue;
                }

                params[key] = self.param[key];
            }
        }

        // The response replaces `.dataTable` wholesale, and the report header sits outside it so it
        // survives that - a header in the response would therefore be a second one, not a
        // replacement. Set on this request only, not on `self.param`: that holds the report's own
        // state, which every later request is rebuilt from and other code reads back.
        params.disable_report_header = 1;

        ajaxRequest.addParams(params, 'get');
        if (extraParams) {
            ajaxRequest.addParams(extraParams, 'post');
        }
        ajaxRequest.withTokenInUrl();

        // store filtering parameters for given widget id
        var widgetId = this.findUniqueWidgetId(this.$element);
        if (widgetId) {
            var filterParams = this.getFilterParams(Object.assign({}, params, extraParams));
            window.CoreHome.SearchFiltersPersistenceStore.setSearchFilters(widgetId, filterParams);
        }

        ajaxRequest.setCallback(
            function (response) {
                // Bail before the teardown below, not just before applying the response: destroying
                // the plot and unbinding its handler would otherwise leave the newer reload's graph
                // torn down with nothing drawn in its place.
                if (isSupersededReload()) {
                    return;
                }
                container.trigger('piwikDestroyPlot');
                container.off('piwikDestroyPlot');
                callbackSuccess(response);
            }
        );
        ajaxRequest.setErrorCallback(function (deferred, status) {
            if (isSupersededReload()
                || status == 'abort' || !deferred || deferred.status < 400 || deferred.status >= 600) {
                return;
            }

            $('#' + self.workingDivId + ' .loadingPiwik').last().css('display', 'none');
            $('#loadingError').show();
        });
        ajaxRequest.setFormat('html');

        ajaxRequest.send();
    },

    // Function called when the AJAX request is successful
    // it looks for the ID of the response and replace the very same ID
    // in the current page with the AJAX response
    dataTableLoaded: function (response, workingDivId, doScroll) {
        var content = $(response);

        if ($.trim($('.dataTableControls', content).html()) === '') {
            $('.dataTableControls', content).append('&nbsp;');
            // fix table controls are not visible because there is no content. prevents limit selection being displayed
            // in the middle
        }

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

        if (doScroll || 'undefined' === typeof doScroll) {
            piwikHelper.lazyScrollTo(content[0], 400);
        }

        piwikHelper.compileVueEntryComponents(content);

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
        self.preBindEventsAndApplyStyleHook(domElem);
        self.handleSort(domElem);
        self.handleLimit(domElem);
        self.handlePeriod(domElem);
        self.handleOffsetInformation(domElem);
        self.handleAnnotationsButton(domElem);
        self.handleEvolutionAnnotations(domElem);
        self.handleExportBox(domElem);
        self.applyCosmetics(domElem);
        self.handleSubDataTable(domElem);
        // after handleConfigurationBox: that is what drops the flatten action from a report with no
        // subtables, and it only reaches the footer entry, so syncing before it would copy the
        // action into the header just as the footer gave it up
        self.handleConfigurationBox(domElem);
        self.syncReportHeaderActions(domElem);
        self.handleSearchBox(domElem);
        self.handleColumnDocumentation(domElem);
        self.handleRowActions(domElem);
        self.handleCellTooltips(domElem);
        self.handleRelatedReports(domElem);
        self.handleTriggeredEvents(domElem);
        self.handleColumnHighlighting(domElem);
        self.setFixWidthToMakeEllipsisWork(domElem);
        self.handleSummaryRow(domElem);
        self.postBindEventsAndApplyStyleHook(domElem);
    },

    preBindEventsAndApplyStyleHook: function (domElem) {

    },
    postBindEventsAndApplyStyleHook: function (domElem) {

    },

    isWidgetized: function () {
        return -1 !== location.search.indexOf('module=Widgetize');
    },

    setFixWidthToMakeEllipsisWork: function (domElem) {
        var self = this;

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

        function getLabelWidth(domElem, tableWidth, minLabelWidth, maxLabelWidth)
        {
            var labelWidth = minLabelWidth;

            var columnsInFirstRow = $('tbody tr:not(.parentComparisonRow):not(.comparePeriod):eq(0) td:not(.label)', domElem);

            var widthOfAllColumns = 0;
            columnsInFirstRow.each(function (index, column) {
                // Round each measurement so the sum is a stable integer; raw sub-pixel widths
                // made the label width (and where labels get truncated) jitter between runs.
                widthOfAllColumns += Math.round($(column).outerWidth());
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
                && !self.isWidgetized()
                && innerWidth !== domElem.width()
                && !self.isDashboard()
            ) {
                labelWidth = maxLabelWidth; // prevent for instance table in Actions-Pages is not too wide
            }
            var allColumns = $('tr:nth-child(1) td.label', domElem).length;
            var firstTableColumn = $('table:first tbody>tr:first td.label', domElem).length;
            var amount = allColumns;
            if (allColumns > 2 * firstTableColumn) {
                amount = 2 * firstTableColumn;
            }
            var newWidth = parseInt(labelWidth / amount, 10)
            if (newWidth == 0) {
                newWidth = maxLabelWidth; // fallback to the maximum width if zero
            }
            return newWidth;
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

        function getLabelColumnMaxWidth(domElem)
        {
            var maxWidth = 0;
            var maxWidthHead = $('thead .first.label', domElem).css('maxWidth');

            if (maxWidthHead) {
                maxWidthHead = parseInt(maxWidthHead, 10);
                if (maxWidthHead > 0) {
                    maxWidth = parseInt(maxWidthHead, 10);
                }
            }

            var maxWidthBody = $('tbody tr:nth-child(1) td.label', domElem).css('maxWidth');

            if (maxWidthBody) {
                maxWidthBody = parseInt(maxWidthBody, 10);
                if (maxWidthBody && maxWidthBody > 0 && (maxWidth === 0 || maxWidthBody < maxWidth)) {
                    maxWidth = maxWidthBody;
                }
            }

            return parseInt(maxWidth, 10);
        }

        function removePaddingFromWidth(elem, labelWidth) {
            var paddingLeft  = elem.css('paddingLeft');
            paddingLeft      = paddingLeft ? Math.round(parseFloat(paddingLeft)) : 0;
            var paddingRight = elem.css('paddingRight');
            paddingRight     = paddingRight ? Math.round(parseFloat(paddingRight)) : 0;

            if (elem.find('.prefix-numeral').length) {
                labelWidth -= Math.round(parseFloat(elem.find('.prefix-numeral').outerWidth()));
            }

            return labelWidth - paddingLeft - paddingRight;
        }

        var isTableVisualization = this.param.viewDataTable
            && typeof this.param.viewDataTable === 'string'
            && typeof this.param.viewDataTable.indexOf === 'function'
            && this.param.viewDataTable.indexOf('table') !== -1;
        if (isTableVisualization) {
            // we do this only for html tables

            var tableWidth = getTableWidth(domElem);
            var labelColumnMinWidth = getLabelColumnMinWidth(domElem);
            var labelColumnMaxWidth = getLabelColumnMaxWidth(domElem);
            var labelColumnWidth    = getLabelWidth(
              domElem,
              tableWidth,
              self.props.min_label_width || 125,
              self.props.max_label_width || 440
            );
            if (labelColumnMinWidth > labelColumnWidth) {
                labelColumnWidth = labelColumnMinWidth;
            }
            if (labelColumnMaxWidth && labelColumnMaxWidth < labelColumnWidth) {
                labelColumnWidth = labelColumnMaxWidth;
            }

            // special handling if the loaded datatable is a subtable
            if ($(domElem).closest('.subDataTableContainer').length) {
                var parentTable = $(domElem).closest('table.dataTable');
                var tableColumns = $('table:eq(0)>thead th', domElem).length;
                var parentTableColumns = $('>thead th', parentTable).length;
                var labelColumn = $('>tbody td.label:eq(0)', parentTable);
                var labelWidthParentTable = labelColumn.outerWidth();

                // if the subtable has the same column count as the main table, we rearrange all tables
                if (parentTableColumns === tableColumns) {
                    labelColumnWidth = Math.min(labelColumnWidth, labelWidthParentTable);

                    // rearrange base table labels, so the tables are displayed aligned
                    $('>tbody>tr:not(.subDataTableContainer)>td.label', parentTable).each(function() {
                        $(this).css({
                            width: removePaddingFromWidth($(this), labelColumnWidth) + 'px'
                        });
                    });

                    // rearrange all subtables having the same column count
                    $('>tbody>tr.subDataTableContainer', parentTable).each(function() {
                        if ($('table:eq(0)>thead th', this).length === parentTableColumns) {
                            $(this).css({
                                width: removePaddingFromWidth($(this), labelColumnWidth) + 'px'
                            });
                        }
                    });
                }
            }

            if (labelColumnWidth) {
                $('td.label', domElem).each(function() {
                    $(this).css({
                        width: removePaddingFromWidth($(this), labelColumnWidth) + 'px'
                    });
                });
            }

            $('td span.label', domElem).each(function () { self.tooltip($(this)); });
        }

        if (!self.windowResizeTableAttached) {
            self.windowResizeTableAttached = true;

            // on resize of the window we re-calculate everything.
            var timeout = null;
            var windowWidth = 0;
            var resizeDataTable = function() {

                if (windowWidth === $(window).width()) {
                    return; // only resize a data table if the width changes
                }

                if (timeout) {
                    clearTimeout(timeout);
                }

                timeout = setTimeout(function () {
                    var isInDom = domElem && domElem[0] && document && document.body && document.body.contains(domElem[0]);
                    if (isInDom) {
                        // as domElem might have been removed by now we check whether domElem actually still is in dom
                        // and do this expensive operation only if needed.
                        if (isTableVisualization) {
                            $('td.label', domElem).width('');
                        }
                        self.setFixWidthToMakeEllipsisWork(domElem);
                        windowWidth = $(window).width();
                    } else {
                        $(window).off('resize', resizeDataTable);
                    }

                    timeout = null;
                }, Math.floor((Math.random() * 80) + 220));
                // we randomize it just a little to not process all dataTables at similar time but to have a little
                // delay in between for smoother resizing. we want to do it between 300 and 400ms
            }

            $(window).on('resize', resizeDataTable);
            self._resizeDataTable = resizeDataTable;
        }
    },

    handleLimit: function (domElem) {
        var tableRowLimits = this.props.datatable_row_limits || piwik.config.datatable_row_limits,
        evolutionLimits =
        {
            day: [8, 30, 60, 90, 180],
            week: [4, 12, 26, 52, 104],
            month: [3, 6, 12, 24, 36, 120],
            year: [3, 5, 10]
        };

        // only allow big evolution limits for non flattened reports
        if (!parseInt(this.param.flat)) {
            evolutionLimits.day.push(365, 500);
            evolutionLimits.week.push(500);
        }

        var self = this;
        if (typeof self.parentId != "undefined" && self.parentId != '') {
            return;
        }

        if (self.props.disable_all_rows_filter_limit) { // remove the -1 value from the limits array
            var tempTableRowLimits = [];
            tableRowLimits.forEach(function (limit) {
                if (limit != -1) {
                    tempTableRowLimits.push(limit);
                }
            });
            tableRowLimits = tempTableRowLimits;
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

        var selectedValue = getFilterLimitAsString(self.param[limitParamName]);

        if (!self.props.show_limit_control) {
            $('.limitSelection', domElem).hide();
            return;
        }

        var options = '';
        for (var i = 0; i < numbers.length; i++) {
            var currentValue = getFilterLimitAsString(numbers[i]);
            var isCurrent = selectedValue == currentValue;

            options += '<li class="mtm-dropdownPanel__menuItem" role="none">'
                // No href, like PeriodsMenu's items: with one, a middle-click would open a tab.
                + '<a class="mtm-dropdownPanel__menuLink" role="menuitemradio" tabindex="0"'
                + ' aria-checked="' + (isCurrent ? 'true' : 'false') + '"'
                + ' data-limit="' + piwikHelper.htmlEntities(String(numbers[i])) + '">'
                + '<span class="mtm-dropdownPanel__menuLabel">'
                + piwikHelper.htmlEntities(String(currentValue)) + '</span>'
                + (isCurrent ? '<span class="mtm-dropdownPanel__rightIcon" aria-hidden="true">'
                    + '<span class="icon-ok"></span></span>' : '')
                + '</a></li>';
        }

        var label = piwikHelper.htmlEntities(_pk_translate('General_RowsToDisplay'));
        // The value goes into the name too: alone, the label would override the visible value,
        // which is the button's only text.
        var triggerName = label + ': ' + piwikHelper.htmlEntities(String(selectedValue));
        // Built here rather than in Vue: the choices are only known once the table has loaded.
        $('.limitSelection', domElem).append(
            '<div class="mtm-selector">'
            + '<button type="button" class="mtm-selector__trigger" title="' + label + '"'
            + ' aria-label="' + triggerName + '" aria-haspopup="menu" aria-expanded="false">'
            + '<span class="mtm-selector__label">'
            + piwikHelper.htmlEntities(String(selectedValue)) + '</span>'
            + '<span class="mtm-selector__rightIcon" aria-hidden="true">'
            + '<span class="icon-chevron-down"></span></span>'
            + '</button>'
            + '<div class="mtm-selector__dropdown"><div class="mtm-dropdownPanel">'
            + '<ul class="mtm-dropdownPanel__menu" role="menu" aria-label="' + label + '">'
            + options + '</ul></div></div></div>'
        );

        var $selector = $('.limitSelection .mtm-selector', domElem);

        if (self.isEmpty) {
            // The class only dims it; the attribute is what removes it from the tab order.
            $selector.addClass('disabled')
                .find('.mtm-selector__trigger').attr('disabled', 'disabled');
            return;
        }

        self._bindLimitSelector($selector, function (limit) {
            if (limit == self.param[limitParamName]) {
                return;
            }

            setLimitValue(self.param, limit);
            self.reloadAjaxDataTable();

            var data = {};
            data[limitParamName] = self.param[limitParamName];
            self.notifyWidgetParametersChange(domElem, data);
        });
    },
    // Rebound, not added to: handleLimit runs again after every reload and nothing detaches them.
    _bindLimitSelectorDismissal: function () {
        function collapse($selectors) {
            $selectors.removeClass('expanded')
                .find('.mtm-selector__trigger').attr('aria-expanded', 'false');
        }

        $(document)
            .off('click.limitSelection keyup.limitSelection')
            .on('click.limitSelection', function (event) {
                $('.limitSelection .mtm-selector.expanded').each(function () {
                    var $selector = $(this);
                    if (!$selector.is(event.target) && !$selector.has(event.target).length) {
                        collapse($selector);
                    }
                });
            })
            .on('keyup.limitSelection', function (event) {
                if (event.key === 'Escape') {
                    collapse($('.limitSelection .mtm-selector.expanded'));
                }
            });
    },
    // The Vue selectors get this from ExpandOnClick; this one is jQuery, so it toggles its own class.
    _bindLimitSelector: function ($selector, onPick) {
        var $trigger = $selector.find('.mtm-selector__trigger');

        function close() {
            $selector.removeClass('expanded');
            $trigger.attr('aria-expanded', 'false');
        }

        // Propagation is left alone: the dismissal handler needs this click to fold the other panels.
        $trigger.on('click', function (event) {
            event.preventDefault();

            var opening = !$selector.hasClass('expanded');
            $selector.toggleClass('expanded', opening);
            $trigger.attr('aria-expanded', opening ? 'true' : 'false');
        });

        $selector.on('click', '[data-limit]', function (event) {
            event.preventDefault();
            close();
            onPick($(this).attr('data-limit'));
        });

        $selector.on('keydown', '[data-limit]', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                $(this).click();
            }
        });

        this._bindLimitSelectorDismissal();
    },
    handlePeriod: function (domElem) {
        var self = this;
        var scope = self._periodsScope(domElem);
        scope.off('click.reportAction', '.dataTablePeriods .tableIcon')
            .on('click.reportAction', '.dataTablePeriods .tableIcon', function () {
            var period = $(this).attr('data-period');
            if (!period || period == self.param['period']) {
                return;
            }

            var piwikPeriods = window.CoreHome.Periods;
            var formatDate = window.CoreHome.format;
            if (self.param['dateUsedInGraph']) {
                // this parameter is passed along when switching between periods. So we perfer using
                // it, to avoid a change in the end date shown in the graph
                var currentPeriod = piwikPeriods.parse('range', self.param['dateUsedInGraph']);
            } else {
                var currentPeriod = piwikPeriods.parse(self.param['period'], self.param['date']);
            }
            var endDateOfPeriod = currentPeriod.getDateRange()[1];
            endDateOfPeriod = formatDate(endDateOfPeriod);

            self.param['period'] = period;
            self.param['date'] = endDateOfPeriod;
            self.reloadAjaxDataTable();
        });
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

    // Drives the report search rendered in the shared ReportHeader. The input and its debounce
    // belong to the Vue component; here we only push state into it and apply the keyword it emits.
    handleSearchBox: function (domElem) {
        var self = this;

        // Subtables reuse the parent report's header and have no search of their own. Both signals
        // are needed: `parentId` is only set by ActionsDataTable, while a generic expandable
        // subtable is a fresh instance that carries `idSubtable` in its params.
        if ((typeof self.parentId != "undefined" && self.parentId != '') || self.param.idSubtable) {
            return;
        }

        var header = self._findReportHeaderApp(domElem);
        if (!header || !header.app) {
            // no shared header in this context (search has no other home now)
            return;
        }

        var currentPattern = '';
        if (self.param.filter_pattern && self.param.filter_pattern.length > 0) {
            currentPattern = self.param.filter_pattern;
        } else if (self.param.filter_pattern_recursive
            && self.param.filter_pattern_recursive.length > 0) {
            currentPattern = self.param.filter_pattern_recursive;
        }
        currentPattern = piwikHelper.htmlDecode(currentPattern);

        // Un-escape a leading regex special char for display (searchForPattern re-escapes it).
        var patternsToReplace = [{from: '?', to: '\\?'}, {from: '+', to: '\\+'}, {from: '*', to: '\\*'}];
        $.each(patternsToReplace, function (index, pattern) {
            if (0 === currentPattern.indexOf(pattern.to)) {
                currentPattern = pattern.from + currentPattern.slice(2);
            }
        });

        // show_search is a report config flag, carried to the client with the report's action
        // config; default to no search when absent.
        var actionsConfig = self._readReportActionsConfig(domElem);
        var showSearch = !!(actionsConfig && actionsConfig.showSearch);

        // Hide the input on an empty table, but keep it while a search is active so a no-result
        // search can still be cleared.
        header.app.showSearch_ = showSearch && (!self.isEmpty || !!currentPattern);
        header.app.searchQuery_ = currentPattern;

        // domElem is the root div.dataTable, the element the empty-state CSS keys off.
        domElem.closest('.dataTable').toggleClass('hasSearchKeyword', !!currentPattern);

        // Bridge the header's debounced search back to this table. Rebinding on every render is
        // safe: the namespaced handler is removed first, and the header persists across reloads.
        header.$el
            .off('reportheader:search.dataTableSearch')
            .on('reportheader:search.dataTableSearch', function (e) {
                self.searchForPattern(e.originalEvent.detail.keyword);
            });
    },

    // Applies a search keyword to the report and reloads it. An empty keyword clears a previous
    // search. Called from the ReportHeader search bridge in handleSearchBox.
    searchForPattern: function (keyword) {
        var self = this;
        keyword = keyword || '';

        var hasCurrentPattern = (self.param.filter_pattern && self.param.filter_pattern.length > 0)
            || (self.param.filter_pattern_recursive
                && self.param.filter_pattern_recursive.length > 0);

        if (!keyword && !hasCurrentPattern) {
            // nothing to search for, and no previous search to clear
            return;
        }

        self.param.filter_offset = 0;

        var patternsToReplace = [{from: '?', to: '\\?'}, {from: '+', to: '\\+'}, {from: '*', to: '\\*'}];
        $.each(patternsToReplace, function (index, pattern) {
            if (0 === keyword.indexOf(pattern.from)) {
                keyword = pattern.to + keyword.slice(1);
            }
        });

        if (self.param.search_recursive) {
            self.param.filter_column_recursive = 'label';
            self.param.filter_pattern_recursive = keyword;
        } else {
            self.param.filter_column = 'label';
            self.param.filter_pattern = keyword;
        }

        delete self.param.totalRows;

        self.reloadAjaxDataTable(true);
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

                if (offsetEnd > totalRows || Number(self.param.filter_limit) == -1) offsetEndDisp = totalRows;

                // only show this string if there is some rows in the datatable
                if (totalRows != 0) {
                    var str = sprintf(_pk_translate('General_Pagination'), offset, offsetEndDisp, totalRows);
                    $(this).text(str);
                } else {
                    $(this).hide();
                }
            }
        );

        var $next = $('.dataTableNext', domElem);

        // Display the next link if the total Rows is greater than the current end row
        // and filter_limit is a positive number (not "show all")
        $next.each(function () {
            var offsetEnd = Number(self.param.filter_offset)
                + Number(self.param.filter_limit);
            var totalRows = Number(self.param.totalRows);
            if (self.param.keep_summary_row == 1) --totalRows;
            if (offsetEnd < totalRows && Number(self.param.filter_limit) > 0) {
                $(this).css('visibility', 'visible');
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
                $(this).css('visibility', 'visible');
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

    // An action that moved into the header is bound there, not on the report. Siblings in a
    // container widget share one header and _findReportScope rightly refuses to widen, so only the
    // report offering the action widens: every report binds these, and each rebind `.off()`s.
    _headerActionScope: function (domElem, offersAction) {
        if (!offersAction) {
            return this._findReportScope(domElem);
        }
        var $scope = this._locateReportHeader(domElem).$scope;
        return $scope.length ? $scope : this._findReportScope(domElem);
    },

    // An action reaches the menu only when the menu itself renders, so its own flag is not enough.
    _offersAction: function (domElem, actionFlag) {
        var config = this._readReportActionsConfig(domElem);
        return !!(config && config.showFooter && config.showFooterIcons && config[actionFlag]);
    },

    _annotationsScope: function (domElem) {
        return this._headerActionScope(domElem, this._offersAnnotations(domElem));
    },

    _periodsScope: function (domElem) {
        return this._headerActionScope(domElem, this._offersAction(domElem, 'showPeriods'));
    },

    // Read from the config, not the DOM: this runs before the header is filled, and Vue renders
    // the entry a tick later still.
    _offersAnnotations: function (domElem) {
        if (!this._readReportActionsConfig(domElem)) {
            return $('.annotationView', this._findReportScope(domElem)).length > 0;
        }
        return this._offersAction(domElem, 'showAnnotations');
    },

    _setAnnotationsShowing: function (domElem, showing) {
        // A sibling reloading beside the graph must not report its empty state on the shared header.
        // Untested: no shipped container renders two reports under one header yet.
        if (!this._offersAnnotations(domElem)) {
            return;
        }
        var header = this._findReportHeaderApp(domElem);
        if (header && header.app) {
            header.app.annotationsShowing_ = showing;
        }
    },

    handleEvolutionAnnotations: function (domElem) {
        var self = this;
        var isEvolution = self.param.viewDataTable === 'graphEvolution'
            || self.param.viewDataTable === 'graphStackedBarEvolution';
        if (isEvolution && self._offersAnnotations(domElem)) {
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

                    piwik.annotations.placeEvolutionAnnotations(annotations, domElem);

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
                                    // Runs on the way out as well as in, so read the panel.
                                    self._setAnnotationsShowing(
                                        domElem, !manager.is(':hidden'));

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

        // An ajax reload replaces the table wholesale and takes the manager with it, while the
        // header deliberately survives - so the state it carries would keep saying the notes are
        // on screen when they are not. Put it back in step on every bind.
        var $scope = self._annotationsScope(domElem);
        self._setAnnotationsShowing(domElem, $('.annotation-manager', domElem).is(':visible'));

        // the trigger is scoped to the report, so it keeps working once it moves up into the
        // header; the manager it toggles stays inside the table
        $scope
            .off('click.reportAction', '.annotationView')
            .on('click.reportAction', '.annotationView', function () {
            var annotationManager = $('.annotation-manager', domElem);

            // The entry reads "hide annotations", so it hides whatever is on screen - notes a
            // marker opened for one day included. Those carry no `data-is-range`, and used to be
            // answered by reloading the whole range instead of closing.
            if (annotationManager.length > 0 && !annotationManager.is(':hidden')) {
                annotationManager.slideUp('slow');
                self._setAnnotationsShowing(domElem, false);
                return;
            }

            if (annotationManager.length > 0
                && annotationManager.attr('data-is-range') == 1) {
                annotationManager.slideDown('slow');
                self._setAnnotationsShowing(domElem, true);
                return;
            }

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
                    // Toggles when the dates already match, so read the panel back rather than
                    // assume it opened.
                    self._setAnnotationsShowing(domElem, !manager.is(':hidden'));
                }
            );
        });
    },

    // DataTable view box (simple table, all columns table, Goals table, pie graph, tag cloud, graph, ...)
    handleExportBox: function (domElem) {
        var self = this;
        if (self.param.idSubtable) {
            // no view box for subtables
            return;
        }

        //footer arrow position element name
        self.jsViewDataTable = self.param.viewDataTable;

        var scope = self._findReportScope(domElem);

        $('.tableAllColumnsSwitch a', scope).show();

        scope.off('click.reportAction', '.dataTableFooterIcons .tableIcon')
            .on('click.reportAction', '.dataTableFooterIcons .tableIcon', function () {
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
    },

    handleConfigurationBox: function (domElem, callbackSuccess) {
        var self = this;

        if (typeof self.parentId != "undefined" && self.parentId != '') {
            // no manipulation when loading subtables
            return;
        }

        var scope = self._findReportScope(domElem);

        if ((typeof self.numberOfSubtables == 'undefined' || self.numberOfSubtables == 0)
            && (typeof self.param.flat == 'undefined' || self.param.flat != 1)
        ) {
            // if there are no subtables, drop the flatten action from the config the header reads,
            // published just below in bindEventsAndApplyStyle
            var actionsConfig = self._readReportActionsConfig(domElem);
            if (actionsConfig) {
                actionsConfig.showFlattenTable = false;
            }
        }

        // No early return on an empty `ul.tableConfiguration`: where the header is rendered by the
        // widget chrome its menu is filled by Vue only once syncReportHeaderActions runs, so the
        // entries do not exist yet at this point. The handlers below are delegated for the same
        // reason - they must survive the menu being rendered, and re-rendered, after this.

        var generateClickCallback = function (paramName, callbackAfterToggle, setParamCallback) {
            return function () {
                if (setParamCallback) {
                    var data = setParamCallback();
                } else {
                    self.param[paramName] = (1 - (self.param[paramName] || 0)) + '';
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

        // handle low population
        scope
            .off('click.reportAction', '.dataTableExcludeLowPopulation')
            .on('click.reportAction', '.dataTableExcludeLowPopulation',
                generateClickCallback('enable_filter_excludelowpop'));

        // handle flatten
        scope
            .off('click.reportAction', '.dataTableFlatten')
            .on('click.reportAction', '.dataTableFlatten', generateClickCallback('flat'));

        // handle flatten
        scope
            .off('click.reportAction', '.dataTableShowTotalsRow')
            .on('click.reportAction', '.dataTableShowTotalsRow', generateClickCallback('keep_totals_row'));

        // handle percentage values
        scope
            .off('click.reportAction', '.dataTableShowPercentageValues')
            .on('click.reportAction', '.dataTableShowPercentageValues',
                generateClickCallback('show_percentage_values'));

        scope
            .off('click.reportAction', '.dataTableIncludeAggregateRows')
            .on('click.reportAction', '.dataTableIncludeAggregateRows',
                generateClickCallback('include_aggregate_rows', function () {
                if (self.param.include_aggregate_rows == 1) {
                    // when including aggregate rows is enabled, we remove the sorting
                    // this way, the aggregate rows appear directly before their children
                    self.param.filter_sort_column = '';
                    self.notifyWidgetParametersChange(domElem, {filter_sort_column: ''});
                }
            }));

        scope
            .off('click.reportAction', '.dataTableShowDimensions')
            .on('click.reportAction', '.dataTableShowDimensions', generateClickCallback('show_dimensions'));

        // handle pivot by
        scope
            .off('click.reportAction', '.dataTablePivotBySubtable')
            .on('click.reportAction', '.dataTablePivotBySubtable', generateClickCallback('pivotBy', null, function () {
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
    },

    findUniqueWidgetId: function (domWidget) {
        // on dashboards, widget have widgetId attribute
        var widget = $(domWidget).closest('[widgetId]');
        if (widget && widget.length && widget[0].hasAttribute('widgetId')) {
            return widget[0].getAttribute('widgetId');
        }

        // on pages other than the dashboard the ID is on a different element
        widget = $(domWidget).closest('.matomo-widget');
        if (widget && widget.length && widget[0].hasAttribute('id')) {
            return widget[0].getAttribute('id');
        }

        return '';
    },

    getFilterParams: function (params) {
        return Object.keys(params)
           .filter(key => key.startsWith('filter_column') || key.startsWith('filter_pattern'))
           .reduce((filterParams, key) => {
               filterParams[key] = params[key];
               return filterParams;
           }, {});
    },

    notifyWidgetParametersChange: function (domWidget, parameters) {
        var widget = $(domWidget).closest('[widgetId],[containerid]');
        // trigger setParameters event on base element

        // Tell parent widget that the parameters of this table were updated, but only if we're not part of a widget
        // container. widget containers send ajax requests for each child widget, and the child widgets' parameters
        // are not saved with the container widget.
        if (widget && widget.length && widget[0].hasAttribute('widgetId')) {
            widget.trigger('setParameters', parameters);
        } else {
            if (!piwik.isUserIsAnonymous) {
              var containerId = widget && widget.length ? widget.attr('containerid') : undefined;
              var reportId = $(domWidget).closest('[data-report]').attr('data-report');

              var ajaxRequest = new ajaxHelper();
              ajaxRequest.addParams({
                module: 'CoreHome',
                action: 'saveViewDataTableParameters',
                report_id: reportId,
                containerId: containerId
              }, 'get');
              ajaxRequest.withTokenInUrl();
              ajaxRequest.addParams({
                parameters: JSON.stringify(parameters)
              }, 'post');
              ajaxRequest.setCallback(function () {
              });
              ajaxRequest.setFormat('html');
              ajaxRequest.send();
            }
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
        // empty
    },

    handleColumnHighlighting: function (domElem) {

        var currentNthChild = null;
        var self = this;

        // highlight all columns on hover
        $(domElem).on('mouseenter', 'td:not(.cellSubDataTable)', function (e) {
            e.stopPropagation();
            var $this = $(e.target);
            if ($this.hasClass('label')) {
                return;
            }

            var table    = $this.closest('table');
            var nthChild = $this.parent('tr').children().index($(e.target)) + 1;
            var rows     = $('> tbody > tr', table);

            if (currentNthChild === nthChild) {
                return;
            }

            currentNthChild = nthChild;

            rows.children("td:nth-child(" + (nthChild) + ")").addClass('highlight');
            self.repositionRowActions($this.parent('tr'));
        });

        $(domElem).on('mouseleave', 'td', function(event) {
            var $this = $(event.target);
            var table    = $this.closest('table');
            var $parentTr = $this.parent('tr');
            var tr       = $parentTr.children();
            var nthChild = $parentTr.children().index($this);
            var targetTd = $(event.relatedTarget).closest('td');
            var nthChildTarget = targetTd.parent('tr').children().index(targetTd);

            if (nthChild == nthChildTarget) {
                return;
            }

            currentNthChild = null;

            var rows = $('tr', table);
            rows.find("td:nth-child(" + (nthChild + 1) + ")").removeClass('highlight');
        });
    },

    getComparisonIdSubtables: function ($row) {
        if ($row.is('.parentComparisonRow')) {
            var comparisonRows = $row.nextUntil('.parentComparisonRow').filter('.comparisonRow');

            var comparisonIdSubtables = {};
            comparisonRows.each(function () {
                var comparisonSeriesIndex = +$(this).data('comparison-series');
                comparisonIdSubtables[comparisonSeriesIndex] = $(this).data('idsubtable');
            });

            return JSON.stringify(comparisonIdSubtables);
        }
        return undefined;
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
                    var numberOfColumns = $(this).closest('table').find('thead tr').first().children().length;

                    var $insertAfter = $(this).nextUntil(':not(.comparePeriod):not(.comparisonRow)').last();
                    if (!$insertAfter.length) {
                        $insertAfter = $(this);
                    }

                    // at the end of the query it will replace the ID matching the new HTML table #ID
                    // we need to create this ID first
                    var newRow = $insertAfter.after(
                        '<tr class="subDataTableContainer">' +
                            '<td colspan="' + numberOfColumns + '" class="cellSubDataTable">' +
                            '<div id="' + divIdToReplaceWithSubTable + '">' +
                            '<span class="loadingPiwik" style="display:inline"><span class="matomo-loader"><span></span><span></span><span></span></span>' + _pk_translate('General_Loading') + '</span>' +
                            '</div>' +
                            '</td>' +
                            '</tr>'
                    );

                    piwikHelper.lazyScrollTo(newRow);

                    var savedActionVariable = self.param.action;

                    // reset all the filters from the Parent table
                    var filtersToRestore = self.resetAllFilters();
                    // do not ignore the exclude low population click
                    self.param.enable_filter_excludelowpop = filtersToRestore.enable_filter_excludelowpop;

                    self.param.idSubtable = idSubTable;
                    self.param.action = self.props.subtable_controller_action;

					          delete self.param.totalRows;

                    var extraParams = {};
                    extraParams.comparisonIdSubtables = self.getComparisonIdSubtables($(this));

                    self.reloadAjaxDataTable(false, function(response) {
                        self.dataTableLoaded(response, divIdToReplaceWithSubTable);
                    }, extraParams);

                    self.param.action = savedActionVariable;
                    delete self.param.idSubtable;
                    self.restoreAllFilters(filtersToRestore);

                    self.loadedSubDataTable[divIdToReplaceWithSubTable] = true;

                    // when "loading..." is displayed, hide actions
                    // repositioning after loading is not easily possible
                    $(this).find('div.dataTableRowActions').hide();
                } else {
                    var $toToggle = $(this).nextUntil('.subDataTableContainer').last();
                    $toToggle = $toToggle.length ? $toToggle : $(this);
                    $toToggle.next().toggle();
                }

                $(this).toggleClass('expanded');
                self.repositionRowActions($(this));
            }
        ).length;
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
                var top = -1 * tooltip.outerHeight();
                var thPos = th.position();
                var distance = tooltip.parent().offset().top;
                var scroller = tooltip.closest('.dataTableScroller');

                var thPosTop = 0;

                if (thPos && thPos.top) {
                    thPosTop = thPos.top;
                }

                // we need to add thPosTop because the parent th is not position:relative. There may be a gap for the
                // headline
                top = top + thPosTop;

                if ($(window).scrollTop() >= distance - 100 || scroller.css('overflow-x')==='scroll') {
                      top = tooltip.parent().outerHeight()
                }

                if (!th.next().length) {
                      left = (-1 * tooltip.outerWidth()) + th.width() +
                        parseInt(th.css('padding-right'), 10);
                }

                if (th.offset().top + top < 0) {
                    top = thPosTop + th.outerHeight();
                }

                tooltip.css({
                    marginLeft: left,
                    marginTop: top,
                    top: 0
                });

                $(".dataTable thead").addClass('with-z-index');
                tooltip.stop(true, true).fadeIn(250);
            },
            function () {
              $(this).prev().stop(true, true).fadeOut(250);
              $(".dataTable thead").removeClass('with-z-index');
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
        domElem.find('span.ratio').tooltip({
            track: true,
            content: function() {
                var title = $(this).attr('title');
                return piwikHelper.escape(title.replace(/\n/g, '<br />'));
            },
            show: {delay: 700, duration: 200},
            hide: false
        })
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

        function replaceReportTitleAndHelp(domElem, relatedReportName) {
            if (!domElem || !domElem.length) {
                return;
            }

            // The header sits outside the reloaded datatable and is not re-mounted, so push
            // the new values into its Vue app instead of rewriting the rendered DOM.
            var headerApp = domElem.prev('[vue-entry="CoreHome.ReportHeader"]')
                .data('vueAppInstance');
            if (headerApp) {
                var $documentation = $('.reportDocumentation', domElem);
                // The table it publishes under is now a different report, and the header keys
                // its config off this.
                headerApp.reportId_ = domElem.closest('[data-report]').attr('data-report')
                    || headerApp.reportId_;
                headerApp.reportTitle_ = relatedReportName;
                headerApp.featureName_ = relatedReportName;
                headerApp.inlineHelp_ = $documentation.attr('data-content') || '';
                headerApp.helpUrl_ = $documentation.find('.onlineGuide').attr('href') || '';
                headerApp.reportGenerated_ = $documentation.find('.helpDate').html() || '';
                return;
            }

            // inside a widget the widget chrome owns the title
            var $widget = domElem.parents('.widget');
            if (!$widget.length) {
                return;
            }

            var widgetApp = $widget.find('.widgetTop [vue-entry="CoreHome.ReportHeader"]')
                .data('vueAppInstance');
            if (widgetApp) {
                widgetApp.reportTitle_ = relatedReportName;
            } else {
                $widget.find('.widgetName > span').text(relatedReportName);
            }
        }

        hideShowRelatedReports(thisReport);

        var relatedReports = $('.datatableRelatedReports span', domElem);

        if (!relatedReports.length) {
            $('.datatableRelatedReports', domElem).hide();
        }

        relatedReports.each(function () {
            var clicked = this;
            $(this).unbind('click').click(function (e) {
                var $this = $(this);
                var url = $this.attr('href');

                // modify parameters
                self.resetAllFilters();
                var newParams = broadcast.getValuesFromUrl(url);
                var isSecondaryDimensionReport = self.param.module === newParams.module && self.param.action === newParams.action && newParams.secondaryDimension;

                for (var key in newParams) {
                    self.param[key] = decodeURIComponent(newParams[key]);
                }

                delete self.param.pivotBy;
                delete self.param.pivotByColumn;

                var relatedReportName = $this.text();

                // do ajax request
                self.reloadAjaxDataTable(true, (function (relatedReportName, isSecondaryDimensionReport) {

                    return function (newReport) {
                        var newDomElem = self.dataTableLoaded(newReport, self.workingDivId);
                        hideShowRelatedReports(clicked);
                        if (!isSecondaryDimensionReport) {
                            replaceReportTitleAndHelp(newDomElem, relatedReportName);
                        }
                    }
                })(relatedReportName, isSecondaryDimensionReport));
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
        var details = _pk_translate('General_LearnMore', [' (<a href="'
        + _pk_externalRawLink('https://matomo.org/faq/how-to/faq_54/') + '" rel="noreferrer noopener" target="_blank">', '</a>)']);

        domElem.find('tr.summaryRow').each(function () {
            var labelSpan = $(this).find('.label .value').filter(function(index, elem){
                return $(elem).text() != '-';
            }).last();
            var defaultLabel = labelSpan.text();

            $(this).hover(function() {
                    labelSpan.html(piwikHelper.htmlEntities(defaultLabel) + details);
                },
                function() {
                    labelSpan.text(defaultLabel);
                });
        });
    },

    // also used in action data table
    doHandleRowActions: function (trs) {
        if (!trs || !trs.length || !trs[0]) {
            return;
        }
        var parent = $(trs[0]).closest('table');

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

        var useTouchEvent = false;
        var listenEvent = 'mouseenter';
        var userAgent = String(navigator.userAgent).toLowerCase();
        if (userAgent.match(/(iPod|iPhone|iPad|Android|IEMobile|Windows Phone)/i)) {
            useTouchEvent = true;
            listenEvent = 'click';
        }

        parent.on(listenEvent, 'tr:not(.subDataTableContainer)', function () {
            var tr = this;
            var $tr = $(tr);
            var td = $tr.find('td.label:last');

            // call initTr on all actions that are available for the report
            for (var i = 0; i < availableActionsForReport.length; i++) {
                var action = availableActionsForReport[i];
                actionInstances[action.name].initTr($tr);
            }

            // if there are row actions, make sure the first column is not too narrow
            td.css('minWidth', $tr.is('.comparisonRow') ? '117px' : '145px');

            if ($(this).is('.parentComparisonRow,.comparePeriod').length) {
                return;
            }

            if (useTouchEvent && tr.actionsDom && tr.actionsDom.prop('rowActionsVisible')) {
                tr.actionsDom.prop('rowActionsVisible', false);
                tr.actionsDom.hide();
                return;
            }

            if (!tr.actionsDom) {
                // create dom nodes on the fly
                tr.actionsDom = self.createRowActions(availableActionsForReport, $tr, actionInstances);
                td.prepend(tr.actionsDom);
            }

            // reposition and show the actions
            self.repositionRowActions($tr);
            if ($(window).width() >= 600 || useTouchEvent) {
                tr.actionsDom.show();
            }

            if (useTouchEvent) {
                tr.actionsDom.prop('rowActionsVisible', true);
            }
        });
        if (!useTouchEvent) {
            parent.on('mouseleave', 'tr', function () {
                var tr = this;
                if (tr.actionsDom) {
                    tr.actionsDom.hide();
                }
            });
        }
    },

    createRowActions: function (availableActionsForReport, tr, actionInstances) {
        var container = $(document.createElement('div')).addClass('dataTableRowActions');

        for (var i = availableActionsForReport.length - 1; i >= 0; i--) {
            var action = availableActionsForReport[i];

            if (!action.isAvailableOnRow(this.param, tr)) {
                continue;
            }

            var actionEl = $(document.createElement('a')).attr({href: '#'}).addClass('action' + action.name);

            if (action.dataTableIcon.indexOf('icon-') === 0) {
                actionEl.append($(document.createElement('span')).addClass(action.dataTableIcon + ' rowActionIcon'));
            } else {
                actionEl.append($(document.createElement('img')).attr({src: action.dataTableIcon}));
            }

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
                    // ensure the tooltips of parent elements are hidden when the action tooltip is shown
                    // otherwise it can happen that tooltips for subtable rows are shown as well.
                    open: function() {
                        var tooltip = $(this).parents('.matomo-widget').tooltip('instance');
                        if (tooltip) {
                            tooltip.disable();
                        }
                    },
                    close: function() {
                        var tooltip = $(this).parents('.matomo-widget').tooltip('instance');
                        if (tooltip) {
                            tooltip.enable();
                        }
                    },
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

        var td = tr.find('td.label:last');
        var actions = tr.find('div.dataTableRowActions');

        if (!actions) {
            return;
        }

        actions.height(tr.innerHeight() - 6);
        actions.css('marginLeft', (td.width() - 3 - actions.outerWidth()) + 'px');
    },

    _findReportHeader: function (domElem) {
        var $prev = domElem.prev();
        if ($prev.is('h2')) {
            return $prev;
        }
        // a full-page report renders its heading inside the shared report header component
        if ($prev.find('.reportHeader').length) {
            return $prev;
        }
        return $('h2', domElem);
    },

    // Locates the shared ReportHeader that titles this report, together with the element that
    // scopes the two of them. Returns empty sets when this report has no header beside it.
    // _findReportScope() needs the scope and _findReportHeaderApp() needs the header, and the two
    // have to recognise the same DOM shapes: a report whose controls are widened into the header
    // but whose header is not found loses its search, and vice versa.
    _locateReportHeader: function (domElem) {
        // Full-page report: the header precedes the table inside the wrapper. Usually it is the
        // table's own previous sibling, but an empty titled report gets an extra `.card >
        // .card-content` between the two (_dataTable.twig), so climb a couple of levels looking
        // for it.
        var $node = domElem;
        for (var depth = 0; depth < 3 && $node.length; depth += 1) {
            var $header = $node.prev('[vue-entry="CoreHome.ReportHeader"]');
            if ($header.length) {
                return { $header: $header, $scope: $node.parent() };
            }
            $node = $node.parent();
        }

        // A widget renders it in the widget chrome instead.
        var $widget = domElem.parents('.widget').first();
        var $widgetHeader = $widget.find('.widgetTop [vue-entry="CoreHome.ReportHeader"]').first();
        if ($widgetHeader.length) {
            return { $header: $widgetHeader, $scope: $widget };
        }

        return { $header: $(), $scope: $() };
    },

    // Returns the element that scopes one report: the wrapper holding both the shared ReportHeader
    // and this table, so an action handler finds its controls whether they are rendered inside the
    // table or up in the header.
    //
    // Falls back to the table itself whenever widening cannot be attributed to this report alone -
    // a subtable, a report rendered without a title and without a wrapper, or a container widget
    // holding several reports - because widening there would reach into another report.
    _findReportScope: function (domElem) {
        // Subtables reuse the parent report's header and have no controls of their own, so they must
        // never widen: doing so would rebind the parent's controls to the subtable's instance.
        // Both signals are needed - `parentId` is only set by ActionsDataTable, while a generic
        // expandable subtable is a fresh instance that carries `idSubtable` in its params.
        if ((typeof this.parentId != "undefined" && this.parentId != '') || this.param.idSubtable) {
            return domElem;
        }

        var $scope = this._locateReportHeader(domElem).$scope;
        if (!$scope.length) {
            return domElem;
        }

        // Several reports can sit under one header, as in a container widget. Every handler rebinds
        // with `.off('click.reportAction')`, so widening there would let whichever instance finishes
        // loading last take over its siblings' controls - and those loads race.
        if ($scope.find('.dataTable').not('.dataTable .dataTable').length > 1) {
            return domElem;
        }

        return $scope;
    },

    // The report's action config, rendered as data by _dataTableActions.twig. The header is
    // rendered outside the table so an ajax reload cannot replace it, which means its menu would
    // otherwise keep describing the report as it was when the page was built: after flattening,
    // "Show dimensions separately" would never appear, and the visualisation list would keep
    // marking the old one as active. The config travels with the table the reload does replace.
    _readReportActionsConfig: function (domElem) {
        var config = $('.reportActionsConfig', domElem).first().data('reportActions');
        return (config && typeof config === 'object') ? config : null;
    },

    syncReportHeaderActions: function (domElem) {
        // A subtable reuses its parent's header and has no controls of its own, so publishing here
        // would replace the parent's config with one describing the subtable.
        if ((typeof this.parentId != "undefined" && this.parentId != '') || this.param.idSubtable) {
            return;
        }

        var config = this._readReportActionsConfig(domElem);
        if (!config) {
            return;
        }

        // Published for the header to read, rather than pushed onto its app: a menu can no longer
        // describe an older load because a push was missed or ran before the menu existed.
        var key = window.CoreHome.reportIdentity(domElem[0]);
        window.CoreHome.ReportActionsStore.set(key, config);

        // The header derives a key of its own at mount, from a position and a report id that both
        // move under it - maximising puts the table in a dialog, a related report renames it. Hand
        // it the key actually written instead.
        var header = this._findReportHeaderApp(domElem);
        if (header && header.app) {
            header.app.reportKey_ = key;
        }
    },

    // Returns { $el, app } for the shared ReportHeader Vue app that titles this report, or null.
    // This is the same app replaceReportTitleAndHelp() pushes into.
    _findReportHeaderApp: function (domElem) {
        var $header = this._locateReportHeader(domElem).$header;
        if (!$header.length) {
            return null;
        }
        return { $el: $header, app: $header.data('vueAppInstance') };
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
    delete dataTable.param.totals;
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
    dataTable.param.keep_totals_row = filters.keep_totals_row;
    dataTable.param.columns = filters.columns;

    dataTable.param.viewDataTable = viewDataTable;
    dataTable.reloadAjaxDataTable();
    dataTable.notifyWidgetParametersChange(dataTable.$element, {viewDataTable: viewDataTable});
};

DataTable.registerFooterIconHandler('cloud', DataTable.switchToGraph);

exports.DataTable = DataTable;

})(jQuery, require);
