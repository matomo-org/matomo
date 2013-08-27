/**
 * Piwik - Web Analytics
 *
 * Visitor profile popup control.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require, Piwik_Popover, ajaxHelper) {

    var piwik = require('piwik'),
        exports = require('piwik/UI');

    /**
     * Sets up and handles events for the visitor profile popup.
     * 
     * @param {Element} element The HTML element returned by the Live.getVisitorLog controller
     *                          action. Should have the CSS class 'visitor-profile'.
     */
    var VisitorProfileControl = function (element) {
        this.element = $(element);

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

    VisitorProfileControl.prototype = {

        _setupControl: function () {
            $('.visitor-profile-visits-container', this.element).jScrollPane({
                showArrows: true,
                verticalArrowPositions: 'os',
                horizontalArrowPositions: 'os'
            });
        },

        _bindEventCallbacks: function () {
            var self = this;

            this.element.on('click', '.visitor-profile-close', function (e) {
                e.preventDefault();
                Piwik_Popover.close();
                return false;
            });

            this.element.on('click', '.visitor-profile-pages-visited,.visitor-profile-more-info', function (e) {
                e.preventDefault();
                self._loadMoreVisits();
                return false;
            });

            this.element.on('click', '.visitor-profile-see-more-cvars>a', function (e) {
                e.preventDefault();
                $('.visitor-profile-extra-cvars', self.element).slideToggle();
                return false;
            });

            this.element.on('click', '.visitor-profile-visit-title', function () {
                self._loadIndividualVisitDetails($(this).attr('data-idvisit'));
            });
        },

        _loadMoreVisits: function () {
            var self = this;

            var loading = $('.visitor-profile-visits-info > .loadingPiwik', this.element);
            loading.css('display', 'table');

            var ajax = new ajaxHelper();
            ajax.addParams({
                module: 'Live',
                action: 'getVisitList',
                period: 'range',
                date: piwik.minDateYear + '-01-01' + ',today',
                idVisitor: this.element.attr('data-visitor-id'),
                filter_offset: $('.visitor-profile-visits>li', this.element).length
            }, 'GET');
            ajax.setCallback(function (response) {
                loading.css('display', 'none');

                var jsp = $('.visitor-profile-visits-container', self.element).data('jsp');
                if (response == '') {
                    jsp.scrollToElement($('.visitor-profile-visits>li:last-child', self.element).children().last(), false, true);
                } else {
                    response = $(response);
                    $('.visitor-profile-visits', self.element).append(response);
                    jsp.reinitialise();
                    jsp.scrollToElement(response[0], true, true);
                }
            });
            ajax.setFormat('html');
            ajax.send();
        },

        _loadIndividualVisitDetails: function (visitId) {
            var self = this;

            $('.visitor-profile-avatar .loadingPiwik', this.element).css('display', 'inline-block');

            var ajax = new ajaxHelper();
            ajax.addParams({
                module: 'Live',
                action: 'getSingleVisitSummary',
                idVisit: visitId
            }, 'GET');
            ajax.setCallback(function (response) {
                $('.visitor-profile-avatar .loadingPiwik', self.element).hide();
                $('.visitor-profile-latest-visit', self.element).html(response);
            });
            ajax.setFormat('html');
            ajax.send();
        },
    };

    exports.VisitorProfileControl = VisitorProfileControl;

})(jQuery, require, Piwik_Popover, ajaxHelper);