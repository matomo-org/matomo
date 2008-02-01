Test

<table border=1 cellpadding=5>

<tr>
	<th>Name</th>
	<th>Description</th>
	<th>Author</th>
	<th>Version</th>
	<th>Action</th>
</tr>
{foreach from=$pluginsName key=name item=plugin}

<tr>
	<td><b>{$name}</b></td>
	<td>{$plugin.info.description}&nbsp;</td>
	<td><a href="{$plugin.info.homepage}">{$plugin.info.author}</a></td>
	<td>{$plugin.info.version}</td>
	<td>{if $plugin.activated}<a href=?module=PluginsAdmin&action=deactivate&pluginName={$name}>Deactivate</a>
	{else}<a href=?module=PluginsAdmin&action=activate&pluginName={$name}>Activate</a>{/if}</td>
</tr>
{/foreach}

</table>