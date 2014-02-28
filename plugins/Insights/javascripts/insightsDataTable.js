/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

 (function ($, require) {

    var exports = require('piwik/UI'),
        DataTable = exports.DataTable,
        dataTablePrototype = DataTable.prototype;

    /**
     * UI control that handles extra functionality for Actions datatables.
     * 
     * @constructor
     */
    exports.InsightsDataTable = function (element) {
        this.parentAttributeParent = '';
        this.parentId = '';
        this.disabledRowDom = {}; // to handle double click on '+' row

        DataTable.call(this, element);
    };

    $.extend(exports.InsightsDataTable.prototype, dataTablePrototype, {

        handleRowActions: function () {},

        _init: function (domElem) {
            this.initMinGrowthPercentage(domElem);
            this.initMinVisitsPercent(domElem);
            this.initBasedOnTotalMetric(domElem);
            this.initShowIncreaseOrDecrease(domElem);
            this.initOrderBy(domElem);
            this.initComparedToXPeriodsAgo(domElem);
            this.initFilterBy(domElem);
        },

        initShowIncreaseOrDecrease: function (domElem) {
            var self = this;
            $('[name=showIncreaseOrDecrease]', domElem).bind('change', function (event) {
                var value = event.target.value;

                self.param.limit_increaser = (value == 'both' || value == 'increase') ? '5' : '0';
                self.param.limit_decreaser = (value == 'both' || value == 'decrease') ? '5' : '0';
                self.reloadAjaxDataTable(true);
            });
        },

        initMinGrowthPercentage: function (domElem) {
            var self = this;
            $('[name=minGrowthPercent]', domElem).bind('change', function (event) {
                self.param.min_growth_percent = event.target.value;
                self.reloadAjaxDataTable(true);
            });
        },

        initOrderBy: function (domElem) {
            var self = this;
            $('[name=orderBy]', domElem).bind('change', function (event) {
                self.param.order_by = event.target.value;
                self.reloadAjaxDataTable(true);
            });
        },

        initMinVisitsPercent: function (domElem) {
            var self = this;
            $('[name=minVisitsPercent]', domElem).bind('change', function (event) {
                self.param.min_visits_percent = event.target.value;
                self.reloadAjaxDataTable(true);
            });
        },

        initBasedOnTotalMetric: function (domElem) {
            var self = this;
            $('[name=basedOnTotalMetric]', domElem).bind('change', function (event) {
                self.param.based_on_total_metric = event.target.value;
                self.reloadAjaxDataTable(true);
            });
        },

        initComparedToXPeriodsAgo: function (domElem) {
            var self = this;
            $('[name=comparedToXPeriodsAgo]', domElem).bind('change', function (event) {
                self.param.compared_to_x_periods_ago = event.target.value;
                self.reloadAjaxDataTable(true);
            });
        },

        initFilterBy: function (domElem) {
            var self = this;
            $('[name=filterBy]', domElem).bind('change', function (event) {
                self.param.filter_by = event.target.value;
                self.reloadAjaxDataTable(true);
            });
        }
    });

})(jQuery, require);