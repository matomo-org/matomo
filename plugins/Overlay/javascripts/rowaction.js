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

DataTable_RowActions_Overlay.prototype.onClick = function (actionA, tr, e) {
    if (!actionA.data('overlay-manipulated')) {
        actionA.data('overlay-manipulated', 1);

        var segment, link;

        if (DataTable_RowActions_Transitions.isActionCustomDimensionReport(this.dataTable.param)) {

            link = this.getLabelFromTr(tr);
            if (link && link.substr(0, 1) === '@') {
                link = link.substr(1);
            }

            link = 'http://' + unescape(link);

            var subtable = tr.closest('table');
            if (subtable.is('.subDataTable')) {
                var prev = subtable.closest('tr').prev();
                segment = prev.attr('data-segment-filter');
            }
        } else {

            link = tr.find('> td:first > a').attr('href');
            link = $('<textarea>').html(link).val(); // remove html entities
        }


        var href = Overlay_Helper.getOverlayLink(this.dataTable.param.idSite, 'month', 'today', segment, link);

        actionA.attr({
            target: '_blank',
            href: href
        });
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

        if (DataTable_RowActions_Transitions.isActionCustomDimensionReport(dataTableParams)) {
            return true;
        }

        return DataTable_RowActions_Transitions.isPageUrlReport(dataTableParams.module, dataTableParams.action);
    },

    isAvailableOnRow: function (dataTableParams, tr) {
        var transitions = DataTable_RowActions_Registry.getActionByName('Transitions');
        return transitions.isAvailableOnRow(dataTableParams, tr);
    }

});
