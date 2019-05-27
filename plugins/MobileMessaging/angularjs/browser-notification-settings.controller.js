/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('BrowserNotificationSettingsController', BrowserNotificationSettingsController);

    BrowserNotificationSettingsController.$inject = [];

    function BrowserNotificationSettingsController() {

        this.timerId = null;

        this.enable = function() {
            Push.Permission.request(
                function() {
                    // Success - update UI
                    $('#enableBrowserNotifications').addClass('ng-hide');
                    $('#disableBrowserNotifications').removeClass('ng-hide');
                    //this.startListening();
                }.bind(this),
                function() {
                    // Failure - bugger
                    alert("Never mind then");
                }
            );
        };

        this.disable = function() {
            if (this.timerId) {
                clearInterval(this.timerId);
            }
            console.log("I don't know how to disable yet");
            // $('#enableBrowserNotifications').show();
            // $('#disableBrowserNotifications').hide();
            
        };

        this.isEnabled = function() {
            return Push.Permission.has();
        };

        this.startListening = function() {
        };
    }

    Push.config({
        serviceWorker: './notifications-sw.js'
    });
})();