{if !$show_autocompleter}
<div class="sites_selection">
	<label>{'General_Website'|translate}</label><span id="selectedSiteName" style="display:none">{$siteName}</span>
	<span id="sitesSelection">
		<form action="{url idSite=null}" method="get">
		<select name="idSite">
		   {foreach from=$sites item=info}
		   		<option value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
		   {/foreach}
		</select>
		{hiddenurl idSite=null}
		<input type="submit" value="go" />
		</form>
	</span>

	{literal}<script type="text/javascript">
	$(document).ready(function() {
		var inlinePaddingWidth=22;
		var staticPaddingWidth=34;
		$("#sitesSelection").fdd2div({CssClassName:"custom_select"});
		$("#sitesSelectionWrapper").show();
		if($("#sitesSelection ul")[0]){
			var widthSitesSelection = Math.max($("#sitesSelection ul").width()+inlinePaddingWidth, $("#selectedSiteName").width()+staticPaddingWidth);
			$("#sitesSelectionWrapper").css('padding-right', widthSitesSelection);
			$("#sitesSelection").css('width', widthSitesSelection);
	
			// this will put the anchor after the url before proceed to different site.
			$("#sitesSelection ul li").bind('click',function (e) {
				e.preventDefault();               
				var request_URL = $(e.target).attr("href");
					var new_idSite = broadcast.getValueFromUrl('idSite',request_URL);
					broadcast.propagateNewPage( 'idSite='+new_idSite );
			});
		}else{
			var widthSitesSelection = Math.max($("#sitesSelection").width()+inlinePaddingWidth);
			$("#sitesSelectionWrapper").css('padding-right', widthSitesSelection);
		}
	});</script>
	{/literal}
</div>
{else}
<div class="sites_autocomplete">
    <label>{'General_Website'|translate}</label>
    <div id="sitesSelectionSearch" class="custom_select">
    
        <a href="index.php?module=CoreHome&amp;action=index&amp;period={$period}&amp;date={$date}&amp;idSite={$idSite}" class="custom_select_main_link custom_select_collapsed">{$siteName}</a>
        
        <div class="custom_select_block">
            <div id="custom_select_container">
            <ul class="custom_select_ul_list" >
                {foreach from=$sites item=info}
                    <li><a {if $idSite==$info.idsite} style="display: none"{/if} href="index.php?module=CoreHome&amp;action=index&amp;period={$period}&amp;date={$date}&amp;idSite={$info.idsite}">{$info.name}</a></li>
				{/foreach}
            </ul>
            </div>
            <div class="custom_select_all" style="clear: both">
				<br />
				<a href="index.php?module=MultiSites&amp;action=index&amp;period={$period}&amp;date={$date}&amp;idSite={$idSite}">{'General_MultiSitesSummary'|translate}</a>
			</div>
            
            <div class="custom_select_search">
                <input type="text" length="15" id="websiteSearch" class="inp">
                <input type="hidden" class="max_sitename_width" id="max_sitename_width" value="130" />
                <input type="submit" value="Search" class="but">
				<img title="Clear" id="reset" style="position: relative; top: 4px; left: -44px; cursor: pointer; display: none;" src="plugins/CoreHome/templates/images/reset_search.png"/>
            </div>
        </div>
	</div>
    
	{literal}<script type="text/javascript">
$("#sitesSelectionSearch .custom_select_main_link").click(function(){
	$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
	
		$('#websiteSearch').focus();
	return false;
});
    </script>{/literal}
</div>
{/if}
