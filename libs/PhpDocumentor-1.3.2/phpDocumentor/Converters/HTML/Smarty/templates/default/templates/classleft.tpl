{foreach key=subpackage item=files from=$classleftindex}
	{if $subpackage != ""}<b>{$subpackage}</b><br>{/if}
	{section name=files loop=$files}
		{if $files[files].link != ''}<a href="{$files[files].link}">{/if}
		{$files[files].title}
		{if $files[files].link != ''}</a>{/if}<br>
	{/section}
{/foreach}
