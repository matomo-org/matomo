{* some users view thousands of pages which can crash the browser viewing Live! *}
{assign var=maxPagesDisplayedByVisitor value=100}

<ul id='visitsLive'>
{foreach from=$visitors item=visitor}
	<li id="{$visitor.idVisit}" class="visit">
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
				<img src="{$visitor.visitConvertedIcon}" />
				<span class='hash'>#</span>{$visitor.goalConversions}
				{if $visitor.visitEcommerceStatusIcon}
					&nbsp;- <img src="{$visitor.visitEcommerceStatusIcon}" title="{$visitor.visitEcommerceStatus}"/>
				{/if}
				</span>{/if}
				{if $visitor.visitorTypeIcon}&nbsp;- <img src="{$visitor.visitorTypeIcon}" title="{'General_ReturningVisitor'|translate}" />{/if}
				{if $visitor.visitIp}- <span title="{if !empty($visitor.visitorId)}{'General_VisitorID'|translate}: {$visitor.visitorId}{/if}">IP: {$visitor.visitIp}</span>{/if}
			</div>
			<!--<div class="settings"></div>-->
			<div class="referer">
				{if $visitor.referrerType != 'direct'}{'General_FromReferrer'|translate} {if !empty($visitor.referrerUrl)}<a href="{$visitor.referrerUrl|escape:'html'}" target="_blank">{/if}{if !empty($visitor.searchEngineIcon)}<img src="{$visitor.searchEngineIcon}" /> {/if}{$visitor.referrerName|escape:'html'}{if !empty($visitor.referrerUrl)}</a>{/if}
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
			{foreach from=$visitor.actionDetails item=action name=visitorPages}
				{if $smarty.foreach.visitorPages.iteration <= $maxPagesDisplayedByVisitor}
				{if $action.type == 'ecommerceOrder' || $action.type == 'ecommerceAbandonedCart'}
					<span title="
						{if $action.type == 'ecommerceOrder'}{'Goals_EcommerceOrder'|translate}{else}{'Goals_AbandonedCart'|translate}{/if} 
 - {if $action.type == 'ecommerceOrder'}{'Live_GoalRevenue'|translate}: {else}{capture assign='revenueLeft'}{'Live_GoalRevenue'|translate}{/capture}{'Goals_LeftInCart'|translate:$revenueLeft}: {/if}{$action.revenue|money:$idSite} 
 - {$action.serverTimePretty|escape:'html'}  
 {if !empty($action.itemDetails)}{foreach from=$action.itemDetails item=product}
  # {$product.itemSKU}{if !empty($product.itemName)}: {$product.itemName}{/if}{if !empty($product.itemCategory)} ({$product.itemCategory}){/if}, {'General_Quantity'|translate}: {$product.quantity}, {'General_Price'|translate}: {$product.price|money:$idSite} 
{/foreach}{/if}">
						<img class='iconPadding' src="{$action.icon	}" /> 
						{if $action.type == 'ecommerceOrder'}{'Live_GoalRevenue'|translate}: {$action.revenue|money:$idSite} {/if}
					</span>
				{else}
				    {php}$col++; if ($col>=9) { $col=0; }{/php}
					<a href="{$action.url|escape:'html'}" target="_blank">
					{if $action.type == 'action'}
						<img src="plugins/Live/templates/images/file{php} echo $col; {/php}.png" title="{if !empty($action.pageTitle)}{$action.pageTitle}{/if} - {$action.serverTimePretty|escape:'html'}{if isset($action.timeSpentPretty)} - {'General_TimeOnPage'|translate}: {$action.timeSpentPretty}{/if}" />
					{elseif $action.type == 'outlink' || $action.type == 'download'}
						<img class='iconPadding' src="{$action.icon}" title="{$action.url|escape:'html'} - {$action.serverTimePretty|escape:'html'}" />
					{else}
						<img class='iconPadding' src="{$action.icon}" title="{$action.goalName|escape:'html'} - {if $action.revenue > 0}{'Live_GoalRevenue'|translate}: {$action.revenue|money:$idSite} - {/if} {$action.serverTimePretty|escape:'html'}" />
					{/if}
					</a>
				{/if}
				{/if}
			{/foreach}
			{if $smarty.foreach.visitorPages.iteration > $maxPagesDisplayedByVisitor}
				<i>({'Live_MorePagesNotDisplayed'|translate})</i>
			{/if}
		</div>
	</li>
{/foreach}
</ul>
