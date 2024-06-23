/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {
    var exports = require('piwik/UI');

    /**
     * @deprecated
     */
    var Notification = function () {
        this.notificationId = null;
    };

    /**
     * @deprecated use NotificationsStore.show() in CoreHome Vue module
     */
    Notification.prototype.show = function (message, options) {
        options = checkOptions(options);
        options.noclear = !! options.noclear;
        this.notificationId = window.CoreHome.NotificationsStore.show($.extend({ message: message }, options));
    };

    /**
     * @deprecated use NotificationsStore.remove() in CoreHome Vue module
     */
    Notification.prototype.remove = function (notificationId) {
        window.CoreHome.NotificationsStore.remove(notificationId);
    };

  /**
   * @deprecated use NotificationsStore.scrollToNotification() in CoreHome Vue module
   */
  Notification.prototype.scrollToNotification = function () {
        if (this.notificationId) {
            window.CoreHome.NotificationsStore.scrollToNotification(this.notificationId);
        }
    };

    /**
     * @deprecated use NotificationsStore.toast() in CoreHome Vue module
     */
    Notification.prototype.toast = function (message, options) {
        options = checkOptions(options);
        options.noclear = !! options.noclear;
        window.CoreHome.NotificationsStore.toast($.extend({ message: message }, options));
    };

    exports.Notification = Notification;

    function checkOptions(options) {
        if (options && !$.isPlainObject(options)) {
            throw new Error('Options has the wrong format, cannot display notification');
        } else if (!options) {
            options = {};
        }
        return options;
    }

})(jQuery, require);
