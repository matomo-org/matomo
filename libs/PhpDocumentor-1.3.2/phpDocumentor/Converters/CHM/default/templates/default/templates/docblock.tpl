<!-- ========== Info from phpDoc block ========= -->
{if $function}
	{if $params}
	<p class="label"><b>Parameters</b></p>
	{section name=params loop=$params}
		<p class=dt><i>{$params[params].var}</i></p>
		<p class=indent>{$params[params].data}</p>
	{/section}
	{/if}
{/if}
{section name=tags loop=$tags}
{if $tags[tags].keyword == 'return'}
	<p class="label"><b>Returns</b></p>
		<p class=indent>{$tags[tags].data}</p>
{/if}
{/section}
{if $sdesc || $desc}
<p class="label"><b>Remarks</b></p>
{/if}
{if $sdesc}
<p>{$sdesc}</p>
{/if}
{if $desc}
<p>{$desc}</p>
{/if}
{section name=tags loop=$tags}
{if $tags[tags].keyword != 'return'}
	<p class="label"><b>{$tags[tags].keyword}</b></p>
		<p class=indent>{$tags[tags].data}</p>
{/if}
{/section}