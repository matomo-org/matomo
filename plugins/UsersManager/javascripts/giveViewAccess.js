/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    function hideLoading()
    {
        $('#giveUserAccessToViewReports').prop('disabled', false);
        $('#ajaxLoadingGiveViewAccess').hide();
    }

    function showLoading()
    {
        $('#giveUserAccessToViewReports').prop('disabled', true);
        $('#ajaxLoadingGiveViewAccess').show();
    }

    function showErrorNotification(errorMessage)
    {
        var placeAt = '#ajaxErrorGiveViewAccess';
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

    function createNewAjaxHelper()
    {
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.setCompleteCallback(function (xhr, status) {
            if (xhr &&
                xhr.responseJSON &&
                xhr.responseJSON.message &&
                xhr.responseJSON.result &&
                xhr.responseJSON.result == 'error') {
                hideLoading();
            }
            if (status && String(status).toLowerCase() !== 'sucess') {
                hideLoading();
            }
        });
        ajaxHandler.addParams({
            module: 'API',
            format: 'json'
        }, 'GET');
        ajaxHandler.setErrorElement('#ajaxErrorGiveViewAccess');

        return ajaxHandler;
    }

    function sendViewAccess(userLogin)
    {
        sendUpdateUserAccess(userLogin, 'view', function () { window.location.reload(); });
        setTimeout(hideLoading, 250);
        // we hide loading after a bit since we cannot influence the ajax request in case of any error
    }

    function setViewAccessForUserToAllWebsitesIfUserConfirms(userLogin)
    {
        // ask confirmation
        $('#confirm').find('#login').text(userLogin);

        function onValidate() {
            sendViewAccess(userLogin);
        }

        piwikHelper.modalConfirm('#confirm', {yes: onValidate, no: hideLoading})
    }

    function setViewAccessForUserIfNotAlreadyHasAccess(userLogin, idSites)
    {
        var ajaxHandler = createNewAjaxHelper();
        ajaxHandler.addParams({
            method: 'UsersManager.getUsersAccessFromSite',
            userLogin: userLogin,
            idSite: idSites
        }, 'GET');
        ajaxHandler.setCallback(function (users) {
            var userLogins = [];
            if (users && users[0]) {
                userLogins = $.map(users[0], function (val, key) {
                    return (''+ key).toLowerCase();
                });
            }

            if (-1 !== userLogins.indexOf(userLogin.toLowerCase())) {
                showErrorNotification(_pk_translate('UsersManager_ExceptionUserHasViewAccessAlready'));
            } else {
                sendViewAccess(userLogin);
            }

        });
        ajaxHandler.send();
    }

    function ifUserExists(usernameOrEmail, callback)
    {
        var ajaxHandler = createNewAjaxHelper();
        ajaxHandler.addParams({
            method: 'UsersManager.userExists',
            userLogin: usernameOrEmail,
        }, 'GET');
        ajaxHandler.setCallback(callback);
        ajaxHandler.send();
    }

    function getUsernameFromEmail(usernameOrEmail, callback)
    {
        var ajaxHandler = createNewAjaxHelper();
        ajaxHandler.addParams({
            method: 'UsersManager.getUserLoginFromUserEmail',
            userEmail: usernameOrEmail,
        }, 'GET');
        ajaxHandler.setCallback(callback);
        ajaxHandler.send();
    }

    function giveViewAccessToUser(userLogin)
    {
        var idSites = getIdSites();

        if (idSites === 'all') {
            setViewAccessForUserToAllWebsitesIfUserConfirms(userLogin);
        } else {
            setViewAccessForUserIfNotAlreadyHasAccess(userLogin, idSites);
        }
    }

    $('#showGiveViewAccessForm').click(function () {
        $('#giveViewAccessForm').toggle()
    });

    $('#giveViewAccessForm #user_invite').keypress(function (e) {
        var key = e.keyCode || e.which;
        if (key == 13) {
            $('#giveViewAccessForm #giveUserAccessToViewReports').click();
        }
    });

    $('#giveViewAccessForm #giveUserAccessToViewReports').click(function () {
        showLoading();

        var usernameOrEmail = $('#user_invite').val();

        if (!usernameOrEmail) {
            showErrorNotification(_pk_translate('UsersManager_ExceptionNoValueForUsernameOrEmail'));
            return;
        }

        ifUserExists(usernameOrEmail, function (isUserName) {
            if (isUserName && isUserName.value) {
                giveViewAccessToUser(usernameOrEmail);
            } else {
                getUsernameFromEmail(usernameOrEmail, function (login) {
                    if (login && login.value) {
                        giveViewAccessToUser(login.value);
                    } else {
                        hideLoading();
                    }
                });
            }
        });
    });
});
