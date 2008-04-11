<refentry id="{$id}">
 <refnamediv>
 <refname>Class Summary {$class_name}</refname>
 <refpurpose>{$sdesc}</refpurpose>
 </refnamediv>
<refsect1>
 <title>{$sdesc}</title>
 {$desc|default:"&notdocumented;"}
</refsect1>
<refsect1>
<title>Class Trees for {$class_name}</title>
 <para>
  {section name=tree loop=$class_tree}
  {section name=mine loop=$class_tree[tree]} {/section}<itemizedlist>
  {section name=mine loop=$class_tree[tree]} {/section} <listitem><para>
  {section name=mine loop=$class_tree[tree]} {/section} {$class_tree[tree]}
  {/section}
  {section name=tree loop=$class_tree}
  {section name=mine loop=$class_tree[tree]} {/section}</para></listitem>
  </itemizedlist>
  {/section}
 </para>
{if $children}
 <para>
  <table>
   <title>Classes that extend {$class_name}</title>
   <tgroup cols="2">
    <thead>
     <row>
      <entry>Class</entry>
      <entry>Summary</entry>
     </row>
    </thead>
    <tbody>
{section name=kids loop=$children}
     <row>
   <entry>{$children[kids].link}</entry>
   <entry>{$children[kids].sdesc}</entry>
     </row>
{/section}
    </tbody>
   </tgroup>
  </table>
 </para>
{/if}
{if $imethods}
 <para>
  {$class_name} Inherited Methods
 </para>
{include file="imethods.tpl" ivars=$ivars}
{/if}
</refsect1>
</refentry>
