{assign var=ok value="<img src='themes/default/images/ok.png' />"}
{assign var=error value="<img src='themes/default/images/error.png' />"}
{assign var=warning value="<img src='themes/default/images/warning.png' />"}
{assign var=link value="<img src='themes/default/images/link.gif' />"}

<h1>{'Installation_SystemCheck'|translate}</h1>

<table class="infosServer">
	<tr>
		<td class="label">{'Installation_SystemCheckPhp'|translate} &gt; {$infos.phpVersion_minimum}</td>
		<td>{if $infos.phpVersion_ok}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckPdo'|translate}</td>
		<td>{if $infos.pdo_ok}{$ok}
			{else}{$error}{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckPdoMysql'|translate}</td>
		<td>{if $infos.pdo_mysql_ok}{$ok}
			{else}{$error}{/if}
		</td>
	</tr>
	{if !$infos.pdo_mysql_ok || !$infos.pdo_ok}
	<tr>
		<td colspan="2" class="error">
			<small>
				{if $infos.isWindows}
					{'Installation_SystemCheckWinPdoHelp'|translate:"<br /><br /><code>extension=php_pdo.dll</code><br /><code>extension=php_pdo_mysql.dll</code><br />"|nl2br}
				{else}
					{'Installation_SystemCheckPdoHelp'|translate:"<br /><br /><code>--with-pdo-mysql </code><br />":"<br /><br /><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />"|nl2br}
				{/if}
				<br />
				{'Installation_SystemCheckPhpPdoSite'|translate}
			</small>
		</td>
	</tr>
	{/if}
	<tr>
		<td valign="top">
			{'Installation_SystemCheckJson'|translate}
		</td>
		<td>{if $infos.json || $infos.xml}{$ok}
			{else}{$error}{/if}
		</td>
	</tr>
	{if !$infos.json && !$infos.xml}
	<tr>
		<td colspan="2" class="error">
			<small>
				{'Installation_SystemCheckJsonHelp'|translate}
				<br />
				{if version_compare($infos.phpVersion, '5.2.0') >= 0}
					{'Installation_SystemCheckJsonSite'|translate}
				{else}
					{'Installation_SystemCheckXmlSite'|translate}
				{/if}
			</small>
		</td>
	</tr>
	{/if}
	<tr>
		<td class="label">{'Installation_SystemCheckExtensions'|translate}</td>
		<td>{foreach from=$infos.needed_extensions item=needed_extension}
				{$needed_extension}
				{if in_array($needed_extension, $infos.missing_extensions)}
					{$error}
				{else}
					{$ok}
				{/if}
				<br />
			{/foreach}
		</td>
	</tr>
	{if count($infos.missing_extensions) gt 0}
	<tr>
		<td colspan="2" class="error">
			<small>
				{foreach from=$infos.missing_extensions item=missing_extension}
					<p>
					{$helpMessages[$missing_extension]|translate}
					</p>
				{/foreach}
			</small>
		</td>
	</tr>
	{/if}
	<tr>
		<td valign="top">
			{'Installation_SystemCheckWriteDirs'|translate}
		</td>
		<td>
			<small>
				{foreach from=$infos.directories key=dir item=bool}
					{if $bool}{$ok}{else}
					<span style="color:red">{$error}</span>{/if} 
					{$dir}
					<br />				
				{/foreach}
			</small>
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
<h1>{'Optional'|translate}</h1>
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
		<td class="label">{'Installation_SystemCheckOpenURL'|translate}</td>
		<td>
			{if $infos.openurl}{$infos.openurl} {$ok}{else}{$warning} <br /><i>{'Installation_SystemCheckOpenURLHelp'|translate}</i>{/if}
		</td>
	</tr>
	{if $infos.json}
	<tr>
		<td class="label">{'Installation_SystemCheckXml'|translate}</td>
		<td>
			{if $infos.xml}{$ok}{else}{$warning}<br /><i>{'Installation_SystemCheckXmlHelp'|translate}</i>{/if}
		</td>
	</tr>
	{/if}
	<tr>
		<td class="label">{'Installation_SystemCheckGD'|translate}</td>
		<td>
			{if $infos.gd_ok}{$ok}{else}{$warning} <br /><i>{'Installation_SystemCheckGDHelp'|translate}</i>{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckFunctions'|translate}</td>
		<td>{foreach from=$infos.needed_functions item=needed_function}
				{$needed_function}
				{if in_array($needed_function, $infos.missing_functions)}
					{$warning}
					<p>
					<small>
					{$helpMessages[$needed_function]|translate}
					</small>
					</p>
				{else}
					{$ok}<br />
				{/if}
			{/foreach}
		</td>
	</tr>
</table>

<p>
{$link} <a href="http://piwik.org/docs/requirements/" target="_blank">{'Installation_Requirements'|translate}</a> 
</p>

{if !$showNextStep}
{literal}
<style>
#legend {
	border:1px solid #A5A5A5;
	padding:5px;
	color:#727272;
	margin-top:30px;
}
</style>
{/literal}
<div id="legend"><small>
<b>{'Installation_Legend'|translate}</b>
<br />
{$ok} {'General_Ok'|translate}<br />
{$error} {'General_Error'|translate}: {'Installation_SystemCheckError'|translate} <br />
{$warning} {'General_Warning'|translate}: {'Installation_SystemCheckWarning'|translate} <br />
</small></div>


<p class="nextStep">
	<a href="{url}">{'General_Refresh'|translate} &raquo;</a>
</p>
{/if}
