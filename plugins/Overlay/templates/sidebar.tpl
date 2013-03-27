<div> <!-- Wrapper is needed that the html can be jqueryfied -->

    <!-- This div is removed by JS and the content is put in the location div -->
    <div class="Overlay_Location">
        <b>{'Overlay_Location'|translate|escape:'html'}:</b>
		<span data-normalized-url="{$normalizedUrl|escape:'html'}" data-label="{$label|escape:'html'}">
			{$location|escape:'html'}
		</span>
    </div>

    {if count($data)}
        <h2 class="Overlay_MainMetrics">{'Overlay_MainMetrics'|translate|escape:'html'}</h2>
        {foreach from=$data item=metric}
            <div class="Overlay_Metric">
                <span class="Overlay_MetricValue">{$metric.value}</span> {$metric.name|escape:'html'}
            </div>
        {/foreach}
    {else}
        <!-- note: the class Overlay_NoData is used in index.js -->
        <div class="Overlay_NoData">{'Overlay_NoData'|translate|escape:'html'}</div>
    {/if}

</div>