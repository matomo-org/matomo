<thead>
   <tr>
       {foreach from=$dataTableColumns item=column name=head}
           <th class="sortable {if $smarty.foreach.head.first}first{elseif $smarty.foreach.head.last}last{/if}" id="{$column}">
               {if !empty($columnDocumentation[$column])}
                   <div class="columnDocumentation">
                       <div class="columnDocumentationTitle">
                           {$columnTranslations[$column]|escape:'html'|replace:"&amp;nbsp;":"&nbsp;"}
                       </div>
                       {$columnDocumentation[$column]|escape:'html'}
                   </div>
               {/if}
               <div id="thDIV">{$columnTranslations[$column]|escape:'html'|replace:"&amp;nbsp;":"&nbsp;"}</div>
           </th>
       {/foreach}
   </tr>
</thead>