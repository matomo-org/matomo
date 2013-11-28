/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function initDashboard(dashboardId, dashboardLayout) {

    $('#dashboardSettings').show();
    initTopControls();

    // Embed dashboard
    if (!$('#topBars').length) {
        $('#dashboardSettings').after($('#Dashboard'));
        $('#Dashboard_embeddedIndex_' + dashboardId).addClass('sfHover');
    }

    $('#dashboardSettings').on('click', function (e) {
        if ($(e.target).is('#dashboardSettings') || $(e.target).is('#dashboardSettings>span')) {
            $('#dashboardSettings').toggleClass('visible');
            if ($('#dashboardWidgetsArea').dashboard('isDefaultDashboard')) {
                $('#removeDashboardLink').hide();
            } else {
                $('#removeDashboardLink').show();
            }
            // fix position
            $('#dashboardSettings').find('.widgetpreview-widgetlist').css('paddingTop', $('#dashboardSettings').find('.widgetpreview-categorylist').parent('li').position().top);
        }
    });
    $('body').on('mouseup', function (e) {
        if (!$(e.target).parents('#dashboardSettings').length && !$(e.target).is('#dashboardSettings')) {
            $('#dashboardSettings').widgetPreview('reset');
            $('#dashboardSettings').removeClass('visible');
        }
    });

    widgetsHelper.getAvailableWidgets();

    $('#dashboardWidgetsArea')
        .on('dashboardempty', showEmptyDashboardNotification)
        .dashboard({
            idDashboard: dashboardId,
            layout: dashboardLayout
        });

    $('#dashboardSettings').widgetPreview({
        isWidgetAvailable: function (widgetUniqueId) {
            return !$('#dashboardWidgetsArea').find('[widgetId=' + widgetUniqueId + ']').length;
        },
        onSelect: function (widgetUniqueId) {
            var widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
            $('#dashboardWidgetsArea').dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
            $('#dashboardSettings').removeClass('visible');
        },
        resetOnSelect: true
    });

    $('#columnPreview').find('>div').each(function () {
        var width = [];
        $('div', this).each(function () {
            width.push(this.className.replace(/width-/, ''));
        });
        $(this).attr('layout', width.join('-'));
    });

    $('#columnPreview').find('>div').on('click', function () {
        $('#columnPreview').find('>div').removeClass('choosen');
        $(this).addClass('choosen');
    });

    $('.submenu > li').on('mouseenter', function (event) {
        if (!$('.widgetpreview-categorylist', event.target).length) {
            $('#dashboardSettings').widgetPreview('reset');
        }
    });

}

function createDashboard() {
    $('#createDashboardName').val('');
    piwikHelper.modalConfirm('#createDashboardConfirm', {yes: function () {
        var dashboardName = $('#createDashboardName').val();
        var type = ($('#dashboard_type_empty:checked').length > 0) ? 'empty' : 'default';

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement();
        ajaxRequest.addParams({
            module: 'Dashboard',
            action: 'createNewDashboard'
        }, 'get');
        ajaxRequest.addParams({
            name: encodeURIComponent(dashboardName),
            type: type
        }, 'post');
        ajaxRequest.setCallback(
            function (id) {
                $('#dashboardWidgetsArea').dashboard('loadDashboard', id);
            }
        );
        ajaxRequest.send(true);
    }});
}

function resetDashboard() {
    piwikHelper.modalConfirm('#resetDashboardConfirm', {yes: function () { $('#dashboardWidgetsArea').dashboard('resetLayout'); }});
}

function renameDashboard() {
    $('#newDashboardName').val($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    piwikHelper.modalConfirm('#renameDashboardConfirm', {yes: function () { $('#dashboardWidgetsArea').dashboard('setDashboardName', $('#newDashboardName').val()); }});
}

function removeDashboard() {
    $('#removeDashboardConfirm').find('h2 span').text($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    piwikHelper.modalConfirm('#removeDashboardConfirm', {yes: function () { $('#dashboardWidgetsArea').dashboard('removeDashboard'); }});
}

function showChangeDashboardLayoutDialog() {
    $('#columnPreview').find('>div').removeClass('choosen');
    $('#columnPreview').find('>div[layout=' + $('#dashboardWidgetsArea').dashboard('getColumnLayout') + ']').addClass('choosen');
    piwikHelper.modalConfirm('#changeDashboardLayout', {yes: function () {
        $('#dashboardWidgetsArea').dashboard('setColumnLayout', $('#changeDashboardLayout').find('.choosen').attr('layout'));
    }});
}

function showEmptyDashboardNotification() {
    piwikHelper.modalConfirm('#dashboardEmptyNotification', {
        resetDashboard: function () { $('#dashboardWidgetsArea').dashboard('resetLayout'); },
        addWidget: function () { $('#dashboardSettings').trigger('click'); }
    });
}

function setAsDefaultWidgets() {
    piwikHelper.modalConfirm('#setAsDefaultWidgetsConfirm', {
        yes: function () { $('#dashboardWidgetsArea').dashboard('saveLayoutAsDefaultWidgetLayout'); }
    });
}

function copyDashboardToUser() {
    $('#copyDashboardName').val($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams({
        module: 'API',
        method: 'UsersManager.getUsers',
        format: 'json'
    }, 'get');
    ajaxRequest.setCallback(
        function (availableUsers) {
            $('#copyDashboardUser').empty();
            $('#copyDashboardUser').append(
                $('<option></option>').val(piwik.userLogin).text(piwik.userLogin)
            );
            $.each(availableUsers, function (index, user) {
                if (user.login != 'anonymous' && user.login != piwik.userLogin) {
                    $('#copyDashboardUser').append(
                        $('<option></option>').val(user.login).text(user.login + ' (' + user.alias + ')')
                    );
                }
            });
        }
    );
    ajaxRequest.send(true);

    piwikHelper.modalConfirm('#copyDashboardToUserConfirm', {
        yes: function () {
            var copyDashboardName = $('#copyDashboardName').val();
            var copyDashboardUser = $('#copyDashboardUser').val();

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams({
                module: 'Dashboard',
                action: 'copyDashboardToUser'
            }, 'get');
            ajaxRequest.addParams({
                name: encodeURIComponent(copyDashboardName),
                dashboardId: $('#dashboardWidgetsArea').dashboard('getDashboardId'),
                user: encodeURIComponent(copyDashboardUser)
            }, 'post');
            ajaxRequest.setCallback(
                function (id) {
                    $('#alert').find('h2').text(_pk_translate('Dashboard_DashboardCopied'));
                    piwikHelper.modalConfirm('#alert', {});
                }
            );
            ajaxRequest.send(true);
        }
    });
}
