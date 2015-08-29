/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function sendUpdateUserAJAX(row) {
    var parameters = {};
    parameters.userLogin = $(row).children('#userLogin').html();
    var password = $(row).find('input#password').val();
    if (password != '-') parameters.password = password;
    parameters.email = $(row).find('input#email').val();
    parameters.alias = $(row).find('input#alias').val();

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'API',
        format: 'json',
        method: 'UsersManager.updateUser'
    }, 'GET');
    ajaxHandler.addParams(parameters, 'POST');
    ajaxHandler.redirectOnSuccess();
    ajaxHandler.setLoadingElement();
    ajaxHandler.send(true);
}

function sendDeleteUserAJAX(login) {
    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'API',
        format: 'json',
        method: 'UsersManager.deleteUser'
    }, 'GET');
    ajaxHandler.addParams({userLogin: login}, 'POST');
    ajaxHandler.redirectOnSuccess();
    ajaxHandler.setLoadingElement('#ajaxLoadingUsersManagement');
    ajaxHandler.setErrorElement('#ajaxErrorUsersManagement');
    ajaxHandler.send(true);
}

function sendAddUserAJAX(row) {
    var parameters = {};
    parameters.userLogin = $(row).find('input#useradd_login').val();
    parameters.password = $(row).find('input#useradd_password').val();
    parameters.email = $(row).find('input#useradd_email').val();
    parameters.alias = $(row).find('input#useradd_alias').val();

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'API',
        format: 'json',
        method: 'UsersManager.addUser'
    }, 'GET');
    ajaxHandler.addParams(parameters, 'POST');
    ajaxHandler.redirectOnSuccess();
    ajaxHandler.setLoadingElement('#ajaxLoadingUsersManagement');
    ajaxHandler.setErrorElement('#ajaxErrorUsersManagement');
    ajaxHandler.send(true);
}

function getIdSites() {
    return $('#usersManagerSiteSelect').attr('siteid');
}

function sendUpdateUserAccess(login, access, successCallback) {
    var parameters = {};
    parameters.userLogin = login;
    parameters.access = access;
    parameters.idSites = getIdSites();

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'API',
        format: 'json',
        method: 'UsersManager.setUserAccess'
    }, 'GET');
    ajaxHandler.addParams(parameters, 'POST');
    ajaxHandler.setCallback(successCallback);
    ajaxHandler.setLoadingElement('#ajaxLoadingUsersManagement');
    ajaxHandler.setErrorElement('#ajaxErrorUsersManagement');
    ajaxHandler.send(true);
}

function submitOnEnter(e) {
    var key = e.keyCode || e.which;
    if (key == 13) {
        $(this).find('.adduser').click();
        $(this).find('.updateuser').click();
    }
}

function launchAjaxRequest(self, successCallback) {
    sendUpdateUserAccess(
        $(self).parent().parent().find('#login').html(), //if changed change also the modal
        $(self).parent().attr('id'),
        successCallback
    );
}

function updateSuperUserAccess(login, hasSuperUserAccess)
{
    var parameters = {};
    parameters.userLogin = login;
    parameters.hasSuperUserAccess = hasSuperUserAccess ? 1: 0;

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'API',
        format: 'json',
        method: 'UsersManager.setSuperUserAccess'
    }, 'GET');
    ajaxHandler.addParams(parameters, 'POST');
    ajaxHandler.setCallback(function () {

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
    });
    ajaxHandler.setLoadingElement('#ajaxErrorSuperUsersManagement');
    ajaxHandler.setErrorElement('#ajaxErrorSuperUsersManagement');
    ajaxHandler.send(true);
}

function bindUpdateSuperUserAccess() {
    var login     = $(this).parents('td').data('login');
    var hasAccess = parseInt($(this).data('hasaccess'), 10);

    var message = 'UsersManager_ConfirmGrantSuperUserAccess';
    if (hasAccess && login == piwik.userLogin) {
        message = 'UsersManager_ConfirmProhibitMySuperUserAccess';
    } else if (hasAccess) {
        message = 'UsersManager_ConfirmProhibitOtherUsersSuperUserAccess';
    }

    message = _pk_translate(message);
    message = message.replace('%s', login);

    $('#superUserAccessConfirm h2').text(message);

    piwikHelper.modalConfirm('#superUserAccessConfirm', {yes: function () {
        updateSuperUserAccess(login, !hasAccess);
    }});
}

function bindUpdateAccess() {
    var self = this;
    // callback called when the ajax request Update the user permissions is successful
    function successCallback(response) {
        var mainDiv = $(self).parent().parent();
        var login = $('#login', mainDiv).text();
        mainDiv.find('.accessGranted')
            .attr("src", "plugins/UsersManager/images/no-access.png")
            .attr("class", "updateAccess")
            .click(bindUpdateAccess)
        ;
        $(self)
            .attr('src', "plugins/UsersManager/images/ok.png")
            .attr('class', "accessGranted")
        ;

        var UI = require('piwik/UI');
        var notification = new UI.Notification();
        notification.show(_pk_translate('General_Done'), {
            placeat: '#accessUpdated',
            context: 'success',
            noclear: true,
            type: 'toast',
            style: {display: 'inline-block', marginTop: '10px'},
            id: 'usersManagerAccessUpdated'
        });

        // reload if user anonymous was updated, since we display a Notice message when anon has view access
        if (login == 'anonymous') {
            window.location.reload();
        }
    }

    var idSite = getIdSites();
    if (idSite == 'all') {
        var target = this;

        //ask confirmation
        var userLogin = $(this).parent().parent().find('#login').text();
        $('#confirm').find('#login').text(userLogin); // if changed here change also the launchAjaxRequest

        function onValidate() {
            launchAjaxRequest(target, successCallback);
        }

        piwikHelper.modalConfirm('#confirm', {yes: onValidate})
    }
    else {
        launchAjaxRequest(this, successCallback);
    }
}

$(document).ready(function () {
    var alreadyEdited = [];
    // when click on edituser, the cells become editable
    $('.edituser')
        .click(function () {
            piwikHelper.hideAjaxError();
            var idRow = $(this).attr('id');
            if (alreadyEdited[idRow] == 1) return;
            alreadyEdited[idRow] = 1;
            $('tr#' + idRow + ' .editable').each(
                // make the fields editable
                // change the EDIT button to VALID button
                function (i, n) {
                    var contentBefore = $(n).text();
                    var idName = $(n).attr('id');
                    if (idName != 'userLogin') {
                        var contentAfter = '<input id="' + idName + '" value="' + piwikHelper.htmlEntities(contentBefore) + '" size="25" />';
                        $(n).html(contentAfter);
                    }
                }
            );

            $(this)
                .toggle()
                .parent()
                .prepend($('<a class="canceluser">' + _pk_translate('General_OrCancel', ['', '']) + '</a>')
                    .click(function () {
                        piwikHelper.redirect();
                    })
                ).prepend($('<input type="submit" class="submit updateuser"  value="' + _pk_translate('General_Save') + '" />')
                    .click(function () {
                        var onValidate = function () {
                            sendUpdateUserAJAX($('tr#' + idRow));
                        };
                        if ($('tr#' + idRow).find('input#password').val() != '-') {
                            piwikHelper.modalConfirm('#confirmPasswordChange', {yes: onValidate});
                        } else {
                            onValidate();
                        }
                    })
            );
        });

    $('.editable').keypress(submitOnEnter);

    $('td.editable')
        .click(function () { $(this).parent().find('.edituser').click(); });

    // when click on deleteuser, the we ask for confirmation and then delete the user
    $('.deleteuser')
        .click(function () {
            piwikHelper.hideAjaxError();
            var idRow = $(this).attr('id');
            var loginToDelete = $(this).parent().parent().find('#userLogin').html();
            $('#confirmUserRemove').find('h2').text(sprintf(_pk_translate('UsersManager_DeleteConfirm'), '"' + loginToDelete + '"'));
            piwikHelper.modalConfirm('#confirmUserRemove', {yes: function () { sendDeleteUserAJAX(loginToDelete); }});
        }
    );

    $('.admin .user .add-user').click(function () {
        piwikHelper.hideAjaxError();
        $(this).toggle();

        var numberOfRows = $('table#users')[0].rows.length;
        var newRowId = numberOfRows + 1;
        newRowId = 'row' + newRowId;

        $($.parseHTML(' <tr id="' + newRowId + '">\
				<td><input id="useradd_login" placeholder="login" size="10" /></td>\
				<td><input id="useradd_password" placeholder="password" size="10" /></td>\
				<td><input id="useradd_email" placeholder="email@domain.com" size="15" /></td>\
				<td><input id="useradd_alias" placeholder="alias" size="15" /></td>\
				<td>-</td>\
                <td>-</td>\
				<td><input type="submit" class="submit adduser"  value="' + _pk_translate('General_Save') + '" /></td>\
	  			<td><span class="cancel">' + sprintf(_pk_translate('General_OrCancel'), "", "") + '</span></td>\
	 		</tr>'))
            .appendTo('#users')
        ;
        $('#' + newRowId).keypress(submitOnEnter);
        $('.adduser').click(function () { sendAddUserAJAX($('tr#' + newRowId)); });
        $('.cancel').click(function () {
            piwikHelper.hideAjaxError();
            $(this).parents('tr').remove();
            $('.add-user').toggle();
        });
    });

    $('#access .updateAccess')
        .click(bindUpdateAccess);

    $('#superUserAccess .accessGranted, #superUserAccess .updateAccess').click(bindUpdateSuperUserAccess);

    // when a site is selected, reload the page w/o showing the ajax loading element
    $('#usersManagerSiteSelect').bind('change', function (e, site) {
        if (site.id != piwik.idSite) {
            piwik.broadcast.propagateNewPage('segment=&idSite=' + site.id, false);
        }
    });

    // Show the token_auth
    $('.token_auth').click(function () {
        var token = $(this).data('token');
        if ($(this).text() != token) {
            $(this).text(token);
        }
    });
});
