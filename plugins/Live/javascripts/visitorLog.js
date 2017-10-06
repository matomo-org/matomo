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

            $('.visitorLogIconWithDetails>img').each(function () {
                $(this).tooltip({
                    items: 'img',
                    track: true,
                    show: false,
                    hide: false,
                    content: function () {
                        return $('<ul>').html($('ul', $(this).closest('.visitorLogIconWithDetails')).html());
                    },
                    tooltipClass: 'small',
                    open: function () {
                        tooltipIsOpened = true;
                    },
                    close: function () {
                        tooltipIsOpened = false;
                    }
                });
            });

            $('.visitorLogTooltip').each(function () {
                $(this).tooltip({
                    track: true,
                    show: false,
                    hide: false,
                    tooltipClass: 'small',
                    content: function() {
                        var title = $(this).attr('title');
                        return $('<a>').text( title ).html().replace(/\n/g, '<br />');
                    },
                    open: function () {
                        tooltipIsOpened = true;
                    },
                    close: function () {
                        tooltipIsOpened = false;
                    }
                });
            });

            // show refresh icon for duplicate page views in a row
            $("ol.visitorLog").each(function () {
                var prevelement;
                var prevhtml;
                var counter = 0, duplicateCounter = 0;
                $(this).find("> li").each(function () {
                    counter++;
                    $(this).val(counter);
                    var current = $(this).html();

                    if (current == prevhtml) {
                        duplicateCounter++;
                        $(this).find('>div').prepend($("<span>"+(duplicateCounter+1)+"</span>").attr({'class': 'repeat icon-refresh', 'title': _pk_translate('Live_PageRefreshed')}));
                        prevelement.addClass('duplicate');

                    } else {
                        duplicateCounter = 0;
                    }

                    prevhtml = current;
                    prevelement = $(this);

                    var $this = $(this);
                    var tooltipIsOpened = false;

                    $('a', $this).on('focus', function () {
                        // see https://github.com/piwik/piwik/issues/4099
                        if (tooltipIsOpened) {
                            $this.tooltip('close');
                        }
                    });

                });
            });

            $("ol.visitorLog > li:not(.duplicate) .icon-refresh").parents('li').each(function(){
                $(this).attr('origtitle', $(this).attr('title'));
                $(this).attr('title', _pk_translate('Live_ClickToViewAllActions'));
                $(this).click(function(e){
                    e.preventDefault();
                    $(this).prevUntil('li:not(.duplicate)').removeClass('duplicate').find('.icon-refresh').hide();
                    var elem = $(this);
                    window.setTimeout(function() {
                        elem.attr('title', elem.attr('origtitle'));
                        elem.attr('origtitle', null);
                    }, 150);
                    $(this).off('click').find('.icon-refresh').hide();
                    return false;
                });

            });

            $("ol.visitorLog > li").tooltip({
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

            // launch visitor profile on visitor profile link click
            this.$element.on('click', '.visitor-log-visitor-profile-link', function (e) {
                e.preventDefault();
                broadcast.propagateNewPopoverParameter('visitorProfile', $(this).attr('data-visitor-id'));
                return false;
            });
        }
    });

})(jQuery, require);
