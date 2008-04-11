   <refsect1 id="{$my_id}.vars">
    <title>Class Variables</title>
{section name=var loop=$vars}
    <refsect2 id="{$vars[vars].id}">
    <title>{$vars[var].var_type} {$vars[var].var_name}{if $vars[var].default} = {$vars[var].var_default}{/if}</title>

{section name=v loop=$vars[var].var_overrides}
    <para>
     <emphasis>Overrides {$vars[var].var_overrides[v].link}</emphasis>{if $vars[var].var_overrides[v].sdesc}: {$vars[var].var_overrides[v].sdesc|default:""}{/if}
    </para>
{/section}
{include file="docblock.tpl" var=true desc=$vars[var].desc sdesc=$vars[var].sdesc tags=$vars[var].tags line_number=$line_number id=$vars[var].id}
    </refsect2>
{/section}
   </refsect1>
