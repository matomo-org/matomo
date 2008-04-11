<refentry id="{$id}">
   <refnamediv>
    <refname>{if $function_call.constructor}constructor {/if}<function>{$class}::{$function_name}</function></refname>
    <refpurpose>{$sdesc|default:$function_name}</refpurpose>
   </refnamediv>
   <refsynopsisdiv>
    <funcsynopsis>
     <funcsynopsisinfo>
      require_once &apos;{$source_location}&apos;;
     </funcsynopsisinfo>
     <funcprototype>
      <funcdef>{$function_return}{if $function_call.returnsref}&amp;{/if}
      {if $function_call.constructor}constructor {/if}<function>{$class}::{$function_name}</function></funcdef>
{if count($function_call.params)}
{section name=params loop=$function_call.params}
      <paramdef>{if @strpos('>',$function_call.params[params].type)}<replaceable>{/if}{$function_call.params[params].type}{if @strpos('>',$function_call.params[params].type)}</replaceable>{/if} <parameter>{if $function_call.params[params].hasdefault} <optional>{/if}{$function_call.params[params].name|replace:"&":"&amp;"}{if $function_call.params[params].hasdefault} = {$function_call.params[params].default}</optional>{/if}</parameter></paramdef>
{/section}
{else}
<paramdef></paramdef>
{/if}
     </funcprototype>
    </funcsynopsis>
    </refsynopsisdiv>
{include file="docblock.tpl" cparams=$params params=$function_call.params desc=$desc tags=$tags}
</refentry>
<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
sgml-parent-document:nil
sgml-default-dtd-file:"../../../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->  

