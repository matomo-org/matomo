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

        placeNotification($.extend({}, { message }, options));
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

        placeNotification($.extend({}, { message }, options, {
          // place the notification in body
          placeat: 'body',
          position: {
            left: $placeat.offset().left,
            top: $placeat.offset().top
          }
        }));
    };

    exports.Notification = Notification;

    function initializeNotificationContainer(selector, group) {
      var $container = $(selector);
      if ($container.data('notification-container-inited') === '1') {
        return;
      }

      var mountPoint = $('<div/>');
      mountPoint.appendTo($container);

      // initialize vue notification group component
      var Vue = window.Vue;
      var app = Vue.createApp({
        template: '<NotificationGroup :group="group"/>',
        data: function() {
          return { group };
        },
      });
      app.config.globalProperties.$sanitize = window.vueSanitize;
      app.config.globalProperties.translate = window.CoreHome.translate;
      app.component('NotificationGroup', window.CoreHome.NotificationGroup);
      app.mount(mountPoint[0]);

      $container.data('notification-container-inited', '1');
    }

    function addNotification(addType, options) {
      var method = addType === 'append' ? 'appendNotification' : 'prependNotification';
      window.CoreHome.NotificationsStore[method](options);
    }

    function placeNotification(options) {
        var group = options.group || options.placeat;
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

        options.noclear = !!options.noclear;

        initializeNotificationContainer(notificationPosition, group);

        addNotification(method, options);
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
