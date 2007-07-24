{section name=consts loop=$consts}
{if $show == 'summary'}
	var {$consts[consts].const_name}, {$consts[consts].sdesc}<br>
{else}
	<a name="{$consts[consts].const_dest}"></a>
	<p></p>
	<h4>{$consts[consts].const_name} = <span class="value">{$consts[consts].const_value|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span></h4>
	<p>[line {if $consts[consts].slink}{$consts[consts].slink}{else}{$consts[consts].line_number}{/if}]</p>
  {include file="docblock.tpl" sdesc=$consts[consts].sdesc desc=$consts[consts].desc tags=$consts[consts].tags}

  <br />
	<div class="top">[ <a href="#top">Top</a> ]</div><br />
{/if}
{/section}
