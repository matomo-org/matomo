{section name=letter loop=$letters}
	[ <a href="{$indexname}.html#{$letters[letter].letter}">{$letters[letter].letter}</a> ]
{/section}

{section name=index loop=$index}
  <hr />
	<a name="{$index[index].letter}"></a>
	<div>
		<h2>{$index[index].letter}</h2>
		<dl>
			{section name=contents loop=$index[index].index}
				<dt><b>{$index[index].index[contents].name}</b></dt>
				<dd>{$index[index].index[contents].listing}</dd>
			{/section}
		</dl>
	</div>
	<a href="{$indexname}.html#top">top</a><br>
{/section}
