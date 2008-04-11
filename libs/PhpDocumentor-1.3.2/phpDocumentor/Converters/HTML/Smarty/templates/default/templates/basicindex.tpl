{section name=letter loop=$letters}
	<a href="{$indexname}.html#{$letters[letter].letter}">{$letters[letter].letter}</a>
{/section}

{section name=index loop=$index}
	<a name="{$index[index].letter}"></a>
	<a href="{$indexname}.html#top">top</a><br>
	<div>
		<h2>{$index[index].letter}</h2>
		<dl class="lettercontents">
			{section name=contents loop=$index[index].index}
				<dt>{$index[index].index[contents].name}</dt>
				<dd>{$index[index].index[contents].listing}</dd>
			{/section}
		</dl>
	</div>
{/section}
