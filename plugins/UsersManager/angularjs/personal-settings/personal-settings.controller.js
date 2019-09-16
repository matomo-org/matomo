/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PersonalSettingsController', PersonalSettingsController);

    PersonalSettingsController.$inject = ['piwikApi', '$filter', '$window', 'piwik'];

    function PersonalSettingsController(piwikApi, $filter, $window, piwik) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var translate = $filter('translate');

        var self = this;

        this.newsletterSignupButtonTitle = translate('General_Save');
        this.doesRequirePasswordConfirmation = false;
        this.showNewsletterSignup = true;

        function updateSettings(postParams)
        {
            self.loading = true;

            piwikApi.withTokenInUrl();
            piwikApi.post({
                module: 'UsersManager', action: 'recordUserSettings', format: 'json'
            }, postParams).then(function (success) {
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                    id: 'PersonalSettingsSuccess', context: 'success'});
                notification.scrollToNotification();

                self.doesRequirePasswordConfirmation = !!self.password;
                self.passwordCurrent = '';
                self.loading = false;
            }, function (errorMessage) {
                self.loading = false;
                self.passwordCurrent = '';
            });
        }

        this.requirePasswordConfirmation = function () {
            this.doesRequirePasswordConfirmation = true;
        };

        this.signupForNewsletter = function () {
            var signupBtn = $('#newsletterSignupBtn');
            signupBtn.html(translate('General_Loading'));
            this.isProcessingNewsletterSignup = true;

            piwikApi.withTokenInUrl();
            piwikApi.fetch({module: 'API', method: 'UsersManager.newsletterSignup'}).then(function () {
                self.isProcessingNewsletterSignup = false;
                self.showNewsletterSignup = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(translate('UsersManager_NewsletterSignupSuccessMessage'), { id: 'newslettersignup', context: 'success'});
                notification.scrollToNotification();

            }, function () {
                self.isProcessingNewsletterSignup = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(translate('UsersManager_NewsletterSignupFailureMessage'), { id: 'newslettersignup', context: 'error' });
                notification.scrollToNotification();

                self.newsletterSignupButtonTitle = translate('General_PleaseTryAgain');
            });
        };

        this.regenerateTokenAuth = function () {
            var parameters = { userLogin: piwik.userLogin };

            self.loading = true;

            piwikHelper.modalConfirm('#confirmTokenRegenerate', {yes: function () {
                piwikApi.withTokenInUrl();
                piwikApi.post({
                    module: 'API',
                    method: 'UsersManager.regenerateTokenAuth'
                }, parameters).then(function (success) {
                    $window.location.reload();
                    self.loading = false;
                }, function (errorMessage) {
                    self.loading = false;
                });
            }});
        };

        this.cancelSave = function () {
            this.passwordCurrent = '';
        };

        this.save = function () {

            if (this.doesRequirePasswordConfirmation && !this.passwordCurrent) {
                angular.element('#confirmChangesWithPassword').openModal({ dismissible: false, ready: function () {
                    $('.modal.open #currentPassword').focus();
                }});
                return;
            }

            angular.element('#confirmChangesWithPassword').closeModal();

            var postParams = {
                email: this.email,
                defaultReport: this.defaultReport == 'MultiSites' ? this.defaultReport : this.site.id,
                defaultDate: this.defaultDate,
                language: this.language,
                timeformat: this.timeformat,
            };

            if (this.password) {
                postParams.password = this.password;
            }

            if (this.passwordBis) {
                postParams.passwordBis = this.passwordBis;
            }

            if (this.passwordCurrent) {
                postParams.passwordConfirmation = this.passwordCurrent;
            }

            updateSettings(postParams);
        };
    }
})();
