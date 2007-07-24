{foreach key=subpackage item=files from=$fileleftindex}
	{if $subpackage != ""}subpackage <b>{$subpackage}</b><br>{/if}
  <div class="package">
	{section name=files loop=$files}
		{if $files[files].link != ''}<a href="{$files[files].link}">{/if}
		{$files[files].title}
		{if $files[files].link != ''}</a>{/if}<br>
	{/section}
  </div><br />
{/foreach}
