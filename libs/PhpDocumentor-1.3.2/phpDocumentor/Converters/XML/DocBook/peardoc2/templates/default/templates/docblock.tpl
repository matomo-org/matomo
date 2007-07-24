{if $var}
{assign var="num" value="refsect3"}
{else}
{assign var="num" value="refsect1"}
{/if}
    <{$num} id="{$id}.desc">
     &title.desc;
{if $line_number}
     <simpara>
      Source on line #: {if $class_slink}{$class_slink}{else}{$line_number}{/if}
     </simpara>
{/if}
{if $var}
     <simpara>
      {$sdesc|default:"&notdocumented;"}
     </simpara>
{/if}
{if $desc}
     {$desc}
{else}
{if $var && $sdesc}
{else}
     &notdocumented;
{/if}
{/if}
    </{$num}>
{if $params}
   <{$num} id="{$id}.param">
    &title.param;
    <para>
{section name=params loop=$params}
     <variablelist>
      <varlistentry>
       <term>
        {assign var="temp" value=$params[params].name}
        {if strpos($params[params].type, '|') ||
        strpos($cparams.$temp.cdatatype, '>')}
        <type>{$params[params].type}</type>
        {else}
        {if $params[params].type == 'integer'}
        {assign var="paramtype" value="int"}
        {elseif $params[params].type == 'boolean'}
        {assign var="paramtype" value="bool"}
        {else}
        {assign var="paramtype" value=$params[params].type}
        {/if}
        {if in_array($paramtype, array('bool', 'int', 'float', 'string', 'mixed', 'object', 'resource', 'array', 'res'))}
        &type.{$paramtype};
        {else}
        <type>{$paramtype}</type>
        {/if}
        {/if}
         <parameter>{$params[params].name|replace:"&":"&amp;"}</parameter>
       </term>
       <listitem>
        <para>
         {$params[params].description}
        </para>
       </listitem>
      </varlistentry>     
     </variablelist>
{/section}
    </para>
   </{$num}>
{/if}
{foreach from=$tags item="tag" key="tagname"}
{if $tagname != 'static' && $tagname != 'author' && $tagname != 'version' && $tagname != 'copyright' && $tagname != 'package' && $tagname != 'subpackage' && $tagname != 'example'}
   <{$num} id="{$id}.{$tagname}">
    &title.{$tagname};
    {section name=t loop=$tag}
    <para>
      <emphasis>{$tag[t].keyword}</emphasis> {$tag[t].data}
    </para>
    {/section}
   </{$num}>
{elseif $tagname == 'deprecated'}
   <{$num} id="{$id}.{$tagname}">
    &title.note;
    &note.deprecated;
    {section name=t loop=$tag}
    <para>
      {$tag[t].data}
    </para>
    {/section}
   </{$num}>
{elseif $tagname == 'static'}
{assign var="canstatic" value=true}
{elseif $tagname == 'example'}
   <{$num} id="{$id}.{$tagname}">
   <title>Examples</title>
    {section name=t loop=$tag}
    {$tag[t].data}
    {/section}
   </{$num}>
{elseif $tagname != 'package' && $tagname != 'subpackage'}
   <{$num} id="{$id}.{$tagname}">
    <title>{$tagname}</title>{* <-- need language snippets support for phpDocumentor, will use this instead *}
    {section name=t loop=$tag}
    <para>
      <emphasis>{$tagname}</emphasis> {$tag[t].data}
    </para>
    {/section}
   </{$num}>
{/if}
{/foreach}
{if $canstatic}
   <{$num} id="{$id}.note">
    &title.note;
    &note.canstatic;    
   </{$num}>
{else}
   <{$num} id="{$id}.note">
    &title.note;
    &note.notstatic;
   </{$num}>
{/if}
