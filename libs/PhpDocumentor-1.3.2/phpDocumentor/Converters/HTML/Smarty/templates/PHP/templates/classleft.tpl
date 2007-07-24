{foreach key=subpackage item=files from=$classleftindex}
  <div class="package">
	{if $subpackage != ""}{$subpackage}<br />{/if}
	{section name=files loop=$files}
    {if $subpackage != ""}&nbsp;&nbsp;{/if}
		{if $files[files].link != ''}<a href="{$files[files].link}">{/if}{$files[files].title}{if $files[files].link != ''}</a>{/if}<br />
	{/section}
  </div>
{/foreach}
