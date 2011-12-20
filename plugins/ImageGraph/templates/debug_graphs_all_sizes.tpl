{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}
<div class="top_controls_inner"></div>
<div>
	<h2>{"ImageGraph_ImageGraph"|translate} ::: {$siteName}</h2>
	<div class="top_controls_inner">
	    {include file="CoreHome/templates/period_select.tpl"}
	</div>
	
	<div class="entityContainer" style="width:100%;">
		<div class="entityAddContainer">
			<table class="dataTable entityTable">
				<thead>
				</thead>
				<tbody>
					<tr class="first">
						<th style="white-space:normal;">Category</th>
						<th style="white-space:normal;">Name</th>
						
						{foreach from=$graphTypes item=type}
							<th style="white-space:normal;">{$type}</th>
						{/foreach}
					</tr>
					{foreach from=$availableReports item=report name=i}
						{if isset($report.imageGraphUrl)}
							<tr>
								<td>{$report.category|escape:"html"}</td>
								<td>{$report.name|escape:"html"}</td>
								{foreach from=$graphTypes item=type}
									<td>
									<h2>Graph {$type} for all supported sizes</h2>
									{foreach from=$graphSizes item=sizes}
										<p>{$sizes.0} x {$sizes.1} {if !empty($sizes.2)} (scaled down to {$sizes.3} x {$sizes.4}){/if}</p>
										<img
												src="{$report.imageGraphUrl}&graphType={$type}&width={$sizes.0}&height={$sizes.1}{if !empty($sizes.2)}&fontSize={$sizes.2}{/if}"
											{if !empty($sizes.3)}width={$sizes.3}{/if}
											{if !empty($sizes.4)}height={$sizes.4}{/if}
												/>
									{/foreach}
									</td>
								{/foreach}
							</tr>
						{/if}
					{/foreach}
				</tbody>
			</table>
		</div>
		<a id="bottom"></a>
	</div>
</div>