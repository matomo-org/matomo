{loadJavascriptTranslations plugins='CoreHome Dashboard'}

{literal}
<script type="text/javascript">
$(document).ready( function() {
    // Standard dashboard
    if($('#periodString').length) 
    {
        $('#periodString').after($('#dashboardSettings'));
        $('#dashboardSettings').css({left:$('#periodString')[0].offsetWidth+10});
    }
    // Embed dashboard
    else 
    {
        $('#dashboardSettings').css({left:0, top:13});
        $('#dashboardSettings').after($('#Dashboard'));
        $('#Dashboard').css({left: $('#dashboardSettings')[0].offsetWidth+15, top: 13});
        $('#dashboardWidgetsArea').css({marginTop: 30});
    }

    $('#dashboardSettings').on('click', function(){
        $('#dashboardSettings').toggleClass('visible');
        if ($('dashboardWidgetsArea').dashboard('isDefaultDashboard')) {
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
    
    $('#dashboardWidgetsArea').on('dashboardempty', showEmptyDashboardNotification);

    $('#dashboardWidgetsArea').dashboard({
        idDashboard: {/literal}{$dashboardId}{literal},
        layout: {/literal}{$dashboardLayout}{literal}
    });

    $('#dashboardSettings').widgetPreview({
        isWidgetAvailable: function(widgetUniqueId) {
            if ($('#dashboardWidgetsArea [widgetId='+widgetUniqueId+']').length) {
                return false;
            } else {
                return true;
            }
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
        })
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

});

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

</script>
{/literal}
<div id="dashboard">
 
    <div class="ui-confirm" id="confirm">
        <h2>{'Dashboard_DeleteWidgetConfirm'|translate}</h2>
        <input role="yes" type="button" value="{'General_Yes'|translate}" />
        <input role="no" type="button" value="{'General_Cancel'|translate}" />
    </div>

    <div class="ui-confirm" id="setAsDefaultWidgetsConfirm">
        <h2>{'Dashboard_SetAsDefaultWidgetsConfirm'|translate}</h2>
        {capture assign=resetDashboard}{'Dashboard_ResetDashboard'|translate}{/capture}
        <div class="popoverSubMessage">{'Dashboard_SetAsDefaultWidgetsConfirmHelp'|translate:$resetDashboard}</div>
        <input role="yes" type="button" value="{'General_Yes'|translate}" />
        <input role="no" type="button" value="{'General_Cancel'|translate}" />
    </div>

    <div class="ui-confirm" id="resetDashboardConfirm">
        <h2>{'Dashboard_ResetDashboardConfirm'|translate}</h2>
        <input role="yes" type="button" value="{'General_Yes'|translate}" />
        <input role="no" type="button" value="{'General_Cancel'|translate}" />
    </div> 

    <div class="ui-confirm" id="dashboardEmptyNotification">
        <h2>{'Dashboard_DashboardEmptyNotification'|translate}</h2>
        <input role="addWidget" type="button" value="{'Dashboard_AddAWidget'|translate}" />
        <input role="resetDashboard" type="button" value="{'Dashboard_ResetDashboard'|translate}" />
    </div>

    <div class="ui-confirm" id="changeDashboardLayout">
        <h2>{'Dashboard_SelectDashboardLayout'|translate}</h2>
        <div id="columnPreview">
        {foreach from=$availableLayouts item=layout}
            <div>
            {foreach from=$layout item=column}
                 <div class="width-{$column}"><span></span></div>
            {/foreach}
            </div>
        {/foreach}
        </div>
        <input role="yes" type="button" value="{'General_Save'|translate}" />
    </div>

    <div class="ui-confirm" id="renameDashboardConfirm">
        <h2>{'Dashboard_RenameDashboard'|translate}</h2>
        <div id="newDashboardNameInput"><label for="newDashboardName">{'Dashboard_DashboardName'|translate} </label><input type="input" name="newDashboardName" id="newDashboardName" value=""/></div>
        <input role="yes" type="button" value="{'General_Save'|translate}" />
        <input role="cancel" type="button" value="{'General_Cancel'|translate}" />
    </div>

    <div class="ui-confirm" id="createDashboardConfirm">
        <h2>{'Dashboard_CreateNewDashboard'|translate}</h2>
        <div id="createDashboardNameInput">
            <label>{'Dashboard_DashboardName'|translate} <input type="input" name="newDashboardName" id="createDashboardName" value=""/></label><br />
            <label><input type="radio" checked="checked" name="type" value="default" id="dashboard_type_default">{'Dashboard_DefaultDashboard'|translate}</label><br />
            <label><input type="radio" name="type" value="empty" id="dashboard_type_empty">{'Dashboard_EmptyDashboard'|translate}</label>
        </div>
        <input role="yes" type="button" value="{'General_Yes'|translate}" />
        <input role="no" type="button" value="{'General_Cancel'|translate}" />
    </div>

    <div class="ui-confirm" id="removeDashboardConfirm">
        <h2>{'Dashboard_RemoveDashboardConfirm'|translate}</h2>
        <div class="popoverSubMessage">{'Dashboard_NotUndo'|translate:$resetDashboard}</div>
        <input role="yes" type="button" value="{'General_Yes'|translate}" />
        <input role="no" type="button" value="{'General_Cancel'|translate}" />
    </div>

    <div id="dashboardSettings">
        <span>{'Dashboard_WidgetsAndDashboard'|translate}</span>
        <ul class="submenu">
            <li>
                <div id="addWidget">{'Dashboard_AddAWidget'|translate} &darr;</div>
                <ul class="widgetpreview-categorylist"></ul>
            </li>
            <li>
                <div id="manageDashboard">{'Dashboard_ManageDashboard'|translate} &darr;</div>
                <ul>
                    <li onclick="resetDashboard();">{'Dashboard_ResetDashboard'|translate}</li>
                    <li onclick="showChangeDashboardLayoutDialog();">{'Dashboard_ChangeDashboardLayout'|translate}</li>
                    {if ($userLogin && 'anonymous' != $userLogin)}
                    <li onclick="renameDashboard();">{'Dashboard_RenameDashboard'|translate}</li>
                    <li onclick="removeDashboard();" id="removeDashboardLink">{'Dashboard_RemoveDashboard'|translate}</li>
                    {if ($isSuperUser)}
                    <li onclick="setAsDefaultWidgets();">{'Dashboard_SetAsDefaultWidgets'|translate}</li>
                    {/if}
                    {/if}
                </ul>
            </li>
            {if ($userLogin && 'anonymous' != $userLogin)}
            <li onclick="createDashboard();">{'Dashboard_CreateNewDashboard'|translate}</li>
            {/if}
        </ul>
        <ul class="widgetpreview-widgetlist"></ul>
        <div class="widgetpreview-preview"></div>
    </div>
    
    <div class="clear"></div>
    
    <div id="dashboardWidgetsArea"></div>
</div>
