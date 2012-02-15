{loadJavascriptTranslations plugins='CoreHome Dashboard'}

<script type="text/javascript">
    piwik.dashboardLayout = {$layout};
    piwik.idDashboard     = {$dashboardId};
    {*
    the old dashboard layout style is:
    piwik.dashboardLayout = 'VisitsSummary.getEvolutionGraph~VisitorInterest.getNumberOfVisitsPerVisitDuration~UserSettings.getBrowser~ExampleFeedburner.feedburner|Referers.getKeywords~Referers.getWebsites|Referers.getSearchEngines~VisitTime.getVisitInformationPerServerTime~ExampleRssWidget.rssPiwik|';
    *}
</script>

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
        $('#dashboardSettings').css({left:7, top:10});
    }

    $('#dashboardSettings').on('click', function(){
        $('#dashboardSettings').toggleClass('visible');
        // fix position
        $('#dashboardSettings .widgetpreview-widgetlist').css('paddingTop', $('#dashboardSettings .widgetpreview-categorylist').parent('li').position().top);
    });
    $('body').on('mouseup', function(e) {
        if(!$(e.target).parents('#dashboardSettings').length && !$(e.target).is('#dashboardSettings')) {
            $('#dashboardSettings').widgetPreview('reset');
            $('#dashboardSettings').removeClass('visible');
        }
    });
    
    piwik.dashboardObject = new dashboard();
    piwik.dashboardObject.init(piwik.dashboardLayout);

    $('#dashboardSettings').widgetPreview({
        isWidgetAvailable: function(widgetUniqueId) {
            if($('#'+widgetUniqueId, piwik.dashboardObject.dashboardElement).length) {
                return false;
            } else {
                return true;
            }
        },
        onSelect: function(widgetUniqueId) {
            var newDashboardWidget = piwik.dashboardObject.addEmptyWidget(0, widgetUniqueId, true);
            $('.widgetContent', newDashboardWidget).replaceWith(
                $('#dashboardSettings .widgetpreview-preview .widgetContent')
            );
            $('#dashboardSettings').removeClass('visible');
            piwik.dashboardObject.makeSortable();
            piwik.dashboardObject.saveLayout();
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
        if(!$('.widgetpreview-categorylist', event.target).length) {
            $('#dashboardSettings').widgetPreview('reset');
        }
    });
});

function resetDashboard() {
    piwikHelper.windowModal('#resetDashboardConfirm', function(){ piwik.dashboardObject.resetLayout(); })
}

function showChangeDashboardLayoutDialog() {
    $('#columnPreview>div[layout='+piwik.dashboardObject.currentColumnLayout+']').addClass('choosen');
    piwikHelper.windowModal('#changeDashboardLayout', function(){
        piwik.dashboardObject.adjustDashboardColumns($('#changeDashboardLayout .choosen').attr('layout'));
    });
}

</script>
{/literal}
<div id="dashboard">
 
    <div class="ui-confirm" id="confirm">
        <h2>{'Dashboard_DeleteWidgetConfirm'|translate}</h2>
        <input id="yes" type="button" value="{'General_Yes'|translate}" />
        <input id="no" type="button" value="{'General_No'|translate}" />
    </div> 
    
    <div class="ui-confirm" id="resetDashboardConfirm">
        <h2>{'Dashboard_ResetDashboardConfirm'|translate}</h2>
        <input id="yes" type="button" value="{'General_Yes'|translate}" />
        <input id="no" type="button" value="{'General_No'|translate}" />
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
        <input id="yes" type="button" value="{'General_Save'|translate}" />
    </div> 
    
    <div id="dashboardSettings">
        <span>{'Dashboard_WidgetsAndDashboard'|translate}</span>
        <ul class="submenu">
            <li>
                <div id='addWidget'>{'Dashboard_AddAWidget'|translate} &darr;</div>
                <ul class="widgetpreview-categorylist"></ul>
            </li>
            <li onclick="resetDashboard();">{'Dashboard_ResetDashboard'|translate}</li>
            <li onclick="showChangeDashboardLayoutDialog();">{'Dashboard_ChangeDashboardLayout'|translate}</li>
        </ul>
        <ul class="widgetpreview-widgetlist"></ul>
        <div class="widgetpreview-preview"></div>
    </div>
    
    <div class="clear"></div>
    
    <div id="dashboardWidgetsArea"></div>
</div>
