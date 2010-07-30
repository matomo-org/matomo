{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<div style="max-width:980px;">

<h2>{'CorePluginsAdmin_PluginsManagement'|translate}</h2>
<p>{'CorePluginsAdmin_MainDescription'|translate}</p>
<table class="adminTable">
	<thead>
	<tr>
		<th>{'CorePluginsAdmin_Plugin'|translate}</th>
		<th class="num">{'CorePluginsAdmin_Version'|translate}</th>
		<th>{'General_Description'|translate}</th>
		<th class="status">{'CorePluginsAdmin_Status'|translate}</th>
		<th class="action-links">{'CorePluginsAdmin_Action'|translate}</th>
	</tr>
	</thead>
	<tbody id="plugins">
	{foreach from=$pluginsName key=name item=plugin}
	{if isset($plugin.alwaysActivated) && !$plugin.alwaysActivated}
		<tr class={if $plugin.activated}"active"{else}"deactivate"{/if}>
			<td class="name">
				{if isset($plugin.info.homepage)}<a title="{'CorePluginsAdmin_PluginHomepage'|translate}" href="{$plugin.info.homepage}">{/if}
				{$name}
				{if isset($plugin.info.homepage)}</a>{/if}
			</td>
			<td class="vers">{$plugin.info.version}</td>
			<td class="desc">
				{$plugin.info.description|nl2br}
				&nbsp;<cite>By 
					{if isset($plugin.info.author_homepage)}<a title="Author Homepage" href="misc/redirectToUrl.php?url={$plugin.info.author_homepage}">{/if}
					{$plugin.info.author}{if isset($plugin.info.author_homepage)}</a>{/if}.</cite>
			</td>
			<td class="status">
				{if $plugin.alwaysActivated}<span title="{'CorePluginsAdmin_ActivatedHelp'|translate}" class="active">{'CorePluginsAdmin_Active'|translate}</span>
				{elseif $plugin.activated}{'CorePluginsAdmin_Active'|translate}
				{else}{'CorePluginsAdmin_Inactive'|translate}{/if}
			</td>
			
			<td class="togl action-links" {if $plugin.alwaysActivated}title="{'CorePluginsAdmin_ActivatedHelp'|translate}"{/if}>
				{if $plugin.alwaysActivated} <center>-</center>  
				{elseif $plugin.activated}<a href='index.php?module=CorePluginsAdmin&action=deactivate&pluginName={$name}&token_auth={$token_auth}'>{'CorePluginsAdmin_Deactivate'|translate}</a>
				{else}<a href='index.php?module=CorePluginsAdmin&action=activate&pluginName={$name}&token_auth={$token_auth}'>{'CorePluginsAdmin_Activate'|translate}</a>{/if}
			</td> 
		</tr>
	{/if}
{/foreach}
</tbody>
</table>

</div>
{include file="CoreAdminHome/templates/footer.tpl"}
