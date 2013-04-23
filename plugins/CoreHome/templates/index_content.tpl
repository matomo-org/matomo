<div class="page">
    <div class="pageWrap">
        <div class="nav_sep"></div>
        <div class="top_controls">
            {include file="CoreHome/templates/period_select.tpl"}
            {postEvent name="template_nextToCalendar"}
            {include file="CoreHome/templates/header_message.tpl"}
            {ajaxRequestErrorDiv}
        </div>

        {ajaxLoadingDiv}

        <div id="content" class="home">
            {if $content}{$content}{/if}
        </div>
        <div class="clear"></div>
    </div>
</div>

<br/><br/>