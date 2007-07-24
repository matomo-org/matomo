<!-- ============ FUNCTION DETAIL =========== -->

<h2 class="tab">Functions</h2>

<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage4" ) );</script>

{section name=func loop=$functions}
<a name="{$functions[func].function_dest}" id="{$functions[func].function_dest}"><!-- --></a>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
  <img src="{$subdir}media/images/PublicMethod.gif" border="0" /> <strong class="method">{$functions[func].function_name}</strong> (line <span class="linenumber">{if $functions[func].slink}{$functions[func].slink}{else}{$functions[func].line_number}{/if}</span>)
 </h4> 
<h4><i>{$functions[func].function_return}</i> <strong>{if $functions[func].ifunction_call.returnsref}&amp;{/if}{$functions[func].function_name}(
{if count($functions[func].ifunction_call.params)}
{section name=params loop=$functions[func].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}{if $functions[func].ifunction_call.params[params].hasdefault}[{/if}{$functions[func].ifunction_call.params[params].type} {$functions[func].ifunction_call.params[params].name}{if $functions[func].ifunction_call.params[params].hasdefault} = {$functions[func].ifunction_call.params[params].default|escape:"html"}]{/if}
{/section}
{/if})</strong></h4>
{if $functions[func].function_conflicts.conflict_type}
<div align="left"><span class="warning">Warning:</span> Conflicts with functions:<br /> 
{section name=me loop=$functions[func].function_conflicts.conflicts}
{$functions[func].function_conflicts.conflicts[me]}<br />
{/section}
</div>
{/if}

{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc tags=$functions[func].tags params=$functions[func].params function=true}
</div>
{/section}
