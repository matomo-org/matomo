/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This series picker component is a popup that displays a list of metrics/row
 * values that can be selected. It's used by certain datatable visualizations
 * to allow users to select different data series for display.
 *
 * Inputs:
 * - multiselect: true if the picker should allow selecting multiple items, false
 *                if otherwise.
 * - selectableColumns: the list of selectable metric values. must be a list of
 *                      objects with the following properties:
 *                      * column: the ID of the column, eg, nb_visits
 *                      * translation: the translated text for the column, eg, Visits
 * - selectableRows: the list of selectable row values. must be a list of objects
 *                   with the following properties:
 *                   * matcher: the ID of the row
 *                   * label: the display text for the row
 * - selectedColumns: the list of selected columns. should be a list of strings
 *                    that correspond to the 'column' property in selectableColumns.
 * - selectedRows: the list of selected rows. should be a list of strings that
 *                 correspond to the 'matcher' property in selectableRows.
 * - onSelect: expression invoked when a user makes a new selection. invoked
 *             with the following local variables:
 *             * columns: list of IDs of new selected columns, if any
 *             * rows: list of matchers of new selected rows, if any
 *
 * Usage:
 * <piwik-series-picker />
 */
(function () {
    angular.module('piwikApp').component('piwikSeriesPicker', {
        templateUrl: 'plugins/CoreVisualizations/angularjs/series-picker/series-picker.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            multiselect: '<',
            selectableColumns: '<',
            selectableRows: '<',
            selectedColumns: '<',
            selectedRows: '<',
            onSelect: '&'
        },
        controller: SeriesPickerController
    });

    SeriesPickerController.$inject = [];

    function SeriesPickerController() {
        var vm = this;
        vm.isPopupVisible = false;

        // note: column & row states are separated since it's technically possible (though
        // highly improbable) that a row value matcher will be the same as a recognized column.
        vm.columnStates = {};
        vm.rowStates = {};
        vm.optionSelected = optionSelected;
        vm.onLeavePopup = onLeavePopup;
        vm.$onInit = $onInit;

        function $onInit() {
            vm.columnStates = getInitialOptionStates(vm.selectableColumns, vm.selectedColumns);
            vm.rowStates = getInitialOptionStates(vm.selectableRows, vm.selectedRows);
        }

        function getInitialOptionStates(allOptions, selectedOptions) {
            var states = {};

            allOptions.forEach(function (columnConfig) {
                states[columnConfig.column || columnConfig.matcher] = false;
            });

            selectedOptions.forEach(function (column) {
                states[column] = true;
            });

            return states;
        }

        function optionSelected(optionValue, optionStates) {
            if (!vm.multiselect) {
                unselectOptions(vm.columnStates);
                unselectOptions(vm.rowStates);
            }

            optionStates[optionValue] = !optionStates[optionValue];

            if (optionStates[optionValue]) {
                triggerOnSelectAndClose();
            }
        }

        function onLeavePopup() {
            vm.isPopupVisible = false;

            if (optionsChanged()) {
                triggerOnSelectAndClose();
            }
        }

        function triggerOnSelectAndClose() {
            if (!vm.onSelect) {
                return;
            }

            vm.isPopupVisible = false;

            vm.onSelect({
                columns: getSelected(vm.columnStates),
                rows: getSelected(vm.rowStates)
            });
        }

        function optionsChanged() {
            return !arrayEqual(getSelected(vm.columnStates), vm.selectedColumns)
                || !arrayEqual(getSelected(vm.rowStates), vm.selectedRows);
        }

        function arrayEqual(lhs, rhs) {
            if (lhs.length !== rhs.length) {
                return false;
            }

            return lhs
                .filter(function (element) { return rhs.indexOf(element) === -1; })
                .length === 0;
        }

        function unselectOptions(optionStates) {
            Object.keys(optionStates).forEach(function (optionName) {
                optionStates[optionName] = false;
            });
        }

        function getSelected(optionStates) {
            return Object.keys(optionStates).filter(function (optionName) {
                return !! optionStates[optionName];
            });
        }
    }
})();