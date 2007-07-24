{if count($globals) > 0}
{section name=glob loop=$globals}
{if $show == 'summary'}
global variable <a href="{$globals[glob].id}">{$globals[glob].global_name}</a> = {$globals[glob].global_value}, {$globals[glob].sdesc}<br>
{else}
  <hr />
	<a name="{$globals[glob].global_link}"></a>
	<h4><i>{$globals[glob].global_type}</i> {$globals[glob].global_name} <span class="smalllinenumber">[line {if $globals[glob].slink}{$globals[glob].slink}{else}{$globals[glob].line_number}{/if}]</span></h4>
	<div class="tags">
  {if $globals[glob].sdesc != ""}
	{include file="docblock.tpl" sdesc=$globals[glob].sdesc desc=$globals[glob].desc tags=$globals[glob].tags}
  {/if}

  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><b>Default value:</b>&nbsp;&nbsp;</td>
      <td>{$globals[glob].global_value|replace:" ":"&nbsp;"|replace:"\n":"<br />\n"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</td>
    </tr>
	{if $globals[glob].global_conflicts.conflict_type}
	<tr>
	  <td><b>Conflicts with globals:</b>&nbsp;&nbsp;</td>
	  <td>
	{section name=me loop=$globals[glob].global_conflicts.conflicts}
	{$globals[glob].global_conflicts.conflicts[me]}<br />
	{/section}
	  </td>
	</tr>
	{/if}
{* original    {if $globals[glob].global_conflicts != ""
    <tr>
      <td><b>Conflicts:</b>&nbsp;&nbsp;</td>
      <td>{$globals[glob].global_conflicts</td>
    </tr>
    {/if *}
  </table>
	</div><br /><br />
	<div class="top">[ <a href="#top">Top</a> ]</div><br /><br />
{/if}
{/section}
{/if}