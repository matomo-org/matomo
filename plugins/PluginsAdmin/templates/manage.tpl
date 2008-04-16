<link rel="stylesheet" href="themes/default/common-admin.css">

<h2>Plugins</h2>

<table id="plugins">

<thead>
	<th width="150px">Name</th>
	<th width="400px">Description</th>
	<th>Author</th>
	<th>Version</th>
	<th>Action</th>
</thead>

<tbody>
	{foreach from=$pluginsName key=name item=plugin}

{if $plugin.activated}<tr class="activate">{else}<tr class="deactivate">{/if}
	<td><b>{$name}</b></td>
	<td>{$plugin.info.description}&nbsp;</td>
	<td class="center"><a href="{$plugin.info.homepage}">{$plugin.info.author}</a></td>
	<td>{$plugin.info.version}</td>
	<td class="switch">{if $plugin.alwaysActivated}<span title="{'PluginsAdmin_ActivatedHelp'|translate}">{'PluginsAdmin_Activated'|translate}</span>{elseif $plugin.activated}<a href=?module=PluginsAdmin&action=deactivate&pluginName={$name}>{'PluginsAdmin_Deactivate'|translate}</a>
{else}<a href=?module=PluginsAdmin&action=activate&pluginName={$name}>{'PluginsAdmin_Activate'|translate}</a>{/if}</td>
	</tr>
{/foreach}

</tbody>
</table>
