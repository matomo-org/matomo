/*!
 * Piwik - Web Analytics
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
 * To find a datatable element by report (ie, 'UserSettings.getBrowser'),
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
                klass = require('piwik/UI')[tableType] || require(tableType),
                table = new klass(this);
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
 * @param {string} report  The report, eg, UserSettings.getWideScreen
 * @return {Element} The datatable div displaying the report, or undefined if
 *                   it cannot be found.
 */
DataTable.getDataTableByReport = function (report) {
    var result = undefined;
    $('.dataTable').each(function () {
        if ($(this).attr('data-report') == report) {
            result = this;
            return false;
        }
    });
    return result;
};

$.extend(DataTable.prototype, UIControl.prototype, {

    //initialisation function
    init: function () {
        var domElem = this.$element;

        this.workingDivId = this._createDivId();
        domElem.attr('id', this.workingDivId);

        this.loadedSubDataTable = {};
        this.isEmpty = $('.pk-emptyDataTable', domElem).length > 0;
        this.bindEventsAndApplyStyle(domElem);
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
        self.reloadAjaxDataTable();
    },

    setGraphedColumn: function (columnName) {
        this.param.columns = columnName;
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
            'totalRows'
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

        // when switching to display graphs, reset limit
        if (self.param.viewDataTable && self.param.viewDataTable.indexOf('graph') === 0) {
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

        // keep the original list of related reports
        var oldReportsElem = $('.datatableRelatedReports', dataTableSel);
        $('.datatableRelatedReports', content).replaceWith(oldReportsElem);

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
     or truncate the labels when they are too big
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
        self.handleReportDocumentation(domElem);
        self.handleRowActions(domElem);
		self.handleCellTooltips(domElem);
        self.handleRelatedReports(domElem);
        self.handleTriggeredEvents(domElem);
        self.handleColumnHighlighting(domElem);
    },

    handleLimit: function (domElem) {
        var tableRowLimits = [5, 10, 25, 50, 100, 250, 500],
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

        // setup limit control
        $('.limitSelection', domElem).append('<div><span>' + self.param[limitParamName] + '</span></div><ul></ul>');

        if (self.props.show_limit_control) {
            $('.limitSelection ul', domElem).hide();
            for (var i = 0; i < numbers.length; i++) {
                $('.limitSelection ul', domElem).append('<li value="' + numbers[i] + '"><span>' + numbers[i] + '</span></li>');
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
                    var limit = parseInt($(event.target).text());

                    hide();
                    if (limit != self.param[limitParamName]) {
                        setLimitValue(self.param, limit);
                        $('.limitSelection>div>span', domElem).text(limit);
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

        function getSortImageSrc() {
            var imageSortSrc = false;
            if (currentIsSubDataTable) {
                if (self.param.filter_sort_order == 'asc') {
                    imageSortSrc = 'plugins/Zeitgeist/images/sort_subtable_asc.png';
                } else {
                    imageSortSrc = 'plugins/Zeitgeist/images/sort_subtable_desc.png';
                }
            } else {
                if (self.param.filter_sort_order == 'asc') {
                    imageSortSrc = 'plugins/Zeitgeist/images/sortasc.png';
                } else {
                    imageSortSrc = 'plugins/Zeitgeist/images/sortdesc.png';
                }
            }
            return imageSortSrc;
        }

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
            var imageSortSrc = getSortImageSrc();
            var imageSortWidth = 16;
            var imageSortHeight = 16;

            var sortOrder = self.param.filter_sort_order || 'desc';
            var ImageSortClass = sortOrder.charAt(0).toUpperCase() + sortOrder.substr(1);

            // we change the style of the column currently used as sort column
            // adding an image and the class columnSorted to the TD
            $("th#" + self.param.filter_sort_column + ' #thDIV', domElem).parent()
                .addClass('columnSorted')
                .prepend('<div class="sortIconContainer sortIconContainer' + ImageSortClass + '"><img class="sortIcon" width="' + imageSortWidth + '" height="' + imageSortHeight + '" src="' + imageSortSrc + '" /></div>');
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
                }
            }
        );

        // Display the next link if the total Rows is greater than the current end row
        $('.dataTableNext', domElem)
            .each(function () {
                var offsetEnd = Number(self.param.filter_offset)
                    + Number(self.param.filter_limit);
                var totalRows = Number(self.param.totalRows);
                if (self.param.keep_summary_row == 1) --totalRows;
                if (offsetEnd < totalRows) {
                    $(this).css('display', 'inline');
                }
            })
            // bind the click event to trigger the ajax request with the new offset
            .click(function () {
                $(this).off('click');
                self.param.filter_offset = Number(self.param.filter_offset) + Number(self.param.filter_limit);
                self.reloadAjaxDataTable();
            })
        ;

        // Display the previous link if the current offset is not zero
        $('.dataTablePrevious', domElem)
            .each(function () {
                var offset = 1 + Number(self.param.filter_offset);
                if (offset != 1) {
                    $(this).css('display', 'inline');
                }
            }
        )
            // bind the click event to trigger the ajax request with the new offset
            // take care of the negative offset, we setup 0
            .click(
            function () {
                $(this).off('click');
                var offset = Number(self.param.filter_offset) - Number(self.param.filter_limit);
                if (offset < 0) { offset = 0; }
                self.param.filter_offset = offset;
                self.param.previous = 1;
                self.reloadAjaxDataTable();
            }
        );
    },

    handleEvolutionAnnotations: function (domElem) {
        var self = this;
        if (self.param.viewDataTable == 'graphEvolution'
            && $('.annotationView', domElem).length > 0) {
            // get dates w/ annotations across evolution period (have to do it through AJAX since we
            // determine placement using the elements created by jqplot)
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

                    // set position of evolution annotation icons
                    annotations.css({
                        top: -datatableFeatures.height() - annotationAxisHeight + noteSize / 2,
                        left: 6 // padding-left of .jqplot-graph element (in _dataTableViz_jqplotGraph.tpl)
                    });

                    piwik.annotations.placeEvolutionIcons(annotations, domElem);

                    // add new section under axis
                    datatableFeatures.append(annotations);

                    // reposition annotation icons every time the graph is resized
                    $('.piwik-graph', domElem).on('resizeGraph', function () {
                        piwik.annotations.placeEvolutionIcons(annotations, domElem);
                    });

                    // on hover of x-axis, show note icon over correct part of x-axis
                    $('span', annotations).hover(
                        function () { $(this).css('opacity', 1); },
                        function () {
                            if ($(this).attr('data-count') == 0) // only hide if there are no annotations for this note
                            {
                                $(this).css('opacity', 0);
                            }
                        }
                    );

                    // when clicking an annotation, show the annotation viewer for that period
                    $('span', annotations).click(function () {
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
                                                $(this).attr('title', viewAndAdd.replace("%s", oldDate));
                                                return false;
                                            }
                                        });
                                    }

                                    // change the tooltip of the clicked evolution icon
                                    if (manager.is(':hidden')) {
                                        spanSelf.attr('title', viewAndAdd.replace("%s", date));
                                    }
                                    else {
                                        spanSelf.attr('title', hideNotes.replace("%s", date));
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

                    var limit = $('.limitSelection>div>span', domElem).text();
                    if (!limit || 'undefined' === limit) {
                        limit = $(this).attr('filter_limit');
                    }
                    url += '&filter_limit=' + limit;

                    return url;
                })
            })
            .attr('href', function () {
                var format = $(this).attr('format');
                var method = $(this).attr('methodToCall');
                var segment = self.param.segment;
                var label = self.param.label;
                var idGoal = self.param.idGoal;
                var param_date = self.param.date;
                var date = $(this).attr('date');
                if (typeof date != 'undefined') {
                    param_date = date;
                }
                if (typeof self.param.dateUsedInGraph != 'undefined') {
                    param_date = self.param.dateUsedInGraph;
                }
                var period = self.param.period;

                // RSS does not work for period=range
                if (format == 'RSS'
                    && self.param.period == 'range') {
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

                if (typeof self.param.flat != "undefined") {
                    str += '&flat=' + (self.param.flat == 0 ? '0' : '1');
                    if (typeof self.param.include_aggregate_rows != "undefined" && self.param.include_aggregate_rows) {
                        str += '&include_aggregate_rows=1';
                    }
                } else {
                    str += '&expanded=1';
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

        var generateClickCallback = function (paramName, callbackAfterToggle) {
            return function () {
                close();
                self.param[paramName] = 1 - self.param[paramName];
                self.param.filter_offset = 0;
                delete self.param.totalRows;
                if (callbackAfterToggle) callbackAfterToggle();
                self.reloadAjaxDataTable(true, callbackSuccess);
                var data = {};
                data[paramName] = self.param[paramName];
                self.notifyWidgetParametersChange(domElem, data);
            };
        };

        var getText = function (text, addDefault) {
            text = _pk_translate(text);
            if (text.indexOf('%s') > 0) {
                text = text.replace('%s', '<br /><span class="action">&raquo; ');
                if (addDefault) text += ' (' + _pk_translate('CoreHome_Default') + ')';
                text += '</span>';
            }
            return text;
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
                }).width(width);
                close();
            }, 400);
        }
    },

    // Tell parent widget that the parameters of this table was updated,
    notifyWidgetParametersChange: function (domWidget, parameters) {
        var widget = $(domWidget).closest('[widgetId]');
        // trigger setParameters event on base element
        widget.trigger('setParameters', parameters);
    },

    truncate: function (domElemToTruncate, truncationOffset) {
        var self = this;

        domElemToTruncate = $(domElemToTruncate);

        if (typeof domElemToTruncate.data('originalText') != 'undefined') {
            // truncate only once. otherwise, the tooltip will show the truncated text as well.
            return;
        }

        if(domElemToTruncate.find('.truncationDisabled').length > 0) {
            return;
        }


        // make the original text (before truncation) available for others.
        // the .truncate plugins adds a title to the dom element but the .tooltip
        // plugin removes that again.
        domElemToTruncate.data('originalText', domElemToTruncate.text());

        if (typeof truncationOffset == 'undefined') {
            truncationOffset = 0;
        }
        var truncationLimit = 50;

        if (typeof self.param.idSubtable == 'undefined'
            && self.param.viewDataTable == 'tableAllColumns') {
            // when showing all columns in a subtable, space is restricted
            truncationLimit = 25;
        }

        truncationLimit += truncationOffset;
        domElemToTruncate.truncate(truncationLimit);

        var tooltipElem = $('.truncated', domElemToTruncate),
            customToolTipText = domElemToTruncate.attr('title');

        // if there's a title on the dom element, use this as the tooltip instead of
        // the one set by the truncate plugin
        if (customToolTipText) {
            // make sure browser doesn't add its own tooltip for the truncated element
            if (tooltipElem[0]) {
                tooltipElem.removeAttr('title');
            }

            tooltipElem = domElemToTruncate;
            tooltipElem.attr('title', customToolTipText);
        }

        // use tooltip (tooltip text determined by the 'title' attribute)
        tooltipElem.tooltip({
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
        $("td:first-child:odd", domElem).addClass('label labeleven');
        $("td:first-child:even", domElem).addClass('label labelodd');
        $("tr:odd td", domElem).slice(1).addClass('column columnodd');
        $("tr:even td", domElem).slice(1).addClass('column columneven');

        $('td span.label', domElem).each(function () { self.truncate($(this)); });
    },

    handleColumnHighlighting: function (domElem) {

        var maxWidth = {};
        var currentNthChild = null;
        var self = this;

        // higlight all columns on hover
        $('td', domElem).hover(
            function() {
                var table    = $(this).closest('table');
                var nthChild = $(this).parent('tr').children().index($(this)) + 1;
                var rows     = $('> tbody > tr', table);

                if (!maxWidth[nthChild]) {
                    maxWidth[nthChild] = 0;
                    rows.find("td:nth-child(" + (nthChild) + ") .value").each(function (index, element) {
                        var width    = $(element).width();
                        if (width > maxWidth[nthChild]) {
                            maxWidth[nthChild] = width;
                        }
                    });
                    rows.find("td:nth-child(" + (nthChild) + ") .value").each(function (index, element) {
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
        this.numberOfSubtables = $('tr.subDataTable', domElem)
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
                            '<span class="loadingPiwik" style="display:inline"><img src="plugins/Zeitgeist/images/loading-blue.gif" />' + _pk_translate('General_Loading') + '</span>' +
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
                self.repositionRowActions($(this));
            }
        ).size();
    },

    // tooltip for column documentation
    handleColumnDocumentation: function (domElem) {
        if ($('#dashboard').size() > 0) {
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

    // documentation for report
    handleReportDocumentation: function (domElem) {
        // don't display report documentation in dashboard
        if ($('#dashboard').size() > 0
            // or in Widgetize screen
            || $('.widgetContent').size() > 0
            // or in Widget export
            || $('.widget').size() > 0
            ) {
            return;
        }
        domElem = $(domElem);
        var doc = domElem.find('.reportDocumentation');

        var h2 = this._findReportHeader(domElem);
        if (doc.size() == 0 || doc.children().size() == 0) // if we can't find the element, or the element is empty
        {
            if (h2 && h2.size() > 0) {
                h2.find('a.reportDocumentationIcon').addClass('hidden');
            }
            return;
        }

        var icon = $('<a href="#"></a>');
        var docShown = false;

        icon.click(function () {
            if (docShown) {
                doc.stop(true, true).fadeOut(250);
            }
            else {
                var widthOrientation = domElem.find('table, canvas, object').eq(0);
                if (widthOrientation.size() > 0) {
                    var width = Math.min(widthOrientation.width(), doc.parent().innerWidth());
                    doc.css('width', (width - 2) + 'px');
                }
                doc.stop(true, true).fadeIn(250);
            }
            docShown = !docShown;
            return false;
        });

        icon.addClass('reportDocumentationIcon');
        if (h2 && h2.size() > 0) {
            // handle previously added icon
            var existingIcon = h2.find('a.reportDocumentationIcon');
            if (existingIcon.size() > 0) {
                existingIcon.replaceWith(icon);
            }
            else {
                // add icon
                h2.append('&nbsp;&nbsp;&nbsp;');
                h2.append(icon);

                h2.hover(function () {
                        $(this).find('a.reportDocumentationIcon').show();
                    },
                    function () {
                        $(this).find('a.reportDocumentationIcon').hide();
                    })
                    .click(
                    function () {
                        $(this).find('a.reportDocumentationIcon').click();
                    })
                    .css('cursor', 'pointer');
            }
        }
        else {
            //domElem.prepend(icon);
        }
    },

    handleRowActions: function (domElem) {
        this.doHandleRowActions(domElem.find('table > tbody > tr'));
    },

	handleCellTooltips: function(domElem) {
		domElem.find('span.cell-tooltip').tooltip({
			track: true,
			items: 'span',
			content: function() {
				return $(this).data('tooltip');
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
        $('.datatableRelatedReports span', domElem).each(function () {
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

                // do ajax request
                self.reloadAjaxDataTable(true, function (newReport) {
                    var newDomElem = self.dataTableLoaded(newReport, self.workingDivId);
                    hideShowRelatedReports(clicked);

                    // update header, if we can find it
                    var h2 = self._findReportHeader(newDomElem);
                    if (h2)
                        h2.text($(clicked).text());
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

DataTable.registerFooterIconHandler('table', switchToHtmlTable);
DataTable.registerFooterIconHandler('tableAllColumns', switchToHtmlTable);
DataTable.registerFooterIconHandler('tableGoals', switchToHtmlTable);
DataTable.registerFooterIconHandler('ecommerceOrder', switchToHtmlTable);
DataTable.registerFooterIconHandler('ecommerceAbandonedCart', switchToHtmlTable);

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
