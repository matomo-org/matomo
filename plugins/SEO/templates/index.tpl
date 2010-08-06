<div id='SeoRanks'>
	<script type="text/javascript" src="plugins/SEO/templates/rank.js"></script>
	
	<form method="post" style="padding: 8px;" >
	  <div align="left" class="mediumtext">
		  {'Installation_SetupWebSiteURL'|translate|ucfirst} 
		  <input type="text" id="seoUrl" size="30" value="{$urlToRank}" class="textbox" />
		  <span style="padding-left:2px;"> 
		  <input type="submit" id="rankbutton" value="{'SEO_Rank'|translate}" />
		  </span>
	  </div>
	
		{ajaxLoadingDiv id=ajaxLoadingSEO}

	   <div id="rankStats" align="left" style='margin-top:10px'>
	   		{if empty($ranks)}
	   			{'General_Error'|translate}
	   		{else}
	   			{'SEO_SEORankingsFor'|translate:"<a href='$urlToRank' target='_blank'>$urlToRank</a>"}
	   			<table cellspacing='2' style='margin:auto;line-height:1.5em;padding-top:10px'>
	   			{foreach from=$ranks item=rank}
	   			<tr>
	   				<td><img style='vertical-align:middle;margin-right:6px;' src='{$rank.logo}' border='0' alt="{$rank.label}"> {$rank.label}
	   				</td><td>
	   					<div style='margin-left:15px'>
		   					{if isset($rank.rank)}{$rank.rank}{else}-{/if}
		   					{if $rank.id=='pagerank'} /10 
		   					{elseif $rank.id=='yahoo-bls'} {'SEO_Backlinks'|translate} 
		   					{elseif $rank.id=='yahoo-pages'} {'SEO_Pages'|translate}
		   					{/if}
	   					</div>	
   					</td>
	   			</tr>
	   			{/foreach}
	   			
	   			</table>
	   		{/if}
	   </div>
	</form>
</div>