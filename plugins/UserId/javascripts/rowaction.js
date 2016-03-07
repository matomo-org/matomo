/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This file registers the visitor details overlay row action on the user IDs list page.
 */
(function () {

    var actionName = 'visitorDetails';

    // Get an URL to visitor details popover in the data-url-label of table row's <tr>
    function getVisitorDetailsUrl(tr) {
        return $(tr).data('url-label');
    }

    function DataTable_RowActions_VisitorDetails(dataTable) {
        this.dataTable = dataTable;
        this.actionName = actionName;
        this.trEventName = 'piwikTriggerVisitorDetailsAction';
    }

    DataTable_RowActions_VisitorDetails.prototype = new DataTable_RowAction();

    DataTable_RowActions_VisitorDetails.prototype.performAction = function (label, tr, e) {
        DataTable_RowAction.prototype.openPopover.apply(this, [getVisitorDetailsUrl(tr)]);
    };

    DataTable_RowActions_VisitorDetails.prototype.doOpenPopover = function (urlParam) {
        var popoverUrl = urlParam;
        Piwik_Popover.createPopupAndLoadUrl(popoverUrl, _pk_translate('Live_VisitorProfile'), 'visitor-profile-popup');
    };

    DataTable_RowActions_Registry.register({

        name: actionName,

        dataTableIcon: 'plugins/UserId/images/visitordetails.png',
        dataTableIconHover: 'plugins/UserId/images/visitordetails-hover.png',

        order: 30,

        dataTableIconTooltip: [
            _pk_translate('Live_ViewVisitorProfile'),
            ''
        ],

        isAvailableOnReport: function (dataTableParams, undefined) {
            return dataTableParams.module == 'UserId';
        },

        isAvailableOnRow: function (dataTableParams, tr) {
            return tr.data('url-label').length > 0;
        },

        createInstance: function (dataTable, param) {
            if (dataTable !== null && typeof dataTable.visitorDetailsInstance != 'undefined') {
                return dataTable.segmentVisitorLogInstance;
            }

            var instance = new DataTable_RowActions_VisitorDetails(dataTable);
            if (dataTable !== null) {
                dataTable.visitorDetailsInstance = instance;
            }

            return instance;
        }
    });
})();