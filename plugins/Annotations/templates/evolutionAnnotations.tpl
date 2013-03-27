<div class="evolution-annotations">
    {foreach from=$annotationCounts item=dateCountPair}
        {assign var=date value=$dateCountPair[0]}
        {assign var=counts value=$dateCountPair[1]}
        <span data-date="{$date}" data-count="{$counts.count}" data-starred="{$counts.starred}"
              {if $counts.count eq 0}title="{'Annotations_AddAnnotationsFor_js'|translate:$date}"
              {elseif $counts.count eq 1}title="{'Annotations_AnnotationOnDate'|translate:$date:$counts.note}

{'Annotations_ClickToEditOrAdd'|translate}"
              {else}title="{'Annotations_ViewAndAddAnnotations_js'|translate:$date}"
                {/if}>
		<img src="themes/default/images/{if $counts.starred > 0}yellow_marker.png{else}grey_marker.png{/if}" width="16" height="16"/>
	</span>
    {/foreach}
</div>
