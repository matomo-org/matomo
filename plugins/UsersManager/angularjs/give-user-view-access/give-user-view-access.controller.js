/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('GiveUserViewAccessController', GiveUserViewAccessController);

    GiveUserViewAccessController.$inject = ['piwikApi', '$window'];

    function GiveUserViewAccessController(piwikApi, $window) {

        var self = this;
        this.isLoading = false;
        this.showForm = false;
        this.usernameOrEmail = '';

        var requestOptions = {placeat: '#ajaxErrorGiveViewAccess'};

        function hideLoading() {
            self.isLoading = false;
        }

        function showLoading() {
            self.isLoading = true;
        }

        function showErrorNotification(errorMessage)
        {
            var placeAt = requestOptions.placeat;
            $(placeAt).show();

            var UI = require('piwik/UI');
            var notification = new UI.Notification();
            notification.show(errorMessage, {
                placeat: placeAt,
                context: 'error',
                id: 'ajaxHelper',
                type: null
            });
            notification.scrollToNotification();
            hideLoading();
        }

        function sendViewAccess(userLogin)
        {
            var parameters = {userLogin: userLogin, access: 'view', idSites: getIdSites()};

            piwikApi.post({
                module: 'API',
                format: 'json',
                method: 'UsersManager.setUserAccess'
            }, parameters, requestOptions).then(function () {
                $window.location.reload();
                hideLoading();
            }, function () {
                hideLoading();
            });
        }
        function getIdSites() {
            return $('#usersManagerSiteSelect').attr('siteid');
        }

        function setViewAccessForUserToAllWebsitesIfUserConfirms(userLogin)
        {
            // ask confirmation
            $('#confirm').find('.login').text(userLogin);

            function onValidate() {
                sendViewAccess(userLogin);
            }

            piwikHelper.modalConfirm('#confirm', {yes: onValidate, no: hideLoading})
        }

        function setViewAccessForUserIfNotAlreadyHasAccess(userLogin, idSites)
        {
            piwikApi.fetch({
                method: 'UsersManager.getUsersAccessFromSite',
                userLogin: userLogin,
                idSite: idSites,
                filter_limit: '-1'
            }, requestOptions).then(function (users) {
                var userLogins = [];
                if (users) {
                    angular.forEach(users, function (val, key) {
                        userLogins.push((''+ key).toLowerCase());
                    });
                }

                if (-1 !== userLogins.indexOf(userLogin.toLowerCase())) {
                    showErrorNotification(_pk_translate('UsersManager_ExceptionUserHasViewAccessAlready'));
                } else {
                    sendViewAccess(userLogin);
                }

            }, function () {
                hideLoading();
            });
        }

        function ifUserExists(usernameOrEmail)
        {
            return piwikApi.fetch({
                method: 'UsersManager.userExists',
                userLogin: usernameOrEmail
            }, requestOptions).then(function (response) {

                return response;

            }, function () {
                hideLoading();
            });
        }

        function getUsernameFromEmail(usernameOrEmail, callback)
        {
            return piwikApi.fetch({
                method: 'UsersManager.getUserLoginFromUserEmail',
                userEmail: usernameOrEmail
            }, requestOptions).then(function (response) {
                return response;
            }, function () {
                hideLoading();
            });
        }

        function giveViewAccessToUser(userLogin)
        {
            var idSites = getIdSites();

            if (idSites === 'all') {
                setViewAccessForUserToAllWebsitesIfUserConfirms(userLogin);
            } else {
                function onValidate() {
                    setViewAccessForUserIfNotAlreadyHasAccess(userLogin, idSites);
                }

                if (userLogin == 'anonymous') {
                    piwikHelper.modalConfirm('#confirmAnonymousAccess', {yes: onValidate, no: hideLoading})
                } else {
                    onValidate();
                }
            }
        }

        this.giveAccess = function () {

            if (!this.usernameOrEmail) {
                showErrorNotification(_pk_translate('UsersManager_ExceptionNoValueForUsernameOrEmail'));
                return;
            }

            showLoading();

            ifUserExists(this.usernameOrEmail).then(function (isUserName) {
                if (isUserName && isUserName.value) {
                    giveViewAccessToUser(self.usernameOrEmail);
                } else {
                    getUsernameFromEmail(self.usernameOrEmail).then(function (login) {
                        if (login && login.value) {
                            giveViewAccessToUser(login.value);
                        } else {
                            hideLoading();
                        }
                    });
                }
            });
        };

        this.showViewAccessForm = function () {
            this.showForm = true;
        }
    }
})();