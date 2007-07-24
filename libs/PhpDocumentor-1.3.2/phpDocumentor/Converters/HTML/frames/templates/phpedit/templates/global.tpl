<!-- ============ GLOBAL DETAIL =========== -->

<h2 class="tab">Global Variables</h2>

<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage2" ) );</script>

{section name=glob loop=$globals}
<a name="{$globals[glob].global_link}" id="{$globals[glob].global_link}"><!-- --></a>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
  <img src="{$subdir}media/images/Constants.gif" border="0" /> <strong class="Property">{$globals[glob].global_name}</strong> (line <span class="linenumber">{if $globals[glob].slink}{$globals[glob].slink}{else}{$globals[glob].line_number}{/if}</span>)
 </h4> 
<h4><i>{$globals[glob].global_type}</i> {$globals[glob].global_name} : {$globals[glob].global_value|replace:"\n":"<br />"}</h4>
{if $globals[glob].global_conflicts.conflict_type}
	<p><span class="warning">Warning:</span> Conflicts with global variables:<br />
	{section name=me loop=$globals[glob].global_conflicts.conflicts}
		{$globals[glob].global_conflicts.conflicts[me]}<br />
	{/section}
	</p>
{/if}

{include file="docblock.tpl" sdesc=$globals[glob].sdesc desc=$globals[glob].desc tags=$globals[glob].tags}
</div>
{/section}