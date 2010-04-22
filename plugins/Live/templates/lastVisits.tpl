{foreach from=$visitors item=visitor}
	<div id="{$visitor.idVisit}" class="visit{if $visitor.idVisit % 2} alt{/if}">
		<div style="display:none" class="idvisit">{$visitor.idVisit}</div>
			<div class="datetime">
				{$visitor.serverDatePretty} - {$visitor.serverTimePretty}
				&nbsp;<img src="{$visitor.countryFlag}" title="{$visitor.country}, Provider {$visitor.provider}" />
				&nbsp;<img src="{$visitor.browserIcon}" title="{$visitor.browser} with plugins {$visitor.plugins} enabled" />
				&nbsp;<img src="{$visitor.operatingSystemIcon}" title="{$visitor.operatingSystem}, {$visitor.resolution}" />
				&nbsp;{if $visitor.isVisitorGoalConverted}<img src="{$visitor.goalIcon}" title="{$visitor.goalType}" />{/if}
				{if $visitor.isVisitorReturning}&nbsp;<img src="plugins/Live/templates/images/returningVisitor.gif" title="Returning Visitor" />{/if}
				&nbsp;<label id="" title="IP: {$visitor.ip} - Duration: {$visitor.visitLengthPretty}">more...</label>
			</div>
			<!--<div class="settings"></div>-->
			<div class="referer">
				{if $visitor.refererType != 'directEntry'}from <a href="{$visitor.refererUrl}" target="_blank">{if !empty($visitor.searchEngineIcon)}<img src="{$visitor.searchEngineIcon}" /> {/if}{$visitor.refererName}</a>
					{if !empty($visitor.keywords)}"{$visitor.keywords}"{/if}
				{/if}
				{if $visitor.refererType == 'directEntry'}Direct entry{/if}
			</div>
		<div id="{$visitor.idVisit}_actions" class="settings">
			<span class="pagesTitle">Pages:</span>&nbsp;
			{php} $col = 0;	{/php}
			{foreach from=$visitor.actionDetails item=action}
			  {php}
			  	$col++;
		  		if ($col>=9)
		  		{
				  $col=0;
		  		}
				{/php}
				<a href="{$action.pageUrl}" target="_blank"><img align="middle" src="plugins/Live/templates/images/file{php} echo $col; {/php}.png" title="{$action.pageUrl}" /></a>
			{/foreach}
		</div>
	</div>
{/foreach}
