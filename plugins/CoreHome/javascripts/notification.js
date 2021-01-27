/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI');

    /**
     * Creates a new notifications.
     *
     * Example:
     * var UI = require('piwik/UI');
     * var notification = new UI.Notification();
     * notification.show('My Notification Message', {title: 'Low space', context: 'warning'});
     */
    var Notification = function () {
        this.$node = null;
    };

    /**
     * Makes the notification visible.
     *
     * @param    {string}  message    The actual message that will be displayed. Must be set.
     * @param    {Object}  [options]
     * @param    {string}  [options.id]         Only needed for persistent notifications. The id will be sent to the
     *                                          frontend once the user closes the notifications. The notification has to
     *                                          be registered/notified under this name
     * @param    {string}  [options.title]      The title of the notification. For instance the plugin name.
     * @param    {bool}    [options.animate=true]     If enabled, the notification will be faded in.
     * @param    {string}  [options.context=warning]  Context of the notification: 'info', 'warning', 'success' or
     *                                                'error'
     * @param    {string}  [options.type=transient]   The type of the notification: Either 'toast' or 'transitent'
     * @param    {bool}    [options.noclear=false]    If set, the close icon is not displayed.
     * @param    {object}  [options.style]            Optional style/css dictionary. For instance {'display': 'inline-block'}
     * @param    {string}  [options.placeat]          By default, the notification will be displayed in the "stats bar".
     *                                                You can specify any other CSS selector to place the notifications
     *                                                wherever you want.
     */
    Notification.prototype.show = function (message, options) {
        checkMessage(message);
        options = checkOptions(options);

        var template = generateNotificationHtmlMarkup(options, message);
        this.$node   = placeNotification(template, options);
    };

    /**
     * Removes a previously shown notification having the given notification id.
     *
     *
     * @param {string}  notificationId   The id of a notification that was previously registered.
     */
    Notification.prototype.remove = function (notificationId) {
        $('[piwik-notification][notification-id=' + notificationId + ']').remove();
    };

    Notification.prototype.scrollToNotification = function () {
        if (this.$node) {
            piwikHelper.lazyScrollTo(this.$node, 250);
        }
    };

    /**
     * Shows a notification at a certain point with a quick upwards animation.
     *
     * TODO: if the materializecss version matomo uses is updated, should use their toasts.
     *
     * @type {Notification}
     * @param    {string}  message    The actual message that will be displayed. Must be set.
     * @param    {Object}  options
     * @param    {string}  options.placeat          Where to place the notification. Required.
     * @param    {string}  [options.id]         Only needed for persistent notifications. The id will be sent to the
     *                                          frontend once the user closes the notifications. The notification has to
     *                                          be registered/notified under this name
     * @param    {string}  [options.title]      The title of the notification. For instance the plugin name.
     * @param    {string}  [options.context=warning]  Context of the notification: 'info', 'warning', 'success' or
     *                                                'error'
     * @param    {string}  [options.type=transient]   The type of the notification: Either 'toast' or 'transitent'
     * @param    {bool}    [options.noclear=false]    If set, the close icon is not displayed.
     * @param    {object}  [options.style]            Optional style/css dictionary. For instance {'display': 'inline-block'}
     */
    Notification.prototype.toast = function (message, options) {
        checkMessage(message);
        options = checkOptions(options);

        var $placeat = $(options.placeat);
        if (!$placeat.length) {
            throw new Error("A valid selector is required for the placeat option when using Notification.toast().");
        }

        var $template = $(generateNotificationHtmlMarkup(options, message)).hide();
        $('body').append($template);

        compileNotification($template);

        $template.css({
            position: 'absolute',
            left: $placeat.offset().left,
            top: $placeat.offset().top
        });
        setTimeout(function () {
            $template.animate(
                {
                    top: $placeat.offset().top - $template.height()
                },
                {
                    duration: 300,
                    start: function () {
                        $template.show();
                    }
                }
            );
        });
    };

    exports.Notification = Notification;

    function generateNotificationHtmlMarkup(options, message) {
        var attributeMapping = {
                id: 'notification-id',
                title: 'notification-title',
                context: 'context',
                type: 'type',
                noclear: 'noclear',
                class: 'class',
                toastLength: 'toast-length'
            },
            html = '<div piwik-notification';

        for (var key in attributeMapping) {
            if (attributeMapping.hasOwnProperty(key)
                && options[key]
            ) {
                html += ' ' + attributeMapping[key] + '="' + options[key].toString().replace(/"/g, "&quot;") + '"';
            }
        }

        html += '><div ng-non-bindable>' + message + '</div></div>';

        return html;
    }

    function compileNotification($node) {
        angular.element(document).injector().invoke(function ($compile, $rootScope) {
            $compile($node)($rootScope.$new(true));
        });
    }

    function placeNotification(template, options) {
        var $notificationNode = $(template);

        // compile the template in angular
        compileNotification($notificationNode);

        if (options.style) {
            $notificationNode.css(options.style);
        }

        var notificationPosition = '#notificationContainer';
        var method = 'append';
        if (options.placeat) {
            notificationPosition = options.placeat;
        } else {
            // If a modal is open, we want to make sure the error message is visible and therefore show it within the opened modal
            var modalSelector = '.modal.open .modal-content';
            var modalOpen = $(modalSelector);
            if (modalOpen.length) {
                notificationPosition = modalSelector;
                method = 'prepend';
            }
        }

        $notificationNode = $notificationNode.hide();
        $(notificationPosition)[method]($notificationNode);

        if (false === options.animate) {
            $notificationNode.show();
        } else {
            $notificationNode.fadeIn(1000);
        }

        return $notificationNode;
    }

    function checkMessage(message) {
        if (!message) {
            throw new Error('No message given, cannot display notification');
        }
    }

    function checkOptions(options) {
        if (options && !$.isPlainObject(options)) {
            throw new Error('Options has the wrong format, cannot display notification');
        } else if (!options) {
            options = {};
        }
        return options;
    }

})(jQuery, require);