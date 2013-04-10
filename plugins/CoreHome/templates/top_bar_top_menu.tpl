<div id="topLeftBar">
    {foreach from=$topMenu key=label item=menu name=topMenu}

        {if isset($menu._html)}
            {$menu._html}
        {elseif $menu._url.module == $currentModule && (empty($menu._url.action) || $menu._url.action == $currentAction)}
            <span class="topBarElem"><b>{$label|translate}</b></span>
            |
        {else}
            <span class="topBarElem" {if isset($menu._tooltip)}title="{$menu._tooltip}"{/if}><a id="topmenu-{$menu._url.module|strtolower}"
                                                                                                href="index.php{$menu._url|@urlRewriteWithParameters}">{$label|translate}</a></span>
            |
        {/if}

    {/foreach}
</div>