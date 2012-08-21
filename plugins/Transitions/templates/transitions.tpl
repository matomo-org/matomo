
<div id="Transitions_Container">
	<div id="Transitions_CenterBox" class="Transitions_Text Transitions_Loading">
		<h2></h2>
		<div class="Transitions_CenterBoxMetrics">
			<p class="Transitions_Margin">
				<span class="Transitions_Pageviews Transitions_Metric"></span> {'General_ColumnPageviews'|translate} 
			</p>
			
			<h3>{'Transitions_IncomingTraffic'|translate}</h3>
			<p>
				<span class="Transitions_DirectEntries Transitions_Metric"></span> {'Referers_TypeDirectEntries'|translate:''} 
				(<span class="Transitions_DirectEntriesPercentage"></span>)
			</p>
			<p>
				<span class="Transitions_InternalTrafficIn Transitions_Metric"></span> {'Transitions_FromInternalPages'|translate:''} 
				(<span class="Transitions_InternalTrafficInPercentage"></span>)
			</p>
			<p>
				<span class="Transitions_SearchEngines Transitions_Metric"></span> {'Referers_TypeSearchEngines'|translate:''}
				(<span class="Transitions_SearchEnginesPercentage"></span>)
			</p>
			<p>
				<span class="Transitions_Websites Transitions_Metric"></span> {'Referers_TypeWebsites'|translate:''}
				(<span class="Transitions_WebsitesPercentage"></span>)
			</p>
			
			<h3>{'Transitions_OutgoingTraffic'|translate}</h3>
			<p>
				<span class="Transitions_InternalTrafficOut Transitions_Metric"></span> {'Transitions_ToInternalPages'|translate:''} 
				(<span class="Transitions_InternalTrafficOutPercentage"></span>)
			</p>
			<p>
				<span class="Transitions_Exits Transitions_Metric"></span> {'General_ColumnExits'|translate} 
				(<span class="Transitions_ExitsPercentage"></span>), {'Transitions_Including'|translate}
			</p>
			<p>
				<span class="Transitions_Bounces Transitions_Metric"></span> {'General_ColumnBounces'|translate} 
				(<span class="Transitions_BouncesPercentage"></span>)
			</p>
		</div>
	</div>
	<div id="Transitions_Loops" class="Transitions_Text">
		<span class="Transitions_Loops Transitions_Metric"></span> {'Transitions_Loops'|translate} 
		(<span class="Transitions_LoopsPercentage"></span>)
	</div>
	<canvas id="Transitions_Canvas_Background"></canvas>
	<canvas id="Transitions_Canvas"></canvas>
</div>

<script type="text/javascript">
	var Transitions_Translations = {literal}{{/literal}
		{foreach from=$addTranslations item=key key=i}{if $i > 0},{/if}
		"{$key}": "{$key|translate|addslashes}"
		{/foreach}
	{literal}}{/literal};
</script>