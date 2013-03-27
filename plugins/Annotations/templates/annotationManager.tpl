<div class="annotation-manager"
     {if $startDate neq $endDate}data-date="{$startDate},{$endDate}" data-period="range"
     {else}data-date="{$startDate}" data-period="{$period}"
        {/if}>

    <div class="annotations-header">
        <span>{'Annotations_Annotations'|translate}</span>
    </div>

    <div class="annotation-list-range">{$startDatePretty}{if $startDate neq $endDate} &mdash; {$endDatePretty}{/if}</div>

    <div class="annotation-list">
        {include file="Annotations/templates/annotations.tpl"}

        <span class="loadingPiwik" style="display:none"><img src="themes/default/images/loading-blue.gif"/>{'General_Loading_js'|translate}</span>

    </div>

    <div class="annotation-controls">
        {if $canUserAddNotes}
            <a href="#" class="add-annotation" title="{'Annotations_CreateNewAnnotation'|translate}">{'Annotations_CreateNewAnnotation'|translate}</a>
        {elseif $userLogin eq 'anonymous'}
            <a href="index.php?module=Login">{'Annotations_LoginToAnnotate'|translate}</a>
        {/if}
    </div>

</div>
