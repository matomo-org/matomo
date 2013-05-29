<div class="reportsByDimensionView">

    <div class="entityList">
        {foreach from=$dimensionCategories key=category item=dimensions name=dimensionCategories}
            <div class='dimensionCategory'>
                {$category|translate}
                <ul class='listCircle'>
                    {foreach from=$dimensions key=idx item=dimension}
                        <li class="reportDimension {if $idx eq 0 && $smarty.foreach.dimensionCategories.index eq 0}activeDimension{/if}"
                            data-url="{$dimension.url}">
                            <span class='dimension'>{$dimension.title|translate}</span>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/foreach}
    </div>

    <div style="float:left;max-width:900px;">
        <div class="loadingPiwik" style="display:none">
            <img src="themes/default/images/loading-blue.gif" alt=""/>{'General_LoadingData'|translate}
        </div>

        <div class="dimensionReport">{$firstReport}</div>
    </div>
    <div class="clear"></div>

</div>
