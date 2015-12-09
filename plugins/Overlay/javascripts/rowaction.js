/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This file registers the Overlay row action on the pages report.
 */

function DataTable_RowActions_Overlay(dataTable) {
    this.dataTable = dataTable;
}

DataTable_RowActions_Overlay.prototype = new DataTable_RowAction;

DataTable_RowActions_Overlay.registeredReports = [];
DataTable_RowActions_Overlay.registerReport = function (handler) {
    DataTable_RowActions_Overlay.registeredReports.push(handler);
}


DataTable_RowActions_Overlay.prototype.onClick = function (actionA, tr, e) {
    if (!actionA.data('overlay-manipulated')) {
        actionA.data('overlay-manipulated', 1);

        var segment, link;

        var i = 0;
        for (i; i < DataTable_RowActions_Overlay.registeredReports.length; i++) {
            var report = DataTable_RowActions_Overlay.registeredReports[i];
            if (report
                && report.onClick
                && report.isAvailableOnReport
                && report.isAvailableOnReport(this.dataTable.param)) {
                var result = report.onClick.apply(this, arguments);

                if (!result || !result.link) {
                    return;
                }

                link = result.link;
                if (result.segment) {
                    segment = result.segment;
                }
                break;
            }
        }

        if (link) {
            var href = Overlay_Helper.getOverlayLink(this.dataTable.param.idSite, 'month', 'today', segment, link);

            actionA.attr({
                target: '_blank',
                href: href
            });
        }
    }

    return true;
};

DataTable_RowActions_Registry.register({

    name: 'Overlay',

    dataTableIcon: 'plugins/Overlay/images/overlay_icon.png',
    dataTableIconHover: 'plugins/Overlay/images/overlay_icon_hover.png',

    order: 30,

    dataTableIconTooltip: [
        _pk_translate('General_OverlayRowActionTooltipTitle'),
        _pk_translate('General_OverlayRowActionTooltip')
    ],

    createInstance: function (dataTable) {
        return new DataTable_RowActions_Overlay(dataTable);
    },

    isAvailableOnReport: function (dataTableParams) {
        // Overlay plugin only works when Transitions plugin is enabled
        if (!window.DataTable_RowActions_Transitions) {
            return false;
        }

        var i = 0;
        for (i; i < DataTable_RowActions_Overlay.registeredReports.length; i++) {
            var report = DataTable_RowActions_Overlay.registeredReports[i];
            if (report
                && report.isAvailableOnReport
                && report.isAvailableOnReport(dataTableParams)) {
                return true;
            }
        }

        return false;
    },

    isAvailableOnRow: function (dataTableParams, tr) {
        var transitions = DataTable_RowActions_Registry.getActionByName('Transitions');
        return transitions.isAvailableOnRow(dataTableParams, tr);
    }

});
