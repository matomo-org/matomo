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

     var UIControl = exports.UIControl;

     function getValueFromEvent(event)
     {
         return event.target.value ? event.target.value : $(event.target).attr('value');
     }

    /**
     * UI control that handles extra functionality for Actions datatables.
     *
     * @constructor
     */
    exports.InsightsDataTable = function (element) {
        this.parentAttributeParent = '';
        this.parentId = '';
        this.disabledRowDom = {}; // to handle double click on '+' row

        if ($(element).attr('data-table-onlyinsightsinit')) {
            // overview-widget
            UIControl.call(this, element);
            this._init($(element));
            this.workingDivId = this._createDivId();
            $(element).attr('id', this.workingDivId);

        } else {
            DataTable.call(this, element);
        }
    };

    $.extend(exports.InsightsDataTable.prototype, dataTablePrototype, {

        handleRowActions: function () {},

        _init: function (domElem) {
            this.initShowIncreaseOrDecrease(domElem);
            this.initOrderBy(domElem);
            this.initComparedToXPeriodsAgo(domElem);
            this.initFilterBy(domElem);
            this.setFixWidthToMakeEllipsisWork(domElem);

            $(domElem).find('select').material_select();
        },

        setFixWidthToMakeEllipsisWork: function (domElem) {
            var width = domElem.width();
            if (width) {
                $('td.label', domElem).width(parseInt(width * 0.50, 10));
            }

            var self = this;

            if (!this.windowResizeTableAttached) {
                this.windowResizeTableAttached = true;

                // on resize of the window we re-calculate everything.
                var timeout = null;
                var resizeDataTable = function () {

                    if (timeout) {
                        clearTimeout(timeout);
                    }

                    timeout = setTimeout(function () {
                        var isInDom = domElem && domElem[0] && document && document.body && document.body.contains(domElem[0]);
                        if (isInDom) {
                            // as domElem might have been removed by now we check whether domElem actually still is in dom
                            // and do this expensive operation only if needed.
                            $('td.label', domElem).width('');
                            self.setFixWidthToMakeEllipsisWork(domElem);
                        } else {
                            $(window).off('resize', resizeDataTable);
                        }

                        timeout = null;
                    }, Math.floor((Math.random() * 80) + 220));
                    // we randomize it just a little to not process all dataTables at similar time but to have a little
                    // delay in between for smoother resizing. we want to do it between 300 and 400ms
                }

                $(window).on('resize', resizeDataTable);
            }
        },

        _changeParameter: function (params) {

            var widgetParams = {};

            for (var index in params) {
                if (params.hasOwnProperty(index)) {
                    this.param[index]   = params[index];
                    widgetParams[index] = params[index];
                }
            }

            this.notifyWidgetParametersChange(this.$element, widgetParams);
        },

        _changeParameterAndReload: function (params) {
            this._changeParameter(params);
            this.reloadAjaxDataTable(true);
        },

        initShowIncreaseOrDecrease: function (domElem) {
            var self = this;
            $('[name=showIncreaseOrDecrease]', domElem).bind('change', function (event) {
                var value = getValueFromEvent(event);

                self._changeParameterAndReload({
                    limit_increaser: (value == 'both' || value == 'increase') ? '5' : '0',
                    limit_decreaser: (value == 'both' || value == 'decrease') ? '5' : '0'
                });
            });
        },

        initOrderBy: function (domElem) {
            var self = this;
            $('[name=orderBy]', domElem).bind('change', function (event) {
                self._changeParameterAndReload({order_by: getValueFromEvent(event)});
            });
            $('th[name=orderBy]', domElem).bind('click', function (event) {
                self._changeParameterAndReload({order_by: getValueFromEvent(event)});
            });
        },

        initComparedToXPeriodsAgo: function (domElem) {
            var self = this;
            $('[name=comparedToXPeriodsAgo]', domElem).bind('change', function (event) {
                self._changeParameterAndReload({compared_to_x_periods_ago: getValueFromEvent(event)});
            });
        },

        initFilterBy: function (domElem) {
            var self = this;
            $('[name=filterBy]', domElem).bind('change', function (event) {
                self._changeParameterAndReload({filter_by: getValueFromEvent(event)});
            });
        }
    });

})(jQuery, require);
