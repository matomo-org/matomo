<div id="{$properties.uniqueId}" class="visitorLog">
{if !$isWidget}
	<h2>{if $javascriptVariablesToSet.filterEcommerce}{'Goals_EcommerceLog'|translate}{else}{'Live_VisitorLog'|translate}{/if}</h2>
		
	{if !empty($reportDocumentation)}
		<div class="reportDocumentation"><p>{$reportDocumentation}</p></div>
	{/if}
{/if}
{capture assign='displayVisitorsInOwnColumn'}{if $isWidget}0{else}1{/if}{/capture}

<a graphid="VisitsSummarygetEvolutionGraph" name="evolutionGraph"></a>
{assign var=maxIdVisit value=0}
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
		{$arrayDataTable.message}
	{else}
		{if count($arrayDataTable) == 0}
		<a name="{$properties.uniqueId}"></a>
		<div class="pk-emptyDataTable">{'CoreHome_ThereIsNoDataForThisReport'|translate}</div>
		{else}
			<a name="{$properties.uniqueId}"></a>

	<table class="dataTable" cellspacing="0" width="100%" style="width:100%;">
	<thead>
	<tr>
	<th style="display:none"></th>
	<th id="label" class="sortable label" style="cursor: auto;width:12%" width="12%">
	<div id="thDIV">{'General_Date'|translate}<div></th>
	{if $displayVisitorsInOwnColumn}
	<th id="label" class="sortable label" style="cursor: auto;width:13%" width="13%">
	<div id="thDIV">{'General_Visitors'|translate}<div></th>
	{/if}
	<th id="label" class="sortable label" style="cursor: auto;width:15%" width="15%">
	<div id="thDIV">{'Live_Referrer_URL'|translate}<div></th>
	<th id="label" class="sortable label" style="cursor: auto;width:62%" width="62%">
	<div id="thDIV">{'General_ColumnNbActions'|translate}<div></th>
	</tr>
	</thead>
	<tbody>

{foreach from=$arrayDataTable item=visitor}
{if $maxIdVisit == 0 || $visitor.columns.idVisit < $maxIdVisit}
{assign var=maxIdVisit value=$visitor.columns.idVisit}
{/if}

	{capture assign='visitorColumnContent'}
		&nbsp;<img src="{$visitor.columns.countryFlag}" title="{$visitor.columns.country}, Provider {$visitor.columns.provider}" />
		&nbsp;<img src="{$visitor.columns.browserIcon}" title="{$visitor.columns.browserName} with plugins {$visitor.columns.plugins} enabled" />
		&nbsp;<img src="{$visitor.columns.operatingSystemIcon}" title="{$visitor.columns.operatingSystem}, {$visitor.columns.resolution} ({$visitor.columns.screenType})" />
		{if $visitor.columns.visitorTypeIcon}
			&nbsp;- <img src="{$visitor.columns.visitorTypeIcon}" title="{'General_ReturningVisitor'|translate}" />
		{/if}
		
		{if !$displayVisitorsInOwnColumn} <br/> <br/> {/if}
		
		&nbsp;{if $visitor.columns.visitConverted}
		<span title="{'General_VisitConvertedNGoals'|translate:$visitor.columns.goalConversions}" class='visitorRank' {if !$displayVisitorsInOwnColumn}style='margin-left:0'{/if}>
		<img src="{$visitor.columns.visitConvertedIcon}" />
		<span class='hash'>#</span>{$visitor.columns.goalConversions}
		{if $visitor.columns.visitEcommerceStatusIcon}
			&nbsp;- <img src="{$visitor.columns.visitEcommerceStatusIcon}" title="{$visitor.columns.visitEcommerceStatus}"/>
		{/if}
		</span>{/if}
		<br/>
		{if $displayVisitorsInOwnColumn}
			{if count($visitor.columns.pluginsIcons) > 0}
				<hr/>
				{'UserSettings_Plugins'|translate}:
					{foreach from=$visitor.columns.pluginsIcons item=pluginIcon name=plugins}
						<img src="{$pluginIcon.pluginIcon}" title="{$pluginIcon.pluginName|capitalize:true}" alt="{$pluginIcon.pluginName|capitalize:true}" />
					{/foreach}
			{/if}
		{/if}
	{/capture}
	
	{capture assign='visitorRow'}
	<tr class="label{cycle values='odd,even'}">
	<td style="display:none;"></td>
	<td class="label" style="width:12%" width="12%">
				<strong title="{if $visitor.columns.visitorType=='new'}{'General_NewVisitor'|translate}{else}{'Live_VisitorsLastVisit'|translate:$visitor.columns.daysSinceLastVisit}{/if}">
				{$visitor.columns.serverDatePrettyFirstAction} 
				{if $isWidget}<br/>{else}-{/if} {$visitor.columns.serverTimePrettyFirstAction}</strong>
				{if !empty($visitor.columns.visitIp)} <br/><span title="{if !empty($visitor.columns.visitorId)}{'General_VisitorID'|translate}: {$visitor.columns.visitorId}{/if}">IP: {$visitor.columns.visitIp}</span>{/if}
				
				{if (isset($visitor.columns.provider)&&$visitor.columns.provider!='IP')} 
					<br />
					{'Provider_ColumnProvider'|translate}: 
					<a href="{$visitor.columns.providerUrl}" target="_blank" title="{$visitor.columns.providerUrl}" style="text-decoration:underline;">
						{$visitor.columns.provider}
					</a>
				{/if}
				{if !empty($visitor.columns.customVariables)}
					<br/>
					{foreach from=$visitor.columns.customVariables item=customVariable key=id}
						{capture assign=name}customVariableName{$id}{/capture}
						{capture assign=value}customVariableValue{$id}{/capture}
						<br/><acronym title="{'CustomVariables_CustomVariables'|translate} (index {$id})">{$customVariable.$name|truncate:30:"...":true|escape:'html'}</acronym>: {$customVariable.$value|truncate:50:"...":true|escape:'html'}
					{/foreach}
				{/if}
				{if !$displayVisitorsInOwnColumn}
					<br/>
					{$visitorColumnContent}
				{/if}
	</td>
	
	{if $displayVisitorsInOwnColumn}
	<td class="label" style="width:13%" width="13%">
		{$visitorColumnContent}
	</td>
	{/if}
	
	<td class="column" style="width:20%" width="20%">
		<div class="referer">
			{if $visitor.columns.referrerType == 'website'}
				{'Referers_ColumnWebsite'|translate}:
				<a href="{$visitor.columns.referrerUrl|escape:'html'}" target="_blank" title="{$visitor.columns.referrerUrl|escape:'html'}" style="text-decoration:underline;">
					{$visitor.columns.referrerName|escape:'html'}
				</a>
			{/if}
			{if $visitor.columns.referrerType == 'campaign'}
				{'Referers_ColumnCampaign'|translate}
				<br />
				{$visitor.columns.referrerName|escape:'html'}
				{if !empty($visitor.columns.referrerKeyword)} - {$visitor.columns.referrerKeyword|escape:'html'}{/if}
			{/if}
			{if $visitor.columns.referrerType == 'search'}
				{if !empty($visitor.columns.searchEngineIcon)}
					<img src="{$visitor.columns.searchEngineIcon}" alt="{$visitor.columns.referrerName|escape:'html'}" /> 
				{/if}
				{$visitor.columns.referrerName|escape:'html'}
				{if !empty($visitor.columns.referrerKeyword)}{'Referers_Keywords'|translate}:
				<br />
				<a href="{$visitor.columns.referrerUrl|escape:'html'}" target="_blank" style="text-decoration:underline;">
						"{$visitor.columns.referrerKeyword|escape:'html'}"</a>
				{/if}
				{capture assign='keyword'}{$visitor.columns.referrerKeyword|escape:'html'}{/capture}
				{capture assign='searchName'}{$visitor.columns.referrerName|escape:"html"}{/capture}
				{capture assign='position'}#{$visitor.columns.referrerKeywordPosition}{/capture}
				{if !empty($visitor.columns.referrerKeywordPosition)}<span title='{'Live_KeywordRankedOnSearchResultForThisVisitor'|translate:$keyword:$position:$searchName}' class='visitorRank'><span class='hash'>#</span>{$visitor.columns.referrerKeywordPosition}</span>{/if}
			{/if}
			{if $visitor.columns.referrerType == 'direct'}{'Referers_DirectEntry'|translate}{/if}
		</div>
	</td>
	<td class="column {if $visitor.columns.visitConverted && !$isWidget}highlightField{/if}" style="width:55%" width="55%">
			<strong>
				{$visitor.columns.actionDetails|@count}
				{if $visitor.columns.actionDetails|@count <= 1}
					{'Live_Action'|translate} 
				{else}
					{'Live_Actions'|translate}
				{/if}
				- {$visitor.columns.visitDurationPretty}
			</strong>
			<br />
			<ol class='visitorLog'>
			{capture assign='visitorHasSomeEcommerceActivity'}0{/capture}
			{foreach from=$visitor.columns.actionDetails item=action}
				{capture assign='customVariablesTooltip'}
				{if !empty($action.customVariables)}
					{'CustomVariables_CustomVariables'|translate} 
					{foreach from=$action.customVariables item=customVariable key=id}
						{capture assign=name}customVariableName{$id}{/capture}
						{capture assign=value}customVariableValue{$id}{/capture}
						 - {$customVariable.$name|escape:'html'} = {$customVariable.$value|escape:'html'}
					{/foreach}
				{/if}
				{/capture}
				{if !$javascriptVariablesToSet.filterEcommerce 	
					|| $action.type == 'ecommerceOrder' 	
					|| $action.type == 'ecommerceAbandonedCart'}
				<li class="{if !empty($action.goalName)}goal{else}action{/if}" title="{$action.serverTimePretty|escape:'html'}{if !empty($action.url) && strlen(trim($action.url))} - {$action.url|escape:'html'}{/if} {if strlen(trim($customVariablesTooltip))} - {$customVariablesTooltip}{/if}{if isset($action.timeSpentPretty)} - {'General_TimeOnPage'|translate}: {$action.timeSpentPretty}{/if}">
				{if $action.type == 'ecommerceOrder' || $action.type == 'ecommerceAbandonedCart'}
 					{* Ecommerce Abandoned Cart / Ecommerce Order *}
 					
					<img src="{$action.icon}" /> 
					{if $action.type == 'ecommerceOrder'}
 					{capture assign='visitorHasSomeEcommerceActivity'}1{/capture}
 					<strong>{'Goals_EcommerceOrder'|translate}</strong> <span style='color:#666666'>({$action.orderId})</span>
					{else}<strong>{'Goals_AbandonedCart'|translate}</strong>
					{/if} <br/>
					<span {if !$isWidget}style='margin-left:20px'{/if}>
					{if $action.type == 'ecommerceOrder'}
						<abbr title="
						{'Live_GoalRevenue'|translate}: {$action.revenue|money:$javascriptVariablesToSet.idSite} 
						{if !empty($action.revenueSubTotal)} - {'General_Subtotal'|translate}: {$action.revenueSubTotal|money:$javascriptVariablesToSet.idSite}{/if} 
						{if !empty($action.revenueTax)} - {'General_Tax'|translate}: {$action.revenueTax|money:$javascriptVariablesToSet.idSite}{/if} 
						{if !empty($action.revenueShipping)} - {'General_Shipping'|translate}: {$action.revenueShipping|money:$javascriptVariablesToSet.idSite}{/if} 
						{if !empty($action.revenueDiscount)} - {'General_Discount'|translate}: {$action.revenueDiscount|money:$javascriptVariablesToSet.idSite}{/if} 
						">{'Live_GoalRevenue'|translate}:
					{else}
						{capture assign='revenueLeft'}{'Live_GoalRevenue'|translate}{/capture}{'Goals_LeftInCart'|translate:$revenueLeft}:
					{/if}
					<strong>{$action.revenue|money:$javascriptVariablesToSet.idSite}</strong>{if $action.type == 'ecommerceOrder'}</abbr>{/if}, 
					{'General_Quantity'|translate}: {$action.items}
 					
 					{* Ecommerce items in Cart/Order *}
 					{if !empty($action.itemDetails)}
 					<ul style='list-style:square;margin-left:{if $isWidget}15{else}50{/if}px'>
 					{foreach from=$action.itemDetails item=product}
						<li>{$product.itemSKU}{if !empty($product.itemName)}: {$product.itemName}{/if}{if !empty($product.itemCategory)} ({$product.itemCategory}){/if}, 
						{'General_Quantity'|translate}: {$product.quantity},
						{'General_Price'|translate}: {$product.price|money:$javascriptVariablesToSet.idSite}
						</li> 					
 					{/foreach}
 					</ul>
 					{/if}
					</span>
					
				{elseif empty($action.goalName)}
				{* Page view / Download / Outlink *}
					{if !empty($action.pageTitle)>0}
						{$action.pageTitle|unescape|urldecode|escape:'html'|truncate:80:"...":true}
						<br/>
					{/if}
					{if $action.type == 'download'
						|| $action.type == 'outlink'}
						<img src='{$action.icon}'>
					{/if}
					{if !empty($action.url)}
					 	<a href="{$action.url|escape:'html'}" target="_blank" style="{if $action.type=='action' && !empty($action.pageTitle)}margin-left: 25px;{/if}text-decoration:underline;">{$action.url|escape:'html'|truncate:80:"...":true}</a>
					{else}
						{$javascriptVariablesToSet.pageUrlNotDefined}
					{/if}
				{else}
				{* Goal conversion *}
					<img src="{$action.icon}" /> 
					<strong>{$action.goalName|escape:'html'}</strong>
					{if $action.revenue > 0}, {'Live_GoalRevenue'|translate}: <strong>{$action.revenue|money:$javascriptVariablesToSet.idSite}</strong>{/if}
				{/if}
				</li>
				{/if}
			{/foreach}
			</ol>
	</td>
	</tr>
	{/capture}
	
	{if !$javascriptVariablesToSet.filterEcommerce
		|| (isset($visitorHasSomeEcommerceActivity) && $visitorHasSomeEcommerceActivity)}
		{$visitorRow}
	{/if}
{/foreach}
	</tbody>
	</table>
	{/if}
	{if count($arrayDataTable) == $javascriptVariablesToSet.filter_limit}
	{* We set a fake large rows count so that 'Next' paginate link is forced to display
	   This is hard coded because the Visitor Log datatable is not fully loaded in memory, 
	   but needs to fetch only the N rows in the logs
	   *}
	{php}$this->_tpl_vars['javascriptVariablesToSet']['totalRows'] = 100000; {/php}
	{/if}
	{if $properties.show_footer}
		{include file="CoreHome/templates/datatable_footer.tpl"}
	{/if}
	{include file="CoreHome/templates/datatable_js.tpl"}
	<script type="text/javascript" defer="defer">
	$(document).ready(function(){ldelim} 
		var dataTableVisitorLog = dataTables['{$properties.uniqueId}'];
		dataTableVisitorLog.param.maxIdVisit = {$maxIdVisit};
		{literal}
		if(dataTableVisitorLog.param.previous == 1) {
			$('.dataTablePrevious').hide();
			dataTableVisitorLog.param.previous = 0;
		}
		
		// Replace duplicated page views by a NX count instead of using too much vertical space
        $("ol.visitorLog").each(function () {
                var prevelement;
                var prevhtml;
                var counter = 0;
                $(this).find("li").each(function () {
                        counter++;
                        $(this).val(counter);
                        var current = $(this).html();
                        if (current == prevhtml) {
                                var repeat = prevelement.find(".repeat")
                                if (repeat.length) {
                                        repeat.html( (parseInt(repeat.html()) + 1) + "x" );
                                } else {
                                        prevelement.append($("<em title='{/literal}{'Live_PageRefreshed'|translate|escape:'js'}{literal}' class='repeat'>2x</em>"));
                                }
                                $(this).hide();
                        } else {
                                prevhtml = current;
                                prevelement = $(this);
                        }
                });
        });
	});
	{/literal}
	</script>
{/if}

{literal}
<style type="text/css">
hr {
	background:none repeat scroll 0 0 transparent;
	border: 0 none #000;
	border-bottom: 1px solid #ccc;
	color:#eee;
	margin:0 2em 0.5em;
	padding:0 0 0.5em;
}

</style>
{/literal}

</div>
