/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function initDashboard(dashboardId, dashboardLayout) {

    // Standard dashboard
    if($('#periodString').length)
    {
        $('#periodString').after($('#dashboardSettings'));
        $('#dashboardSettings').css({left:$('#periodString')[0].offsetWidth});
    }
    // Embed dashboard
    if(!$('#topBars').length)
    {
        $('#dashboardSettings').css({left:0});
        $('#dashboardSettings').after($('#Dashboard'));
        $('#Dashboard > ul li a').each(function(){$(this).css({width:this.offestWidth+30, paddingLeft:0, paddingRight:0});});
        $('#Dashboard_embeddedIndex_'+dashboardId).addClass('sfHover');
    }

    $('#dashboardSettings').on('click', function(){
        $('#dashboardSettings').toggleClass('visible');
        if ($('#dashboardWidgetsArea').dashboard('isDefaultDashboard')) {
            $('#removeDashboardLink').hide();
        } else {
            $('#removeDashboardLink').show();
        }
        // fix position
        $('#dashboardSettings .widgetpreview-widgetlist').css('paddingTop', $('#dashboardSettings .widgetpreview-categorylist').parent('li').position().top);
    });
    $('body').on('mouseup', function(e) {
        if(!$(e.target).parents('#dashboardSettings').length && !$(e.target).is('#dashboardSettings')) {
            $('#dashboardSettings').widgetPreview('reset');
            $('#dashboardSettings').removeClass('visible');
        }
    });

    widgetsHelper.getAvailableWidgets();

    $('#dashboardWidgetsArea').on('dashboardempty', showEmptyDashboardNotification);

    $('#dashboardWidgetsArea').dashboard({
        idDashboard: dashboardId,
        layout: dashboardLayout
    });

    $('#dashboardSettings').widgetPreview({
        isWidgetAvailable: function(widgetUniqueId) {
            return !$('#dashboardWidgetsArea [widgetId=' + widgetUniqueId + ']').length;
        },
        onSelect: function(widgetUniqueId) {
            var widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
            $('#dashboardWidgetsArea').dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
            $('#dashboardSettings').removeClass('visible');
        },
        resetOnSelect: true
    });

    $('#columnPreview>div').each(function(){
        var width = new Array();
        $('div', this).each(function(){
            width.push(this.className.replace(/width-/, ''));
        });
        $(this).attr('layout', width.join('-'));
    });

    $('#columnPreview>div').on('click', function(){
        $('#columnPreview>div').removeClass('choosen');
        $(this).addClass('choosen');
    });

    $('.submenu>li').on('mouseenter', function(event){
        if (!$('.widgetpreview-categorylist', event.target).length) {
            $('#dashboardSettings').widgetPreview('reset');
        }
    });

}

function createDashboard() {
    $('#createDashboardName').attr('value', '');
    piwikHelper.modalConfirm('#createDashboardConfirm', {yes: function(){
        var dashboardName = $('#createDashboardName').attr('value');
        var type = ($('#dashboard_type_empty:checked').length > 0) ? 'empty' : 'default';
        piwikHelper.showAjaxLoading();
        var ajaxRequest =
        {
            type: 'GET',
            url: 'index.php?module=Dashboard&action=createNewDashboard',
            dataType: 'json',
            async: true,
            error: piwikHelper.ajaxHandleError,
            success: function(id) {
                $('#dashboardWidgetsArea').dashboard('loadDashboard', id);
            },
            data: {
                token_auth: piwik.token_auth,
                idSite: piwik.idSite,
                name: encodeURIComponent(dashboardName),
                type: type
            }
        };
        $.ajax(ajaxRequest);
    }});
}

function resetDashboard() {
    piwikHelper.modalConfirm('#resetDashboardConfirm', {yes: function(){ $('#dashboardWidgetsArea').dashboard('resetLayout'); }});
}

function renameDashboard() {
    $('#newDashboardName').attr('value', $('#dashboardWidgetsArea').dashboard('getDashboardName'));
    piwikHelper.modalConfirm('#renameDashboardConfirm', {yes: function(){ $('#dashboardWidgetsArea').dashboard('setDashboardName', $('#newDashboardName').attr('value')); }});
}

function removeDashboard() {
    $('#removeDashboardConfirm h2 span').html($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    piwikHelper.modalConfirm('#removeDashboardConfirm', {yes: function(){ $('#dashboardWidgetsArea').dashboard('removeDashboard'); }});
}

function showChangeDashboardLayoutDialog() {
    $('#columnPreview>div').removeClass('choosen');
    $('#columnPreview>div[layout='+$('#dashboardWidgetsArea').dashboard('getColumnLayout')+']').addClass('choosen');
    piwikHelper.modalConfirm('#changeDashboardLayout', {yes: function(){
        $('#dashboardWidgetsArea').dashboard('setColumnLayout', $('#changeDashboardLayout .choosen').attr('layout'));
    }});
}

function showEmptyDashboardNotification() {
    piwikHelper.modalConfirm('#dashboardEmptyNotification', {
        resetDashboard: function() { $('#dashboardWidgetsArea').dashboard('resetLayout'); },
        addWidget: function(){ $('#dashboardSettings').trigger('click'); }
    });
}

function setAsDefaultWidgets() {
    piwikHelper.modalConfirm('#setAsDefaultWidgetsConfirm', {
        yes: function(){ $('#dashboardWidgetsArea').dashboard('saveLayoutAsDefaultWidgetLayout'); }
    });
}
