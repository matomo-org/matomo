{foreach from=$titleAndUrls item=plot}
	<h2>{$plot.0|escape}</h2>
	<a href='{$plot.1}'><img border=0 src="{$plot.1}"></a>
{/foreach}