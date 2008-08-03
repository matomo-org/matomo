{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{include file="CoreAdminHome/templates/menu.tpl"}
{literal}
<style>
.widefat {
	border-width: 1px;
	border-style: solid;
	border-collapse: collapse;
	width: 100%;
	clear: both;
	margin: 0;
}

.widefat a {
	text-decoration: none;
}

.widefat abbr {
	white-space: nowrap;
}

.widefat td, .widefat th {
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #ccc;
	font-size: 11px;
	vertical-align: text-top;
}

.widefat td {
	padding: 7px 15px 9px 10px;
	vertical-align: top;
}

.widefat th {
	padding: 9px 15px 6px 10px;
	text-align: left;
	line-height: 1.3em;
}

.widefat th input {
	margin: 0 0 0 8px;
	padding: 0;
}

.widefat .check-column {
	text-align: right;
	width: 1.5em;
	padding: 0;

}
.widefat {
	border-color: #ccc;
}

.widefat tbody th.check-column {
	padding: 8px 0 22px;
}
.widefat .num {
	text-align: center;
}
.widefat td, .widefat th, div#available-widgets-filter, ul#widget-list li.widget-list-item, .commentlist li {
	border-bottom-color: #ccc;
}

.widefat thead, .thead {
	background-color: #464646;
	color: #d7d7d7;
}

.widefat td.action-links, .widefat th.action-links {
	text-align: right;
}

.widefat .name {
	font-weight: bold;
}

.widefat a {
	color:#2583AD;
}

.widefat  .active {
	background-color: #ECF9DD;
}


</style>
{/literal}

<div style="max-width:980px;">

<h2>Plugins Management</h2>
<p>Plugins extend and expand the functionality of Piwik. Once a plugin is installed, you may activate it or deactivate it here.</p>
<table class="widefat">
	<thead>
	<tr>
		<th>Plugin</th>
		<th class="num">Version</th>
		<th>Description</th>
		<th class="status">Status</th>
		<th class="action-links">Action</th>
	</tr>
	</thead>
	<tbody id="plugins">
	{foreach from=$pluginsName key=name item=plugin}
	<tr class={if $plugin.activated}"active"{else}class="deactivate"{/if}>
		<td class="name">
			{if isset($plugin.info.homepage)}<a title="Plugin Homepage" href="{$plugin.info.homepage}">{/if}
			{$name}
			{if isset($plugin.info.homepage)}</a>{/if}
		</td>
		<td class="vers">{$plugin.info.version}</td>
		<td class="desc">
			{$plugin.info.description}
			&nbsp;<cite>By 
				{if isset($plugin.info.author_homepage)}<a title="Author Homepage" href="{$plugin.info.author_homepage}">{/if}
				{$plugin.info.author}{if isset($plugin.info.author_homepage)}</a>{/if}.</cite>
		</td>
		<td class="status">
			{if $plugin.alwaysActivated}<span title="{'CorePluginsAdmin_ActivatedHelp'|translate}" class="active">Active</span>
			{elseif $plugin.activated}Active
			{else}Inactive{/if}
		</td>
		
		<td class="togl action-links" {if $plugin.alwaysActivated}title="{'CorePluginsAdmin_ActivatedHelp'|translate}"{/if}>
			{if $plugin.alwaysActivated} <center>-</center>  
			{elseif $plugin.activated}<a href=?module=CorePluginsAdmin&action=deactivate&pluginName={$name}>{'CorePluginsAdmin_Deactivate'|translate}</a>
			{else}<a href=?module=CorePluginsAdmin&action=activate&pluginName={$name}>{'CorePluginsAdmin_Activate'|translate}</a>{/if}
		</td> 
	</tr>
{/foreach}

</tbody>
</table>

</div>