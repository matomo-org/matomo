<tr class="annotation" data-id="{$annotation.idNote}" data-date="{$annotation.date}">
    <td class="annotation-meta">
        <div class="annotation-star{if $annotation.canEditOrDelete} annotation-star-changeable{/if}" data-starred="{$annotation.starred}"
             {if $annotation.canEditOrDelete}title="{'Annotations_ClickToStarOrUnstar'|translate}"{/if}>
            {if $annotation.starred}
                <img src="themes/default/images/star.png"/>
            {else}
                <img src="themes/default/images/star_empty.png"/>
            {/if}
        </div>
        <div class="annotation-period {if $annotation.canEditOrDelete}annotation-enter-edit-mode{/if}">({$annotation.date})</div>
        {if $annotation.canEditOrDelete}
            <div class="annotation-period-edit" style="display:none">
                <a href="#">{$annotation.date}</a>

                <div class="datepicker" style="display:none"/>
            </div>
        {/if}
    </td>
    <td class="annotation-value">
        <div class="annotation-view-mode">
            <span {if $annotation.canEditOrDelete}title="{'Annotations_ClickToEdit'|translate}"
                  class="annotation-enter-edit-mode"{/if}>{$annotation.note|unescape|escape:'html'}</span>
            {if $annotation.canEditOrDelete}
                <a href="#" class="edit-annotation annotation-enter-edit-mode" title="{'Annotations_ClickToEdit'|translate}">{'General_Edit'|translate}...</a>
            {/if}
        </div>
        {if $annotation.canEditOrDelete}
            <div class="annotation-edit-mode" style="display:none">
                <input class="annotation-edit" type="text" value="{$annotation.note|unescape|escape:'html'}"/>
                <br/>
                <input class="annotation-save submit" type="button" value="{'General_Save'|translate}"/>
                <input class="annotation-cancel submit" type="button" value="{'General_Cancel'|translate}"/>
            </div>
        {/if}
    </td>
    {if isset($annotation.user) && $userLogin != 'anonymous'}
        <td class="annotation-user-cell">
            <span class="annotation-user">{$annotation.user|unescape|escape:'html'}</span><br/>
            {if $annotation.canEditOrDelete}
                <a href="#" class="delete-annotation" style="display:none" title="{'Annotations_ClickToDelete'|translate}">{'General_Delete'|translate}</a>
            {/if}
        </td>
    {/if}
</tr>

