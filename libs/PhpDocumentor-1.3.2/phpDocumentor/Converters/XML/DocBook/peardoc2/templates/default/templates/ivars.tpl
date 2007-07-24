      <para>
{section name=classes loop=$ivars}
       <table>
        <title>Inherited from {$ivars[classes].parent_class}</title>
        <tgroup cols="2">
         <thead>
         <row>
          <entry>Variable Name</entry>
          <entry>Summary</entry>
          <entry>Default Value</entry>
          </row>
         </thead>
         <tbody>
{section name=m loop=$ivars[classes].ivars}
         <row>
          <entry>{if $ivars[classes].ivars[m].constructor} Constructor{/if} {$ivars[classes].ivars[m].link}</entry>
          <entry>{$ivars[classes].ivars[m].sdesc|default:"&notdocumented;"}</entry>
          <entry>{$ivars[classes].ivars[m].default|default:"&null;"}</entry>
         </row>
{/section}
        </tbody>
       </tgroup>
      </table>
{/section}
      </para>

