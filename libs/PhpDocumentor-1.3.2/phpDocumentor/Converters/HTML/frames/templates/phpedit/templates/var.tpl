
<h2 class="tab">Class Variables</h2>
<!-- ============ VARIABLE DETAIL =========== -->
<strong>Summary:</strong><br />
{section name=vars loop=$vars}
{if $vars[vars].static}
<div class="var-title">
    static <span class="var-type">{$vars[vars].var_type}</span>
    <a href="#{$vars[vars].var_dest}" title="details" class="property"><strong>{$vars[vars].var_name}</strong></a>
</div>
{/if}
{/section}
{section name=vars loop=$vars}
{if !$vars[vars].static}
<div class="var-title">
    <span class="var-type">{$vars[vars].var_type}</span>
    <a href="#{$vars[vars].var_dest}" title="details" class="property"><strong>{$vars[vars].var_name}</strong></a>
</div>
{/if}
{/section}
<hr />
{section name=vars loop=$vars}
{if $vars[vars].static}
<a name="{$vars[vars].var_dest}" id="{$vars[vars].var_dest}"><!-- --></A>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
<img src="{$subdir}media/images/PublicProperty.gif" border="0" /> <strong class="property">static {$vars[vars].var_name}{if $vars[vars].var_default} = {$vars[vars].var_default|replace:"\n":"<br />"}{/if}</strong> (line <span class="linenumber">{if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}</span>)
 </h4>
<h4>Data type : {$vars[vars].var_type}</h4>
{if $vars[vars].var_overrides}<p><strong>Overrides:</strong> {$vars[vars].var_overrides}<br></p>{/if}
{include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}
</div>
{/if}
{/section}
{section name=vars loop=$vars}
{if !$vars[vars].static}
<a name="{$vars[vars].var_dest}" id="{$vars[vars].var_dest}"><!-- --></A>
<div style="background='{cycle values="#ffffff,#eeeeee"}'">
<h4>
<img src="{$subdir}media/images/PublicProperty.gif" border="0" /> <strong class="property">{$vars[vars].var_name}{if $vars[vars].var_default} = {$vars[vars].var_default|replace:"\n":"<br />"}{/if}</strong> (line <span class="linenumber">{if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}</span>)
 </h4>
<h4>Data type : {$vars[vars].var_type}</h4>
{if $vars[vars].var_overrides}<p><strong>Overrides:</strong> {$vars[vars].var_overrides}<br></p>{/if}
{include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}
</div>
{/if}
{/section}
<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage1" ) );</script>

