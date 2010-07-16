<div class="sites_selection">
<span id="sitesSelectionWrapper" style="display:none;" >
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
		var inlineWaddingWidth=22;
		var staticPaddingWidth=34;
		$("#sitesSelection").fdd2div({CssClassName:"custom_select"});
		$("#sitesSelectionWrapper").show();
		if($("#sitesSelection ul")[0]){
			var widthSitesSelection = Math.max($("#sitesSelection ul").width()+inlineWaddingWidth, $("#selectedSiteName").width()+staticPaddingWidth);
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
			var widthSitesSelection = Math.max($("#sitesSelection").width()+inlineWaddingWidth);
			$("#sitesSelectionWrapper").css('padding-right', widthSitesSelection);
		}
	});</script>
	{/literal}
</span>
</div>

<!-- new site selection autocomplete control (html example) 
<div class="sites_autocomplete">
    <label>Website</label>
    <div id="sitesSelectionSearch" class="custom_select">
    
        <a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=1" class="custom_select_main_link custom_select_collapsed">BuyForSeniors</a>
        
        <div class="custom_select_block">
            <ul class="custom_select_ul_list"
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=4">CCSlaughterhouse</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=8">CMSJam</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=2">DazzlingDonna</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=9">eBuzdsdsdsdsdz- Coach</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=7">HabariTips</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=30">Name</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=5">PressKitTemplates</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=32">UTC+12</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=3">VistaJunkie</a></li>
                <li><a href="index.php?module=CoreHome&amp;action=index&amp;period=month&amp;date=2009-06-19&amp;idSite=6">WowWayCool</a></li>
            </ul>
            
            <div class="custom_select_all"><a href="#">All websites...</a></div>
            
            <div class="custom_select_search">
                <input type="text" length="15" id="keyword" class="inp">
                <input type="submit" value="Search" class="but">
            </div>
        </div>
	</div>
    
	{literal}<script type="text/javascript">
    	$("#sitesSelectionSearch .custom_select_main_link").click(function(){
			$("#sitesSelectionSearch .custom_select_main_link").toggleClass("custom_select_loading");
			$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
			return false;
		});
    </script>{/literal}
</div>
-->
