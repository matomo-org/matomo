/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This file registers the page performance row action on the pages report.
 */

(function () {

    var actionName = 'PagePerformance';

    function getDataTableFromApiMethod(apiMethod)
    {
        var div = $(require('piwik/UI').DataTable.getDataTableByReport(apiMethod));
        if (div.length && div.data('uiControlObject')) {
            return div.data('uiControlObject');
        }
    }

    function DataTable_RowActions_PagePerformance(dataTable) {
        this.dataTable = dataTable;
        this.actionName = actionName;

        // has to be overridden in subclasses
        this.trEventName = 'matomoTriggerPagePerformanceAction';
    }

    DataTable_RowActions_PagePerformance.prototype = new DataTable_RowAction();

    DataTable_RowActions_PagePerformance.prototype.performAction = function (label, tr, e, originalRow) {
        var apiMethod = this.dataTable.param.module + '.' + this.dataTable.param.action;
        this.openPopover(apiMethod, label);
    };

    DataTable_RowActions_PagePerformance.prototype.openPopover = function (apiMethod, label) {
        var urlParam = apiMethod + ':' + label;
        DataTable_RowAction.prototype.openPopover.apply(this, [urlParam]);
    };

    DataTable_RowActions_PagePerformance.prototype.doOpenPopover = function (urlParam) {
        var urlParamParts = urlParam.split(':');
        var apiMethod = urlParamParts.shift();
        var label = decodeURIComponent(urlParamParts.shift());

        PagePerformance.show(apiMethod, label);
    };

    DataTable_RowActions_Registry.register({

        name: actionName,

        dataTableIcon: 'icon-page-performance',

        order: 50,

        dataTableIconTooltip: [
            _pk_translate('PagePerformance_RowActionTitle'),
            _pk_translate('PagePerformance_RowActionDescription')
        ],

        isAvailableOnReport: function (dataTableParams) {
            return dataTableParams.module == 'Actions'
                && (dataTableParams.action == 'getPageUrls' || dataTableParams.action == 'getEntryPageUrls' ||
                    dataTableParams.action == 'getExitPageUrls' || dataTableParams.action == 'getPageUrlsFollowingSiteSearch' ||
                    dataTableParams.action == 'getPageTitles' || dataTableParams.action == 'getPageTitlesFollowingSiteSearch');
        },

        isAvailableOnRow: function (dataTableParams, tr) {
            return !tr.is('.totalsRow');
        },

        createInstance: function (dataTable, param) {
            if (dataTable !== null && typeof dataTable.pagePerformanceInstance != 'undefined') {
                return dataTable.pagePerformanceInstance;
            }

            if (dataTable === null && param) {
                // when row evolution is triggered from the url (not a click on the data table)
                // we look for the data table instance in the dom
                var report = param.split(':')[0];
                var div = $(require('piwik/UI').DataTable.getDataTableByReport(report));
                if (div.length && div.data('uiControlObject')) {
                    dataTable = div.data('uiControlObject');
                    if (typeof dataTable.pagePerformanceInstance != 'undefined') {
                        return dataTable.pagePerformanceInstance;
                    }
                }
            }

            var instance = new DataTable_RowActions_PagePerformance(dataTable);
            if (dataTable !== null) {
                dataTable.pagePerformanceInstance = instance;
            }
            return instance;
        },

    });

})();
