{foreach key=subpackage item=files from=$fileleftindex}
	{if $subpackage != ""}subpackage <b>{$subpackage}</b><br>{/if}
	{section name=files loop=$files}
		{if $files[files].link != ''}<a href="{ldelim}$subdir{rdelim}{$files[files].link}">{/if}
		{$files[files].title}
		{if $files[files].link != ''}</a>{/if}<br>
	{/section}
{/foreach}
