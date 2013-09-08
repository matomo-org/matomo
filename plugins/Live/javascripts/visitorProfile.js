/**
 * Piwik - Web Analytics
 *
 * Visitor profile popup control.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var piwik = require('piwik'),
        exports = require('piwik/UI');

    /**
     * Sets up and handles events for the visitor profile popup.
     * 
     * @param {Element} element The HTML element returned by the Live.getVisitorLog controller
     *                          action. Should have the CSS class 'visitor-profile'.
     * @constructor
     */
    var VisitorProfileControl = function (element) {
        this.$element = $(element).focus();
        this._setupControl();
        this._bindEventCallbacks();
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
            // highlight the first visit
            $('.visitor-profile-visits>li:first-child', this.$element).addClass('visitor-profile-current-visit');
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
                self._loadIndividualVisitDetails($(this));
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
                if (e.which == 37) { // on <- key press, load previous visitor
                    self._loadPreviousVisitor();
                } else if (e.which == 39) { // on -> key press, load next visitor
                    self._loadNextVisitor();
                }
            });

            var mapShown = false;
            $element.on('click', '.visitor-profile-show-map', function (e) {
                e.preventDefault();

                var $map = $('.visitor-profile-map', $element);
                if ($map.is(':hidden')) {
                    if (!mapShown) {
                        $map.resize();
                        mapShown = true;
                    }

                    $map.slideDown('slow');
                    var newLabel = 'Live_HideMap_js';

                    piwikHelper.lazyScrollTo($('.visitor-profile-location', $element)[0], 400, true);
                } else {
                    $map.slideUp('slow');
                    var newLabel = 'Live_ShowMap_js';
                }
                $(this).text(_pk_translate(newLabel));

                return false;
            });
        },

        _loadMoreVisits: function () {
            var self = this,
                $element = this.$element;

            var loading = $('.visitor-profile-more-info > .loadingPiwik', $element);
            loading.show();

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
                if (response == "") { // no more visits left
                    self._showNoMoreVisitsSpan();
                } else {
                    response = $(response);
                    loading.hide();

                    $('.visitor-profile-visits', $element).append(response);
                    if (response.filter('li').length < 10) {
                        self._showNoMoreVisitsSpan();
                    }

                    piwikHelper.lazyScrollTo($(response)[0], 400, true);
                }
            });
            ajax.setFormat('html');
            ajax.send();
        },

        _showNoMoreVisitsSpan: function () {
            var noMoreSpan = $('<span/>').text(_pk_translate('Live_NoMoreVisits_js')).addClass('visitor-profile-no-visits');
            $('.visitor-profile-more-info', this.$element).html(noMoreSpan);
        },

        _loadIndividualVisitDetails: function ($visitElement) {
            var self = this,
                $element = this.$element,
                visitId = $visitElement.attr('data-idvisit');

            $('.visitor-profile-avatar .loadingPiwik', $element).css('display', 'inline-block');
            piwikHelper.lazyScrollTo($('.visitor-profile-avatar', $element)[0], 400);

            var ajax = new ajaxHelper();
            ajax.addParams({
                module: 'Live',
                action: 'getSingleVisitSummary',
                idVisit: visitId
            }, 'GET');
            ajax.setCallback(function (response) {
                $('.visitor-profile-avatar .loadingPiwik', $element).hide();

                $('.visitor-profile-current-visit', $element).removeClass('visitor-profile-current-visit');
                $visitElement.closest('li').addClass('visitor-profile-current-visit');

                var $latestVisitSection = $('.visitor-profile-latest-visit', $element);
                $latestVisitSection
                    .html(response)
                    .effect('highlight', {color: '#FFFFCB'}, 1200);
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
        }
    };

    exports.VisitorProfileControl = VisitorProfileControl;

    // add the popup handler that creates a visitor profile
    broadcast.addPopoverHandler('visitorProfile', VisitorProfileControl.showPopover);

})(jQuery, require);