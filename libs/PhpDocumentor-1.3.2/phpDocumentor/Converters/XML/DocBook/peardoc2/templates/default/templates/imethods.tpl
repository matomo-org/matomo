      <para>
{section name=classes loop=$imethods}
       <table>
        <title>Inherited from {$imethods[classes].parent_class}</title>
        <tgroup cols="2">
         <thead>
          <row>
           <entry>Method Name</entry>
           <entry>Summary</entry>
          </row>
         </thead>
         <tbody>
{section name=m loop=$imethods[classes].imethods}
          <row>
           <entry>{if $imethods[classes].imethods[m].constructor} Constructor{/if} {$imethods[classes].imethods[m].link}</entry>
           <entry>{$imethods[classes].imethods[m].sdesc|default:"&notdocumented;"}</entry>
          </row>
{/section}
         </tbody>
        </tgroup>
       </table>
{/section}
      </para>

