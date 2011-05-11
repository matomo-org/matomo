<div class="sites_autocomplete">
    <label>{'General_Website'|translate}</label>
    <div id="sitesSelectionSearch" class="custom_select">
    
        <a href="javascript:broadcast.propagateNewPage( 'idSite={$idSite}' );" class="custom_select_main_link">{$siteName}</a>
        
        <div class="custom_select_block">
            <div id="custom_select_container">
            <ul class="custom_select_ul_list" >
                {foreach from=$sites item=info}
                    <li {if $idSite==$info.idsite} style="display: none"{/if}><a href="javascript:broadcast.propagateNewPage( 'idSite={$info.idsite}');">{$info.name}</a></li>
				{/foreach}
            </ul>
            </div>
            <div class="custom_select_all" style="clear: both">
				<br />
				<a href="index.php?module=MultiSites&amp;action=index&amp;period={$period}&amp;date={$rawDate}&amp;idSite={$idSite}">{'General_MultiSitesSummary'|translate}</a>
			</div>
            
            <div class="custom_select_search">
                <input type="text" length="15" id="websiteSearch" class="inp">
                <input type="hidden" class="max_sitename_width" id="max_sitename_width" value="130" />
                <input type="submit" value="Search" class="but">
				<img title="Clear" id="reset" style="position: relative; top: 4px; left: -44px; cursor: pointer; display: none;" src="plugins/CoreHome/templates/images/reset_search.png"/>
            </div>
        </div>
	</div>
    
	<script type="text/javascript">
    {if !$show_autocompleter}{literal}
    $('.custom_select_search').hide();
    $('.custom_select_all').hide();
    {/literal}{/if}
	{literal}
    if($('.custom_select_ul_list li').length > 1) {
        $("#sitesSelectionSearch .custom_select_main_link").click(function(){
    		$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
    		$('#websiteSearch').focus();
    		return false;
    	});
        var inlinePaddingWidth=22;
        var staticPaddingWidth=34;
        if($(".custom_select_block ul")[0]){
            var widthSitesSelection = Math.max($(".custom_select_block ul").width()+inlinePaddingWidth, $(".custom_select_main_link").width()+staticPaddingWidth);
            $(".custom_select_block").css('width', widthSitesSelection);
        }
    } else {
        $('.custom_select_main_link').addClass('noselect');
    }
    {/literal}
    </script>
</div>

