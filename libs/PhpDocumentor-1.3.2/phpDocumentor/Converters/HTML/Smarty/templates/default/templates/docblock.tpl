{if $sdesc != ''}
<p align="center"><strong>{$sdesc|default:''}
</strong></p>
{/if}
{if $desc != ''}{$desc|default:''}{/if}
{if count($tags)}
<h4>Tags:</h4>
<ul>
{section name=tag loop=$tags}
	<li><b>{$tags[tag].keyword}</b> - {$tags[tag].data}</li>
{/section}
</ul>
{/if}
