{assign var=ok value="<img src='themes/default/images/ok.png' />"}
{assign var=error value="<img src='themes/default/images/error.png' />"}
{assign var=warning value="<img src='themes/default/images/warning.png' />"}

<h1>{'Installation_SystemCheck'|translate}</h1>


<table class="infosServer">
	<tr>
		<td class="label">{'Installation_SystemCheckPhp'|translate} &gt; {$infos.phpVersion_minimum}</td>
		<td>{if $infos.phpVersion_ok}{$ok}{else}{$error}{/if}</td>
	</tr><tr>
		<td class="label">{'Installation_SystemCheckPdo'|translate}</td>
		<td>{if $infos.pdo_ok}{$ok}
		{else}{$error}{/if}	
		</td>
	</tr>  
	<tr>
		<td class="label">{'Installation_SystemCheckPdoMysql'|translate}</td>
		<td>{if $infos.pdo_mysql_ok}{$ok}
		{else}{$error}
		{/if}
		
		{if !$infos.pdo_mysql_ok || !$infos.pdo_ok}
			<p class="error" style="width:80%">{'Installation_SystemCheckPdoError'|translate}
			<small>
			<br /><br />
			{'Installation_SystemCheckPdoHelp'|translate:"<br/><code>extension=php_pdo.dll</code><br /><code>extension=php_pdo_mysql.dll</code><br />":"<code>--with-pdo-mysql </code>":"<br/><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />"}
			</small>
			</p>
		{/if}
		
		</td>
	</tr>
	<tr>
		<td valign="top">
			{'Installation_SystemCheckWriteDirs'|translate}
		</td>
		<td>
			{foreach from=$infos.directories key=dir item=bool}
				{if $bool}{$ok}{else}
				<span style="color:red">{$error}</span>{/if} 
				{$dir}
				<br />				
			{/foreach}
		</td>
	</tr>
</table>

{if $problemWithSomeDirectories}
	<br />
	<div class="error">
			{'Installation_SystemCheckWriteDirsHelp'|translate}:
	{foreach from=$infos.directories key=dir item=bool}
		<ul>{if !$bool}
			<li><pre>chmod a+w {$dir}</pre></li>
		{/if}
		</ul>
	{/foreach}
	</div>
	<br />
{/if}
<h1>Optional</h1>
<table class="infos">
	<tr>
		<td class="label">{'Installation_SystemCheckMemoryLimit'|translate}</td>
		<td>
			{$infos.memoryCurrent}
			{if $infos.memory_ok}{$ok}{else}{$warning} 
				<br /><i>{'Installation_SystemCheckMemoryLimitHelp'|translate}</i>{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckGD'|translate}</td>
		<td>
			{if $infos.gd_ok}{$ok}{else}{$warning} <br /><i>{'Installation_SystemCheckGDHelp'|translate}</i>{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckTimeLimit'|translate}</td>
		<td>{if $infos.setTimeLimit_ok}{$ok}{else}{$warning}
			<br /><i>{'Installation_SystemCheckTimeLimitHelp'|translate}</i>{/if}</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckMail'|translate}</td>
		<td>{if $infos.mail_ok}{$ok}{else}{$warning}{/if}</td>
	</tr>
</table>
<p><small>
Legend:
<br />
{$ok} {'General_Ok'|translate}<br />
{$error} {'General_Error'|translate}: {'Installation_SystemCheckError'|translate} <br />
{$warning} {'General_Warning'|translate}: {'Installation_SystemCheckWarning'|translate} <br />
</small></p>

{if !$showNextStep}
<p class="nextStep">
	<a href="{url}">{'General_Refresh'|translate} &raquo;</a>
</p>

{/if}
