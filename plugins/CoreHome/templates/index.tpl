{assign var=showSitesSelection value=true}

{include file="CoreHome/templates/header.tpl"}


{if isset($menu) && $menu}{include file="CoreHome/templates/menu.tpl"}{/if}

{include file="CoreHome/templates/index_content.tpl"}

{include file="CoreHome/templates/footer.tpl"}

