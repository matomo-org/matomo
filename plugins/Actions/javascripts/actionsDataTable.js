/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        DataTable = exports.DataTable,
        dataTablePrototype = DataTable.prototype;

    // helper function for ActionDataTable
    function getLevelFromClass(style) {
        if (!style || typeof style == "undefined") return 0;

        var currentLevel = 0;

        var currentLevelIndex = style.indexOf('level');
        if (currentLevelIndex >= 0) {
            currentLevel = Number(style.slice(currentLevelIndex + 5, currentLevelIndex + 6));
        }
        return currentLevel;
    }

    /**
     * UI control that handles extra functionality for Actions datatables.
     *
     * @constructor
     */
    exports.ActionsDataTable = function (element) {
        this.parentAttributeParent = '';
        this.parentId = '';
        this.disabledRowDom = {}; // to handle double click on '+' row

        DataTable.call(this, element);
    };

    $.extend(exports.ActionsDataTable.prototype, dataTablePrototype, {

        //see dataTable::bindEventsAndApplyStyle
        bindEventsAndApplyStyle: function (domElem, rows) {
            var self = this;

            self.cleanParams();
            self.preBindEventsAndApplyStyleHook(domElem, rows);

            if (!rows) {
                rows = $('tr', domElem);
            }

            self.numberOfSubtables = rows.filter('.subDataTable').click(function () {
                if (!self.param.filter_pattern_recursive) {
                    // we don't display the link on the row with subDataTable when we are already
                    // printing all the subTables (case of recursive search when the content is
                    // including recursively all the subtables
                    self.onClickActionSubDataTable(this);
                }
            }).length;
            self.applyCosmetics(domElem, rows);
            self.handleColumnHighlighting(domElem);
            self.handleRowActions(domElem, rows);
            self.handleLimit(domElem);
            self.handlePeriod(domElem);
            self.handleAnnotationsButton(domElem);
            self.handleExportBox(domElem);
            self.handleSort(domElem);
            self.handleOffsetInformation(domElem);
            if (self.workingDivId != undefined) {
                var dataTableLoadedProxy = function (response) {
                    self.dataTableLoaded(response, self.workingDivId);
                };

                self.handleConfigurationBox(domElem, dataTableLoadedProxy);
                self.handleSearchBox(domElem, dataTableLoadedProxy);
            }

            self.handleColumnDocumentation(domElem);
            self.handleRelatedReports(domElem);
            self.handleTriggeredEvents(domElem);
            self.handleCellTooltips(domElem);
            self.setFixWidthToMakeEllipsisWork(domElem);
            self.handleSummaryRow(domElem);
            self.openSubtableFromLevel0IfOnlyOneSubtableGiven(domElem);
            self.postBindEventsAndApplyStyleHook(domElem, rows);
        },

        openSubtableFromLevel0IfOnlyOneSubtableGiven: function (domElem) {
            var $subtables = domElem.find('.subDataTable');
            var hasOnlyOneSubtable = $subtables.length === 1;

            if (hasOnlyOneSubtable) {
                var hasOnlyOneRow = domElem.find('tbody tr.level0').length === 1;

                if (hasOnlyOneRow) {
                    var $labels = $subtables.find('.label');
                    if ($labels.length) {
                        $labels.first().click();
                    }
                }
            }
        },

        openSubtableFromSubtableIfOnlyOneSubtableGiven: function (domElem) {
            var hasOnlyOneRow = domElem.length === 1;
            var hasOnlyOneSubtable = domElem.hasClass('subDataTable');

            if (hasOnlyOneRow && hasOnlyOneSubtable) {
                // when subtable is loaded
                var $labels = domElem.find('.label');
                if ($labels.length) {
                    $labels.first().click();
                }
            }
        },

        //see dataTable::applyCosmetics
        applyCosmetics: function (domElem, rows) {
            var self = this;
            var rowsWithSubtables = rows.filter('.subDataTable');

            rowsWithSubtables.css('font-weight', 'bold');

            $("th:first-child", domElem).addClass('label');
            $(rowsWithSubtables)
                .each(function () {
                    if (self.param.filter_pattern_recursive) {
                        $(this).addClass('expanded');
                        // remove tooltip "Click this row to expand or contract the subtable"
                        $(this).attr('title', '');
                    }
                });

            var rootRow = rows.first().prev();

            // we look at the style of the row before the new rows to determine the rows'
            // level
            var level = rootRow.length ? getLevelFromClass(rootRow.attr('class')) + 1 : 0;

            rows.each(function () {
                var currentStyle = $(this).attr('class') || '';

                if (currentStyle.indexOf('level') == -1) {
                    $(this).addClass('level' + level);
                }

                // we add an attribute parent that contains the ID of all the parent categories
                // this ID is used when collapsing a parent row, it searches for all children rows
                // which 'parent' attribute's value contains the collapsed row ID
                $(this).prop('parent', function () {
                    return self.parentAttributeParent + ' ' + self.parentId;
                });
            });

            self.addOddAndEvenClasses(domElem);
        },

        addOddAndEvenClasses: function(domElem) {
            // empty
        },

        handleRowActions: function (domElem, rows) {
            this.doHandleRowActions(rows);
        },

        // Called when the user click on an actionDataTable row
        onClickActionSubDataTable: function (domElem) {
            var self = this;

            // get the idSubTable
            var idSubTable = $(domElem).attr('id');

            var divIdToReplaceWithSubTable = 'subDataTable_' + idSubTable;

            var $insertAfter = $(domElem).nextUntil(':not(.comparePeriod):not(.comparisonRow)').last();
            if (!$insertAfter.length) {
                $insertAfter = $(domElem);
            }

            var NextStyle = $insertAfter.next().attr('class');
            var CurrentStyle = $(domElem).attr('class');

            var currentRowLevel = getLevelFromClass(CurrentStyle);
            var nextRowLevel = getLevelFromClass(NextStyle);

            // if the row has not been clicked
            // which is the same as saying that the next row level is equal or less than the current row
            // because when we click a row the level of the next rows is higher (level2 row gives level3 rows)
            if (currentRowLevel >= nextRowLevel) {
                //unbind click to avoid double click problem
                $(domElem).off('click');
                self.disabledRowDom = $(domElem);

                var numberOfColumns = $(domElem).children().length;
                $insertAfter.after('\
                <tr id="' + divIdToReplaceWithSubTable + '" class="cellSubDataTable">\
                    <td colspan="' + numberOfColumns + '">\
                            <span class="loadingPiwik" style="display:inline"><img src="plugins/Morpheus/images/loading-blue.gif" /> Loading...</span>\
                    </td>\
                </tr>\
                ');
                var savedActionVariable = self.param.action;

                // reset all the filters from the Parent table
                var filtersToRestore = self.resetAllFilters();

                // Do not reset the sorting filters that must be applied to sub tables
                this.param['filter_sort_column'] = filtersToRestore['filter_sort_column'];
                this.param['filter_sort_order'] = filtersToRestore['filter_sort_order'];
                this.param['enable_filter_excludelowpop'] = filtersToRestore['enable_filter_excludelowpop'];

                self.param.idSubtable = idSubTable;
                self.param.action = self.props.subtable_controller_action;

                var extraParams = {};
                extraParams.comparisonIdSubtables = self.getComparisonIdSubtables($(domElem));

                self.reloadAjaxDataTable(false, function (resp) {
                    self.actionsSubDataTableLoaded(resp, idSubTable);
                    self.repositionRowActions($(domElem));
                }, extraParams);
                self.param.action = savedActionVariable;

                self.restoreAllFilters(filtersToRestore);

                delete self.param.idSubtable;
            }
            // else we toggle all these rows
            else {
                var isExpanded = $(domElem).hasClass('subDataTable') && $(domElem).hasClass('expanded');

                $(domElem).siblings().each(function () {
                    var parents = $(this).prop('parent').split(' ');
                    if (parents) {
                        if (parents.indexOf(idSubTable) >= 0
                            || parents.indexOf('subDataTable_' + idSubTable) >= 0) {
                            if (!isExpanded) {
                                $(this).css('display', '').removeClass('hidden');

                                // unroll everything if the row is already opened
                                var NextStyle = $(this).next().attr('class');
                                var CurrentStyle = $(this).attr('class');

                                var currentRowLevel = getLevelFromClass(CurrentStyle);
                                var nextRowLevel = getLevelFromClass(NextStyle);

                                if (currentRowLevel < nextRowLevel) {
                                    $(this).addClass('expanded');
                                }
                            }
                            else {
                                $(this).css('display', 'none').addClass('hidden');
                            }
                            self.repositionRowActions($(domElem));
                        }
                    }
                });

                self.$element.trigger('piwik:actionsSubTableToggled');
            }

            // toggle the +/- image
            $(domElem).toggleClass('expanded');
        },

        //called when the full table actions is loaded
        dataTableLoaded: function (response, workingDivId) {
            var content = $(response);
            var idToReplace = workingDivId || $(content).attr('id');

            //reset parents id
            self.parentAttributeParent = '';
            self.parentId = '';

            var dataTableSel = $('#' + idToReplace);

            // keep the original list of related reports
            var oldReportsElem = $('.datatableRelatedReports', dataTableSel);
            $('.datatableRelatedReports', content).replaceWith(oldReportsElem);

            dataTableSel.replaceWith(content);

            content.trigger('piwik:dataTableLoaded');

            piwikHelper.compileVueEntryComponents(content);

            piwikHelper.lazyScrollTo(content[0], 400);

            return content;
        },

        // Called when a set of rows for a category of actions is loaded
        actionsSubDataTableLoaded: function (response, idSubTable) {
            var self = this;
            var idToReplace = 'subDataTable_' + idSubTable;
            var root = $('#' + self.workingDivId);

            var response = $(response);
            self.parentAttributeParent = $('tr#' + idToReplace).prev().prop('parent');
            self.parentId = idToReplace;

            $('tr#' + idToReplace, root).after(response).remove();

            var requiredColumnCount = 0, availableColumnCount = 0;

            response.prev().find('td').each(function(){ requiredColumnCount += $(this).attr('colspan') || 1; });
            response.find('td').each(function(){ availableColumnCount += $(this).attr('colspan') || 1; });

            var missingColumns = requiredColumnCount - availableColumnCount;
            for (var i = 0; i < missingColumns; i++) {
                // if the subtable has fewer columns than the parent table, add some columns.
                // this happens for example, when the parent table has performance metrics and the subtable doesn't.
                response.append('<td>-</td>');
            }

            var re = /subDataTable_(\d+)/;
            var ok = re.exec(self.parentId);
            if (ok) {
                self.parentId = ok[1];
            }

            // we execute the bindDataTableEvent function for the new DIV
            self.bindEventsAndApplyStyle($('#' + self.workingDivId), response);

            self.$element.trigger('piwik:actionsSubDataTableLoaded');

            //bind back the click event (disabled to avoid double-click problem)
            self.disabledRowDom.click(
                function () {
                    self.onClickActionSubDataTable(this)
                });

            self.openSubtableFromSubtableIfOnlyOneSubtableGiven(response);
        }
    });

})(jQuery, require);
