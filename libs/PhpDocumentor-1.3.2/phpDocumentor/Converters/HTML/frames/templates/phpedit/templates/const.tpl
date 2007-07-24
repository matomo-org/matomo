
<h2 class="tab">Class Constants</h2>
<!-- ============ VARIABLE DETAIL =========== -->
<strong>Summary:</strong><br />
{section name=consts loop=$consts}
<div class="const-title">
    <a href="#{$consts[consts].const_dest}" title="details" class="property"><strong>{$consts[consts].const_name}</strong></a>
</div>
{/section}
<hr />
{section name=consts loop=$consts}
<a name="{$consts[consts].const_dest}" id="{$consts[consts].const_dest}"><!-- --></A>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
<img src="{$subdir}media/images/Constant.gif" border="0" /> <strong class="property">{$consts[consts].const_name} = {$consts[consts].const_value|replace:"\n":"<br />"}</strong> (line <span class="linenumber">{if $consts[consts].slink}{$consts[consts].slink}{else}{$consts[consts].line_number}{/if}</span>)
 </h4>
{include file="docblock.tpl" sdesc=$consts[consts].sdesc desc=$consts[consts].desc tags=$consts[consts].tags}
</div>
{/section}
<script type="text/javascript">tp1.addTabPage( document.getElementById( "constantsTabpage" ) );</script>

