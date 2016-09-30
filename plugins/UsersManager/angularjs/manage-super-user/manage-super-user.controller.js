/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageSuperUserController', ManageSuperUserController);

    ManageSuperUserController.$inject = ['piwikApi', '$timeout'];

    function ManageSuperUserController(piwikApi, $timeout) {

        var self = this;
        this.isLoading = false;

        function updateSuperUserAccess(login, hasSuperUserAccess)
        {
            self.isLoading = true;

            $timeout(function () {
                piwik.helper.lazyScrollTo('.loadingManageSuperUser', 40);
            });

            piwikApi.post({
                module: 'API',
                method: 'UsersManager.setSuperUserAccess'
            }, {userLogin: login, hasSuperUserAccess: hasSuperUserAccess}).then(function () {

                self.isLoading = false;
                
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('General_Done'), {
                    placeat: '#superUserAccessUpdated',
                    context: 'success',
                    noclear: true,
                    type: 'toast',
                    style: {display: 'inline-block', marginTop: '10px', marginBottom: '30px'},
                    id: 'usersManagerSuperUserAccessUpdated'
                });
                notification.scrollToNotification();
                piwikHelper.redirect();

            }, function () {
                self.isLoading = false;
            });
        }
        
        this.removeSuperUserAccess = function (login) {
            var message = 'UsersManager_ConfirmProhibitOtherUsersSuperUserAccess';
            if (login == piwik.userLogin) {
                message = 'UsersManager_ConfirmProhibitMySuperUserAccess';
            }

            message = _pk_translate(message, [login]);

            $('#superUserAccessConfirm h2').text(message);

            piwikHelper.modalConfirm('#superUserAccessConfirm', {yes: function () {
                updateSuperUserAccess(login, 0);
            }});
        };
        
        this.giveSuperUserAccess = function (login) {

            var message = _pk_translate('UsersManager_ConfirmGrantSuperUserAccess', [login]);

            $('#superUserAccessConfirm h2').text(message);

            piwikHelper.modalConfirm('#superUserAccessConfirm', {yes: function () {
                updateSuperUserAccess(login, 1);
            }});
        };
    }
})();