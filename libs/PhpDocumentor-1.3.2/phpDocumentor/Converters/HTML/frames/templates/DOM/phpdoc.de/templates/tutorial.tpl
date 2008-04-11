{include file="header.tpl" title=$title top3=true}

{if $nav}
	{include file="tutorial_nav.tpl" prev=$prev next=$next up=$up prevtitle=$prevtitle nexttitle=$nexttitle uptitle=$uptitle}
{/if}

{$contents}

{if $nav}
	{include file="tutorial_nav.tpl" prev=$prev next=$next up=$up prevtitle=$prevtitle nexttitle=$nexttitle uptitle=$uptitle}
{/if}

{include file="footer.tpl" top3=true}