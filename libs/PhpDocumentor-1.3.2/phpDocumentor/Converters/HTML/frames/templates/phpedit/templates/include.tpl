<!-- ============ Includes DETAIL =========== -->

<h2 class="tab">Include/Require Statements</h2>
<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage1" ) );</script>


{section name=includes loop=$includes}
<a name="{$includes[includes].include_file}"><!-- --></a>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
  <img src="{$subdir}media/images/file.png" border="0" /> <strong class="Property">{$includes[includes].include_value}</strong> (line <span class="linenumber">{if $includes[includes].slink}{$includes[includes].slink}{else}{$includes[includes].line_number}{/if}</span>)
 </h4> 
<h4>{$includes[includes].include_name} : {$includes[includes].include_value}</h4>
{include file="docblock.tpl" sdesc=$includes[includes].sdesc desc=$includes[includes].desc tags=$includes[includes].tags}
</div>
{/section}