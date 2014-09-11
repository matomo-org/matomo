/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function sendUserSettingsAJAX() {
    var params;
    var defaultDate = $('input[name=defaultDate]:checked').val();
    if (defaultDate == 'today' || defaultDate == 'yesterday') {
        params = 'period=day&date=' + defaultDate;
    } else if (defaultDate.indexOf('last') >= 0
        || defaultDate.indexOf('previous') >= 0) {
        params = 'period=range&date=' + defaultDate;
    } else {
        params = 'date=today&period=' + defaultDate;
    }

    var alias = $('#alias').val();
    var email = $('#email').val();
    var password = $('#password').val();
    var passwordBis = $('#passwordBis').val();
    var defaultReport = $('input[name=defaultReport]:checked').val();

    if (defaultReport == 1) {
        defaultReport = $('#defaultReportSiteSelector').attr('siteid');
    }
    var postParams = {};
    postParams.alias = alias;
    postParams.email = email;
    if (password) {
        postParams.password = password;
    }
    if (passwordBis) {
        postParams.passwordBis = passwordBis;
    }
    postParams.defaultReport = defaultReport;
    postParams.defaultDate = defaultDate;
    postParams.language = $('#userSettingsTable #language').val();

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'UsersManager',
        format: 'json',
        action: 'recordUserSettings'
    }, 'GET');
    ajaxHandler.addParams(postParams, 'POST');
    ajaxHandler.redirectOnSuccess(params);
    ajaxHandler.setLoadingElement('#ajaxLoadingUserSettings');
    ajaxHandler.setErrorElement('#ajaxErrorUserSettings');
    ajaxHandler.send(true);
}
function sendAnonymousUserSettingsAJAX() {
    var anonymousDefaultReport = $('input[name=anonymousDefaultReport]:checked').val();
    if (anonymousDefaultReport == 1) {
        anonymousDefaultReport = $('#anonymousDefaultReportWebsite').find('option:selected').val();
    }
    var anonymousDefaultDate = $('input[name=anonymousDefaultDate]:checked').val();

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
        module: 'UsersManager',
        format: 'json',
        action: 'recordAnonymousUserSettings'
    }, 'GET');
    ajaxHandler.addParams({
        anonymousDefaultReport: anonymousDefaultReport,
        anonymousDefaultDate: anonymousDefaultDate
    }, 'POST');
    ajaxHandler.redirectOnSuccess();
    ajaxHandler.setLoadingElement('#ajaxLoadingAnonymousUserSettings');
    ajaxHandler.setErrorElement('#ajaxErrorAnonymousUserSettings');
    ajaxHandler.send(true);
}

$(document).ready(function () {
    $('#userSettingsSubmit').click(function () {
        if ($('#password').length > 0 && $('#password').val() != '') {
            piwikHelper.modalConfirm('#confirmPasswordChange', {yes: sendUserSettingsAJAX});
        } else {
            sendUserSettingsAJAX();
        }

    });
    $('#userSettingsTable').find('input').keypress(function (e) {
        var key = e.keyCode || e.which;
        if (key == 13) {
            $('#userSettingsSubmit').click();
        }
    });

    $('#anonymousUserSettingsSubmit').click(function () {
        sendAnonymousUserSettingsAJAX();
    });
});
