/**
 * Piwik - Web Analytics
 *
 * Visitor profile popup control.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require, doc) {

    var piwik = require('piwik'),
        exports = require('piwik/UI');

    /**
     * Sets up and handles events for the visitor profile popup.
     * 
     * @param {Element} element The HTML element returned by the Live.getVisitorLog controller
     *                          action. Should have the CSS class 'visitor-profile'.
     */
    var VisitorProfileControl = function (element) {
        this.$element = $(element);

        this._setupControl();
        this._bindEventCallbacks();

        this.$element.focus();
    };

    /**
     * Initializes all elements w/ the .visitor-profile CSS class as visitor profile popups,
     * if the element has not already been initialized.
     */
    VisitorProfileControl.initElements = function () {
        $('.visitor-profile').each(function () {
            if (!$(this).attr('data-inited')) {
                var control = new VisitorProfileControl(this);

                $(this).data('uiControlObject', control);
                $(this).attr('data-inited', 1);
            }
        });
    };

    /**
     * Shows the visitor profile popover for a visitor ID. This should not be called directly.
     * Instead broadcast.propagateNewPopoverParameter('visitorProfile', visitorId) should be
     * called. This would make sure the popover would be opened if the URL is copied and pasted
     * in a new tab/window.
     * 
     * @param {String} visitorId The string visitor ID.
     */
    VisitorProfileControl.showPopover = function (visitorId) {
        var url = 'module=Live&action=getVisitorProfilePopup&idVisitor=' + encodeURIComponent(visitorId);
        Piwik_Popover.createPopupAndLoadUrl(url, '', 'visitor-profile-popup');
    };

    VisitorProfileControl.prototype = {

        _setupControl: function () {
            $('.visitor-profile-visits-container', this.$element).jScrollPane({
                showArrows: true,
                verticalArrowPositions: 'os',
                horizontalArrowPositions: 'os'
            });
        },

        _bindEventCallbacks: function () {
            var self = this,
                $element = this.$element;

            $element.on('click', '.visitor-profile-close', function (e) {
                e.preventDefault();
                Piwik_Popover.close();
                return false;
            });

            $element.on('click', '.visitor-profile-pages-visited,.visitor-profile-more-info', function (e) {
                e.preventDefault();
                self._loadMoreVisits();
                return false;
            });

            $element.on('click', '.visitor-profile-see-more-cvars>a', function (e) {
                e.preventDefault();
                $('.visitor-profile-extra-cvars', $element).slideToggle();
                return false;
            });

            $element.on('click', '.visitor-profile-visit-title', function () {
                self._loadIndividualVisitDetails($(this).attr('data-idvisit'));
            });

            $element.on('click', '.visitor-profile-prev-visitor', function (e) {
                e.preventDefault();
                self._loadPreviousVisitor();
                return false;
            });

            $element.on('click', '.visitor-profile-next-visitor', function (e) {
                e.preventDefault();
                self._loadNextVisitor();
                return false;
            });

            $element.on('keydown', function (e) {
                if (event.which == 37) { // on <- key press, load previous visitor
                    self._loadPreviousVisitor();
                } else if (event.which == 39) { // on -> key press, load next visitor
                    self._loadNextVisitor();
                }
            });
        },

        _loadMoreVisits: function () {
            var self = this,
                $element = this.$element;

            var loading = $('.visitor-profile-visits-info > .loadingPiwik', $element);
            loading.css('display', 'table');

            var ajax = new ajaxHelper();
            ajax.addParams({
                module: 'Live',
                action: 'getVisitList',
                period: 'range',
                date: piwik.minDateYear + '-01-01' + ',today',
                idVisitor: $element.attr('data-visitor-id'),
                filter_offset: $('.visitor-profile-visits>li', $element).length
            }, 'GET');
            ajax.setCallback(function (response) {
                loading.css('display', 'none');

                var jsp = $('.visitor-profile-visits-container', $element).data('jsp');
                if (response == '') {
                    jsp.scrollToElement($('.visitor-profile-visits>li:last-child', $element).children().last(), false, true);
                } else {
                    response = $(response);
                    $('.visitor-profile-visits', $element).append(response);
                    jsp.reinitialise();
                    jsp.scrollToElement(response[0], true, true);
                }
            });
            ajax.setFormat('html');
            ajax.send();
        },

        _loadIndividualVisitDetails: function (visitId) {
            var self = this,
                $element = this.$element;

            $('.visitor-profile-avatar .loadingPiwik', $element).css('display', 'inline-block');

            var ajax = new ajaxHelper();
            ajax.addParams({
                module: 'Live',
                action: 'getSingleVisitSummary',
                idVisit: visitId
            }, 'GET');
            ajax.setCallback(function (response) {
                $('.visitor-profile-avatar .loadingPiwik', $element).hide();
                $('.visitor-profile-latest-visit', $element).html(response);
            });
            ajax.setFormat('html');
            ajax.send();
        },

        _loadPreviousVisitor: function () {
            this._gotoAdjacentVisitor(this.$element.attr('data-prev-visitor'));
        },

        _loadNextVisitor: function () {
            this._gotoAdjacentVisitor(this.$element.attr('data-next-visitor')); 
        },

        _gotoAdjacentVisitor: function (idVisitor) {
            if (!idVisitor) {
                return;
            }

            if (this._inPopover()) {
                broadcast.propagateNewPopoverParameter('visitorProfile', idVisitor);
            } else if (this._inWidget()) {
                this.$element.closest('[widgetid]').dashboardWidget('reload', false, true, {idVisitor: idVisitor});
            }
        },

        _getFirstVisitId: function () {
            return $('.visitor-profile-visits>li:first-child>h2', this.$element).attr('data-idvisit');
        },

        _inPopover: function () {
            return !! this.$element.closest('#Piwik_Popover').length;
        },

        _inWidget: function () {
            return !! this.$element.closest('.widget').length;
        },
    };

    exports.VisitorProfileControl = VisitorProfileControl;

    // add the popup handler that creates a visitor profile
    broadcast.addPopoverHandler('visitorProfile', VisitorProfileControl.showPopover);

})(jQuery, require, document);