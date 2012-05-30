{loadJavascriptTranslations plugins='CoreHome Dashboard'}

{literal}
<script type="text/javascript">
widgetsHelper.availableWidgets = {/literal}{$availableWidgets}{literal};
$(document).ready(function() {
    initDashboard({/literal}{$dashboardId},{$dashboardLayout}{literal});
});
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
        <h2>{'Dashboard_RemoveDashboardConfirm'|translate:'<span></span>'}</h2>
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
