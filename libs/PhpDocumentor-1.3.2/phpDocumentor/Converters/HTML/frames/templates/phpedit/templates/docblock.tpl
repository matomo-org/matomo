<!-- ========== Info from phpDoc block ========= -->
{if $sdesc}
<h5>{$sdesc}</h5>
{/if}
{if $desc}
<div class="desc">{$desc}</div>
{/if}
{if $function}
	{if $params}
	<h4>Parameters</h4>
	<ul>
	{section name=params loop=$params}
		<li><strong>{$params[params].datatype} {$params[params].var}</strong>: {$params[params].data}</li>
	{/section}
	</ul>
	{/if}
	
	<h4>Info</h4>
	<ul>
	{section name=tags loop=$tags}
		<li><strong>{$tags[tags].keyword}</strong> - {$tags[tags].data}</li>
	{/section}
	</ul>
{else}
<ul>
	{section name=tags loop=$tags}
	<li><strong>{$tags[tags].keyword}:</strong> - {$tags[tags].data}</li>
	{/section}
</ul>
{/if}
