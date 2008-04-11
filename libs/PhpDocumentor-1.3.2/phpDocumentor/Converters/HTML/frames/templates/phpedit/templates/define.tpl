<!-- ============ CONSTANT DETAIL =========== -->

<A NAME='constant_detail'></A>
<h2 class="tab">Constants</h2>

<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage3" ) );</script>

{section name=def loop=$defines}
<a name="{$defines[def].define_link}"><!-- --></a>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
  <img src="{$subdir}media/images/Constant.gif" border="0" /> <strong class="property">{$defines[def].define_name}</strong> (line <span class="linenumber">{if $defines[def].slink}{$defines[def].slink}{else}{$defines[def].line_number}{/if}</span>)
 </h4> 
<h4>{$defines[def].define_name} : {$defines[def].define_value|replace:"\n":"<br />"}</h4>
{if $defines[def].define_conflicts.conflict_type}
	<p><span class="warning">Warning:</span> Conflicts with constants:<br />
	{section name=me loop=$defines[def].define_conflicts.conflicts}
		{$defines[def].define_conflicts.conflicts[me]}<br />
	{/section}
	</p>
{/if}
{include file="docblock.tpl" sdesc=$defines[def].sdesc desc=$defines[def].desc tags=$defines[def].tags}
</div>
{/section}