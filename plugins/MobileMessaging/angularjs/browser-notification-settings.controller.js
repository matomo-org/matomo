/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('BrowserNotificationSettingsController', BrowserNotificationSettingsController);

    BrowserNotificationSettingsController.$inject = ['$scope'];

    function BrowserNotificationSettingsController($scope) {

        this.serviceWorker = null;
        this.subscription = null;

        this.serverKey = "BLnqbKTQD4nShW5LSA-x9yAY04V98VOYleR4RQK-QTG4m532MWuJYxSB2h6oJm8uICgiPZLoCdAdaK4_ZVlnYlg";

        this.isLoading = false;

        this.isSupported = function() {
            return 'serviceWorker' in navigator && 'PushManager' in window;
        };

        this.toggleEnabled = function () {
            this.isLoading = true;

            if (this.subscription) {
                this.disable();
            } else {
                this.enable();
            }
        };

        this.enable = function() {
            var applicationServerKey = this.urlB64ToUint8Array(this.serverKey);
            this.serviceWorker.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            }).then(function(subscription) {
                console.log('User is subscribed.');

//                updateSubscriptionOnServer(subscription);

                this.subscription = subscription;

                $('#enableBrowserNotifications').html("Disable");
            }).catch(function(err) {
                console.log('Failed to subscribe the user: ', err);
            });
        };

        this.disable = function() {
            console.log("I don't know how to disable yet");
            $('#enableBrowserNotifications').html("Disable");
        };

        this.urlB64ToUint8Array = function(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;            
        };

        this.registerServiceWorker = function() {
            var swUrl = 'sw.js';
            navigator.serviceWorker.register(swUrl)
                .then(function(serviceWorker) {
                    console.log('Service Worker is registered', serviceWorker);
                    this.serviceWorker = serviceWorker;
                    this.checkForSubscription(serviceWorker);
                }.bind(this))
                .catch(function(error) {
                    console.error('Service Worker Error', error);
                });
        };

        this.checkForSubscription = function(serviceWorker) {
            serviceWorker.pushManager.getSubscription().then(function(subscription) {
                if (!subscription) {
                    console.log("There is no subscription");
                    return;
                }

                console.log("There is a subscription");
                this.subscription = subscription;
                // TODO: Figure out how to get jQuery here
                $('#enableBrowserNotifications').html('Disable');
            }.bind(this));
        };

        $scope.init = function() {
            this.registerServiceWorker();
        }.bind(this);
        $scope.init();
    }
})();