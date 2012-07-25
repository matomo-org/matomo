<div class="sites_autocomplete">
    <div id="sitesSelectionSearch" class="custom_select">
    
        <a href="index.php?module=CoreHome&amp;action=index&amp;idSite={$idSite}&amp;period={$period}&amp;date={$rawDate}" siteid="{$idSite}" onclick="return false" class="custom_select_main_link">{$siteName}</a>
        
        <div class="custom_select_block">
            <div id="custom_select_container">
            <ul class="custom_select_ul_list" >
                {foreach from=$sites item=info}
                    <li {if $idSite==$info.idsite} style="display: none"{/if}><a href="index.php?module=CoreHome&amp;action=index&amp;idSite={$info.idsite}&amp;period={$period}&amp;date={$rawDate}" siteid="{$info.idsite}">{$info.name}</a></li>
				{/foreach}
            </ul>
            </div>
            <div class="custom_select_all" style="clear: both">
				<br />
				<a href="index.php?module=MultiSites&amp;action=index&amp;period={$period}&amp;date={$rawDate}&amp;idSite={$idSite}">{'General_MultiSitesSummary'|translate}</a>
			</div>
            
            <div class="custom_select_search">
                <input type="text" length="15" id="websiteSearch" class="inp"/>
                <input type="hidden" class="max_sitename_width" id="max_sitename_width" value="130" />
                <input type="submit" value="Search" class="but"/>
				<img title="Clear" id="reset" style="position: relative; top: 4px; left: -44px; cursor: pointer; display: none;" src="plugins/CoreHome/templates/images/reset_search.png"/>
            </div>
        </div>
	</div>
    
	<script type="text/javascript">
    {if !$show_autocompleter}{literal}
    $('.custom_select_search').hide();
    {/literal}{/if}
	{literal}
	// set event handling code for non-jquery-autocomplete parts of widget
    if($('.custom_select_ul_list li').length > 1) {
    	// event handler for when site selector is clicked. shows dropdown w/ first X sites
        $("#sitesSelectionSearch .custom_select_main_link").click(function(){
    		$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
    		$('.custom_select_ul_list').show();
    		$('#websiteSearch').val('').focus();
    		return false;
    	});
        $('#sitesSelectionSearch .custom_select_block').on('mouseenter', function(){
            $('.custom_select_ul_list li a').each(function(){
                var hash = broadcast.getHashFromUrl();
                hash = hash ? hash.replace(/idSite=[0-9]+/, 'idSite='+$(this).attr('siteid')) : "";
                
                var queryString = piwikHelper.getCurrentQueryStringWithParametersModified(
                	'idSite=' + $(this).attr('siteid'));
                $(this).attr('href', queryString + hash);
            });
        });

        // change selection. fire's site selector's on select event and modifies the attributes
        // of the selected link
		$('.custom_select_ul_list li a').each(function(){
            $(this).click(function (e) {
            	var idsite = $(this).attr('siteid'), name = $(this).text();
            	window.autocompleteOnNewSiteSelect(idsite, name);
            	
            	$("#sitesSelectionSearch .custom_select_main_link")
            		.attr('href', $(this).attr('href'))
            		.attr('siteid', idsite)
            		.text(name);
            	
            	// close the dropdown
    			$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
    			
    			e.preventDefault();
            });
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
