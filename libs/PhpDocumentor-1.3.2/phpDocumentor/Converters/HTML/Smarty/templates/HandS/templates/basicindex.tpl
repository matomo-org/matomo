{section name=letter loop=$letters}
	[ <a href="{$indexname}.html#{$letters[letter].letter}">{$letters[letter].letter}</a> ]
{/section}
<br /><br />
{section name=index loop=$index}
	<a name="{$index[index].letter}"></a>
	<div class="index-letter-section">
		<div style="float: left" class="index-letter-title">{$index[index].letter}</div>
		<div style="float: right"><a href="#top">[Top]</a></div>
		<div style="clear: both"></div>
	</div>
	<div>
		<h2>{$index[index].letter}</h2>
		<dl>
			{section name=contents loop=$index[index].index}
				<dt><b>{$index[index].index[contents].name}</b></dt>
				<dd>{$index[index].index[contents].listing}</dd>
			{/section}
		</dl>
	</div>
{/section}
