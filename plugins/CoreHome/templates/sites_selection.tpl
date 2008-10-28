<span id="sitesSelectionWrapper" style="display:none;" >
	{'General_Website'|translate} <span id="selectedSiteName" style="display:none">{$siteName}</span>
	<span id="sitesSelection" style="position:absolute">Site 
		<form action="{url idSite=null}" method="get">
		<select name="idSite">
		   {foreach from=$sites item=info}
		   		<option value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
		   {/foreach}
		</select>
		{hiddenurl idSite=null}
		<input type="submit" value="go"/>
		</form>
	</span>

	{literal}<script language="javascript">
	$(document).ready(function() {
		var extraPadding = 0;
		// if there is only one website, we dont show the arrows image, so no need to add the extra padding
		if( $('#sitesSelection').find('option').size() > 1)
		{
			extraPadding = 21;
		}
		$("#sitesSelectionWrapper").show();
		var widthSitesSelection = $("#selectedSiteName").width() + 4 + extraPadding;
		$("#sitesSelectionWrapper").css('padding-right', widthSitesSelection);
		$("#sitesSelection").fdd2div({CssClassName:"formDiv"});
	});</script>
	{/literal}
</span>