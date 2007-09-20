{assign var=ok value="<img src='themes/default/images/ok.png' />"}
{assign var=error value="<img src='themes/default/images/error.png' />"}
{assign var=warning value="<img src='themes/default/images/warning.png' />"}

<h1>System check</h1>


<table class="infosServer">
	<tr>
		<td class="label">PHP version &gt; {$infos.phpVersion_minimum}</td>
		<td>{if $infos.phpVersion_ok}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td class="label">Pdo_Mysql extension</td>
		<td>{if $infos.pdo_mysql_ok}{$ok}{else}{$error}{/if}</td>
	</tr>  
	<tr>
		<td class="label">PHP-XML extension <br> (utf8_decode function)</td>
	    <td>{if $infos.phpXml_ok}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td valign="top">
			Directories with write access
		</td>
		<td>
			{foreach from=$infos.directories key=dir item=bool}
				{if $bool}{$ok}{else}
				<span style="color:red">{$error}</span>{/if} 
				{$dir}
				<br>				
			{/foreach}
		</td>
	</tr>
</table>

{if $problemWithSomeDirectories}
	<br>
	<div class="error">
		To fix this error on your Linux system, try typing in the following command(s):
	{foreach from=$infos.directories key=dir item=bool}
		<ul>{if !$bool}
			<li>chmod a+w {$basePath}{$dir}</li>
		{/if}
		</ul>
	{/foreach}
	</div>
	<br>
{/if}
<h1>Optional</h1>
<table class="infos">
	<tr>
		<td class="label">Memory limit</td>
		<td>
			{$infos.memoryCurrent}
			{if $infos.memory_ok}{$ok}{else}{$warning}{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">set_time_limit() allowed</td>
		<td>{if $infos.setTimeLimit_ok}{$ok}{else}{$warning}{/if}</td>
	</tr>
	<tr>
		<td class="label">mail() allowed</td>
		<td>{if $infos.mail_ok}{$ok}{else}{$warning}{/if}</td>
	</tr>
</table>
<p><small>
Legend:
<br>
{$ok} Ok<br>
{$error} Error to be fixed<br>
{$warning} Warning: Piwik will work normally but some features may be missing<br>
</small></p>
