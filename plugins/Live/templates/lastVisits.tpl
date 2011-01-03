{foreach from=$visitors item=visitor}
	<div id="{$visitor.idVisit}" class="visit{if $visitor.idVisit % 2} alt{/if}">
		<div style="display:none" class="idvisit">{$visitor.idVisit}</div>
			<div class="datetime">
				{$visitor.serverDatePretty} - {$visitor.serverTimePretty} ({$visitor.visitLengthPretty})
				&nbsp;<img src="{$visitor.countryFlag}" title="{$visitor.country}, {'Provider_ColumnProvider'|translate} {$visitor.provider}" />
				&nbsp;<img src="{$visitor.browserIcon}" title="{$visitor.browser}, {'UserSettings_Plugins'|translate}: {$visitor.plugins}" />
				&nbsp;<img src="{$visitor.operatingSystemIcon}" title="{$visitor.operatingSystem}, {$visitor.resolution}" />
				&nbsp;{if $visitor.isVisitorGoalConverted}<img src="{$visitor.goalIcon}" title="{'Goals_GoalConversion'|translate} ({$visitor.goalType})" />{/if}
				{if $visitor.isVisitorReturning}&nbsp;<img src="plugins/Live/templates/images/returningVisitor.gif" title="Returning Visitor" />{/if}
				{if $visitor.ip}IP: {$visitor.ip}{/if}
			</div>
			<!--<div class="settings"></div>-->
			<div class="referer">
				{if $visitor.refererType != 'directEntry'}from <a href="{$visitor.refererUrl|escape:'html'}" target="_blank">{if !empty($visitor.searchEngineIcon)}<img src="{$visitor.searchEngineIcon}" /> {/if}{$visitor.refererName|escape:'html'}</a>
					{if !empty($visitor.keywords)}"{$visitor.keywords|escape:'html'}"{/if}
				{/if}
				{if $visitor.refererType == 'directEntry'}{'Referers_DirectEntry'|translate}{/if}
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
				<a href="{$action.pageUrl|escape:'html'}" target="_blank"><img align="middle" src="plugins/Live/templates/images/file{php} echo $col; {/php}.png" title="{$action.pageUrl|escape:'html'}" /></a>
			{/foreach}
		</div>
	</div>
{/foreach}
