/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <piwik-c3graph>
 */
(function (jQuery, require) {
    angular.module('piwikApp').component('piwikC3Graph', {
        templateUrl: 'plugins/CoreVisualizations/angularjs/c3graph/c3graph.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            data: '<',
            props: '<',
            params: '<'
        },
        controller: C3GraphController
    });

    C3GraphController.$inject = ['$element'];

    function C3GraphController($element) {
        var vm = this;

        vm.$onInit = function () {
            // TODO: cleanup on destroy
            vm.chart = c3.generate({
                data: {
                    json: vm.data,
                    type: 'bar',
                    keys: {
                        x: 'label',
                        value: vm.props.columns_to_display
                    },
                    names: {
                        nb_uniq_visitors: 'Unique Visitors'
                    }
                },
                bar: {
                    width: {
                        ratio: 0.5 // this makes bar width 50% of length between ticks
                    }
                    // or
                    //width: 100 // this makes bar width 100px
                },
                axis: {
                    x: {
                        type: 'category'
                    }
                },
                bindto: $element.find('.c3Graph')[0]
            });
        };
    }

    var exports = require('piwik/UI'),
        DataTable = exports.DataTable;

    exports.C3GraphDataTable = function (element) {
        DataTable.call(this, element);
    };

    $.extend(exports.C3GraphDataTable.prototype, DataTable.prototype, {
        init: function () {
            DataTable.prototype.init.call(this);
        },
    });

    DataTable.registerFooterIconHandler('graphVerticalBarC3', DataTable.switchToGraph);

})(jQuery, require);
