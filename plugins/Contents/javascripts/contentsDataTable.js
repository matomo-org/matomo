/*!
 * Piwik - free/libre analytics platform
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
    exports.ContentsDataTable = function (element) {
        DataTable.call(this, element);
    };

    $.extend(exports.ContentsDataTable.prototype, dataTablePrototype, {

        //see dataTable::bindEventsAndApplyStyle
        _init: function (domElem) {
            domElem.find('table > tbody > tr').each(function (index, tr) {
                var $tr  = $(tr);
                var $td  = $tr.find('.label .value');
                var text = $td.text().trim();

                if (text.search('^https?:\/\/[^\/]+') !== -1) {
                    if (text.match(/(.jpg|.gif|.png|.svg)$/)) {
                        if (window.encodeURI) {
                            text = window.encodeURI(text);
                        }
                        $td.tooltip({
                            track: true,
                            items: 'span',
                            content: '<p><img style="max-width: 150px;max-height:150px;" src="' + text + '"/><br />' + text + '</p>',
                            tooltipClass: 'rowActionTooltip',
                            show: false,
                            hide: false
                        });
                    }
                }
            });

        }
    });

})(jQuery, require);