/**
 * Piwik - free/libre analytics platform
 *
 * Visitor profile popup control.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        DataTable = exports.DataTable,
        dataTablePrototype = DataTable.prototype;

    /**
     * DataTable UI class for jqPlot graph datatable visualizations.
     *
     * @constructor
     */
    exports.VisitorLog = function (element) {
        DataTable.call(this, element);
    };

    $.extend(exports.VisitorLog.prototype, dataTablePrototype, {

        handleColumnHighlighting: function () {

        },

        setFixWidthToMakeEllipsisWork: function () {

        },

        /**
         * Initializes this class.
         */
        init: function () {
            dataTablePrototype.init.call(this);

            // Replace duplicated page views by a NX count instead of using too much vertical space
            $("ol.visitorLog").each(function () {
                var prevelement;
                var prevhtml;
                var counter = 0;
                $(this).find("li").each(function () {
                    counter++;
                    $(this).val(counter);
                    var current = $(this).html();
                    if (current == prevhtml) {
                        var repeat = prevelement.find(".repeat");
                        if (repeat.length) {
                            repeat.html((parseInt(repeat.html()) + 1) + "x");
                        } else {
                            prevelement.append($("<em>2x</em>").attr({'class': 'repeat', 'title': _pk_translate('Live_PageRefreshed')}));
                        }
                        $(this).hide();
                    } else {
                        prevhtml = current;
                        prevelement = $(this);
                    }

                    var $this = $(this);
                    var tooltipIsOpened = false;

                    $('a', $this).on('focus', function () {
                        // see https://github.com/piwik/piwik/issues/4099
                        if (tooltipIsOpened) {
                            $this.tooltip('close');
                        }
                    });

                    $this.tooltip({
                        track: true,
                        show: false,
                        hide: false,
                        content: function() {
                            var title = $(this).attr('title');
                            return $('<a>').text( title ).html().replace(/\n/g, '<br />');
                        },
                        tooltipClass: 'small',
                        open: function() { tooltipIsOpened = true; },
                        close: function() { tooltipIsOpened = false; }
                    });
                });
            });

            // launch visitor profile on visitor profile link click
            this.$element.on('click', '.visitor-log-visitor-profile-link', function (e) {
                e.preventDefault();
                broadcast.propagateNewPopoverParameter('visitorProfile', $(this).attr('data-visitor-id'));
                return false;
            });
        }
    });

})(jQuery, require);