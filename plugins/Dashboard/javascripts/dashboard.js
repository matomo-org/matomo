/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function createDashboard() {
    $(makeSelectorLastId('createDashboardName')).val('');

    piwikHelper.modalConfirm(makeSelectorLastId('createDashboardConfirm'), {yes: function () {
        var dashboardName = $(makeSelectorLastId('createDashboardName')).val();
        var addDefaultWidgets = ($('[id=dashboard_type_empty]:last:checked').length > 0) ? 0 : 1;

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement();
        ajaxRequest.withTokenInUrl();
        ajaxRequest.addParams({
            module: 'API',
            method: 'Dashboard.createNewDashboardForUser',
            format: 'json'
        }, 'get');
        ajaxRequest.addParams({
            dashboardName: dashboardName,
            addDefaultWidgets: addDefaultWidgets,
            login: piwik.userLogin
        }, 'post');
        ajaxRequest.setCallback(
            function (response) {
                var id = response.value;
                Promise.all([
                  window.Dashboard.DashboardStore.reloadAllDashboards(),
                  window.CoreHome.ReportingMenuStore.reloadMenuItems(),
                  $('#dashboardWidgetsArea').dashboard('rebuildMenu'),
                ]).then(function () {
                  $('#dashboardWidgetsArea').dashboard('loadDashboard', id);
                });
            }
        );
        ajaxRequest.send();
    }});
}

function makeSelectorLastId(domElementId)
{
    // there can be many elements with this id, we prefer the last one
    return '[id=' + domElementId + ']:last';
}

function resetDashboard() {
    piwikHelper.modalConfirm(makeSelectorLastId('resetDashboardConfirm'), {yes:
        function () { $('#dashboardWidgetsArea').dashboard('resetLayout');
    }});
}

function renameDashboard() {
    $(makeSelectorLastId('newDashboardName')).val($('#dashboardWidgetsArea').dashboard('getDashboardName'));

    piwikHelper.modalConfirm(makeSelectorLastId('renameDashboardConfirm'), {yes: function () {
        var newDashboardName = $(makeSelectorLastId('newDashboardName')).val();
        $('#dashboardWidgetsArea').dashboard('setDashboardName', newDashboardName);
    }});
}

function removeDashboard() {
    $(makeSelectorLastId('removeDashboardConfirm')).find('h2 span').text($('#dashboardWidgetsArea').dashboard('getDashboardName'));

    piwikHelper.modalConfirm(makeSelectorLastId('removeDashboardConfirm'), {yes: function () {
        $('#dashboardWidgetsArea').dashboard('removeDashboard');
    }});
}

function showChangeDashboardLayoutDialog() {
    $('#columnPreview').find('>div').removeClass('choosen');
    $('#columnPreview').find('>div[layout=' + $('#dashboardWidgetsArea').dashboard('getColumnLayout') + ']').addClass('choosen');

    var id = makeSelectorLastId('changeDashboardLayout');
    piwikHelper.modalConfirm(id, {yes: function () {
        var layout = $(id).find('.choosen').attr('layout');
        $('#dashboardWidgetsArea').dashboard('setColumnLayout', layout);
    }}, {fixedFooter: true});
}

function showEmptyDashboardNotification() {
    piwikHelper.modalConfirm(makeSelectorLastId('dashboardEmptyNotification'), {
        resetDashboard: function () { $('#dashboardWidgetsArea').dashboard('resetLayout'); },
        addWidget: function () {
          $('.dashboardSettings > a').trigger('click');
        }
    });
}

function setAsDefaultWidgets() {
    piwikHelper.modalConfirm(makeSelectorLastId('setAsDefaultWidgetsConfirm'), {
        yes: function () {
            $('#dashboardWidgetsArea').dashboard('saveLayoutAsDefaultWidgetLayout');
        }
    });
}

function copyDashboardToUser() {
    $(makeSelectorLastId('copyDashboardName')).val($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams({
        module: 'API',
        method: 'UsersManager.getUsers',
        format: 'json',
        filter_limit: '-1'
    }, 'get');
    ajaxRequest.setCallback(
        function (availableUsers) {
            $(makeSelectorLastId('copyDashboardUser')).empty();
            $(makeSelectorLastId('copyDashboardUser')).append(
                $('<option></option>').val(piwik.userLogin).text(piwik.userLogin)
            );
            $.each(availableUsers, function (index, user) {
                if (user.login != 'anonymous' && user.login != piwik.userLogin) {
                    $(makeSelectorLastId('copyDashboardUser')).append(
                        $('<option></option>').val(user.login).text(user.login)
                    );
                }
            });
        }
    );
    ajaxRequest.send();

    piwikHelper.modalConfirm(makeSelectorLastId('copyDashboardToUserConfirm'), {
        yes: function () {
            var copyDashboardName = $(makeSelectorLastId('copyDashboardName')).val();
            var copyDashboardUser = $(makeSelectorLastId('copyDashboardUser')).val();

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams({
                module: 'API',
                method: 'Dashboard.copyDashboardToUser',
                format: 'json'
            }, 'get');
            ajaxRequest.addParams({
                dashboardName: copyDashboardName,
                idDashboard: $('#dashboardWidgetsArea').dashboard('getDashboardId'),
                copyToUser: copyDashboardUser
            }, 'post');
            ajaxRequest.setCallback(
                function (response) {
                    $('#alert').find('h2').text(_pk_translate('Dashboard_DashboardCopied'));
                    piwikHelper.modalConfirm('#alert', {});
                }
            );
            ajaxRequest.withTokenInUrl();
            ajaxRequest.send();
        }
    });
}
