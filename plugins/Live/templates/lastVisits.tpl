{foreach from=$visitors item=visitor}
	<div id="{$visitor.idVisit}" class="visit{if $visitor.idVisit % 2} alt{/if}">
		<div style="display:none" class="idvisit">{$visitor.idVisit}</div>
			<div class="datetime">
				<span style='display:none' class='serverTimestamp'>{$visitor.serverTimestamp}</span>
				{$visitor.serverDatePretty} - {$visitor.serverTimePretty} ({$visitor.visitDurationPretty})
				&nbsp;<img src="{$visitor.countryFlag}" title="{$visitor.country}, {'Provider_ColumnProvider'|translate} {$visitor.provider}" />
				&nbsp;<img src="{$visitor.browserIcon}" title="{$visitor.browserName}, {'UserSettings_Plugins'|translate}: {$visitor.plugins}" />
				&nbsp;<img src="{$visitor.operatingSystemIcon}" title="{$visitor.operatingSystem}, {$visitor.resolution}" />
				&nbsp;
				{if $visitor.visitConverted}
				<span title="{'General_VisitConvertedNGoals'|translate:$visitor.goalConversions}" class='visitorRank'>
				<img src="themes/default/images/goal.png" />
				<span class='hash'>#</span>{$visitor.goalConversions}
				</span>{/if}
				{if $visitor.visitorType=='returning'}&nbsp;<img src="plugins/Live/templates/images/returningVisitor.gif" title="{'General_ReturningVisitor'|translate}" />{/if}
				{if $visitor.visitIp}- <span title="{if !empty($visitor.visitorId)}{'General_VisitorID'|translate}: {$visitor.visitorId}{/if}">IP: {$visitor.visitIp}</span>{/if}
			</div>
			<!--<div class="settings"></div>-->
			<div class="referer">
				{if $visitor.referrerType != 'direct'}from {if !empty($visitor.referrerUrl)}<a href="{$visitor.referrerUrl|escape:'html'}" target="_blank">{/if}{if !empty($visitor.searchEngineIcon)}<img src="{$visitor.searchEngineIcon}" /> {/if}{$visitor.referrerName|escape:'html'}{if !empty($visitor.referrerUrl)}</a>{/if}
					{if !empty($visitor.referrerKeyword)} - "{$visitor.referrerKeyword|escape:'html'}"{/if}
					{capture assign='keyword'}{$visitor.referrerKeyword|escape:'html'}{/capture}
					{capture assign='searchName'}{$visitor.referrerName|escape:"html"}{/capture}
					{capture assign='position'}#{$visitor.referrerKeywordPosition}{/capture}
					{if !empty($visitor.referrerKeywordPosition)}<span title='{'Live_KeywordRankedOnSearchResultForThisVisitor'|translate:$keyword:$position:$searchName}' class='visitorRank'><span class='hash'>#</span>{$visitor.referrerKeywordPosition}</span>{/if}
				{else}{'Referers_DirectEntry'|translate}{/if}
			</div>
		<div id="{$visitor.idVisit}_actions" class="settings">
			<span class="pagesTitle">{'Actions_SubmenuPages'|translate}:</span>&nbsp;
			{php} $col = 0;	{/php}
			{foreach from=$visitor.actionDetails item=action}
			  {php}
			  	$col++;
		  		if ($col>=9)
		  		{
				  $col=0;
		  		}
				{/php}
				<a href="{$action.url|escape:'html'}" target="_blank">
				{if $action.type == 'action'}
					<img src="plugins/Live/templates/images/file{php} echo $col; {/php}.png" title="{$action.pageTitle} - {$action.serverTimePretty|escape:'html'}" />
				{elseif $action.type == 'outlink'}
					<img class='iconPadding' src="themes/default/images/link.gif" title="{$action.url|escape:'html'} - {$action.serverTimePretty|escape:'html'}" />
				{elseif $action.type == 'download'}
					<img class='iconPadding' src="themes/default/images/download.png" title="{$action.url|escape:'html'} - {$action.serverTimePretty|escape:'html'}" />
				{else}
					<img class='iconPadding' src="themes/default/images/goal.png" title="{$action.goalName|escape:'html'} - {if $action.revenue > 0}{'Live_GoalRevenue'|translate}: {$action.revenue} {$visitor.siteCurrency} - {/if} {$action.serverTimePretty|escape:'html'}" />
				{/if}
				</a>
			{/foreach}
		</div>
	</div>
{/foreach}
