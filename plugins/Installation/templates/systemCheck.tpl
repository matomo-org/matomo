{assign var=ok value="<img src='themes/default/images/ok.png' />"}
{assign var=error value="<img src='themes/default/images/error.png' />"}
{assign var=warning value="<img src='themes/default/images/warning.png' />"}
{assign var=link value="<img src='themes/default/images/link.gif' />"}

<h2>{'Installation_SystemCheck'|translate}</h2>

<table class="infosServer">
	<tr>
		<td class="label">{'Installation_SystemCheckPhp'|translate} &gt; {$infos.phpVersion_minimum}</td>
		<td>{if $infos.phpVersion_ok}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td class="label">PDO {'Installation_Extension'|translate}</td>
		<td>{if $infos.pdo_ok}{$ok}
			{else}-{/if}
		</td>
	</tr>
	{foreach from=$infos.adapters key=adapter item=port}
	<tr>
		<td class="label">{$adapter} {'Installation_Extension'|translate}</td>
		<td>{$ok}</td>
	</tr>
	{/foreach}
	{if !count($infos.adapters)}
	<tr>
		<td colspan="2" class="error">
			<small>
				{'Installation_SystemCheckDatabaseHelp'|translate}
				<p>
				{if $infos.isWindows}
					{'Installation_SystemCheckWinPdoAndMysqliHelp'|translate:"<br /><br /><code>extension=php_mysqli.dll</code><br /><code>extension=php_pdo.dll</code><br /><code>extension=php_pdo_mysql.dll</code><br />"|nl2br}
				{else}
					{'Installation_SystemCheckPdoAndMysqliHelp'|translate:"<br /><br /><code>--with-mysqli</code><br /><code>--with-pdo-mysql</code><br />":"<br /><br /><code>extension=mysqli.so</code><br /><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />"|nl2br}
				{/if}
				<br />
				{'Installation_SystemCheckPhpPdoAndMysqliSite'|translate}
				</p>
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
				{if in_array($needed_extension, $infos.missing_extensions)}
					{$error}
				{else}
					{$ok}
				{/if}
				{$needed_extension}
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
					<i>{$helpMessages[$missing_extension]|translate}</i>
					</p>
				{/foreach}
			</small>
		</td>
	</tr>
	{/if}
	<tr>
		<td class="label">{'Installation_SystemCheckFunctions'|translate}</td>
		<td>{foreach from=$infos.needed_functions item=needed_function}
				{if in_array($needed_function, $infos.missing_functions)}
					{$error} {$needed_function}
					<p>
					<i>{$helpMessages[$needed_function]|translate}</i>
					</p>
				{else}
					{$ok} {$needed_function}<br />
				{/if}
			{/foreach}
		</td>
	</tr>
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
<h2>{'Optional'|translate}</h2>
<table class="infos">
	<tr>
		<td class="label">{'Installation_SystemCheckFileIntegrity'|translate}</td>
		<td>
		{if empty($infos.integrityErrorMessages)}
			{$ok}
		{else}
			{if $infos.integrity}
				{$warning} <i>{$infos.integrityErrorMessages[0]}</i>
			{else}
				{$error} <i>{$infos.integrityErrorMessages[0]}</i>
			{/if}
			{if count($infos.integrityErrorMessages) > 1}
				<button id="more-results" class="ui-button ui-state-default ui-corner-all">{'General_Details'|translate}</button>
			{/if}
		{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckMemoryLimit'|translate}</td>
		<td>
			{if $infos.memory_ok}
				{$ok} {$infos.memoryCurrent}
			{else}
				{$warning} {$infos.memoryCurrent}
				<br /><i>{'Installation_SystemCheckMemoryLimitHelp'|translate}</i>
			{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'SitesManager_Timezone'|translate}</td>
		<td>
			{if $infos.timezone}{$ok}{else}{$warning} 
				<br /><i>{'SitesManager_AdvancedTimezoneSupportNotFound'|translate}</i>{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckOpenURL'|translate}</td>
		<td>
			{if $infos.openurl}{$ok} {$infos.openurl}{else}{$warning} <i>{'Installation_SystemCheckOpenURLHelp'|translate}</i>{/if}
			{if !$infos.can_auto_update}
				<br />{$warning} <i>{'Installation_SystemCheckAutoUpdateHelp'|translate}</i>{/if}	
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
		<td class="label">{'Installation_SystemCheckMbstring'|translate}</td>
		<td>
			{if $infos.hasMbstring}
				{if $infos.multibyte_ok}{$ok}{else}{$warning} <br /><i>{'Installation_SystemCheckMbstringFuncOverloadHelp'|translate}</i>{/if}
			{else}
				{$warning} <br /><i>{'Installation_SystemCheckMbstringExtensionHelp'|translate}</i>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckFunctions'|translate}</td>
		<td>{foreach from=$infos.desired_functions item=desired_function}
				{if in_array($desired_function, $infos.missing_desired_functions)}
					{$warning} {$desired_function}
					<p>
					<i>{$helpMessages[$desired_function]|translate}</i>
					</p>
				{else}
					{$ok} {$desired_function}<br />
				{/if}
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckProtocol'|translate}</td>
		<td>
			{if $infos.protocol_ok}{$ok}{else}{$warning} {$infos.protocol}<br /><i>{'Installation_SystemCheckProtocolHelp'|translate}</i><br /><br /><code>[General]</code><br /><code>reverse_proxy = 1</code><br />{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckIpv4'|translate}</td>
		<td>
			{if $infos.isIpv4}{$ok}{else}{$warning}<br /><i>{'Installation_SystemCheckIpv4Help'|translate}</i>{/if}
		</td>
	</tr>
</table>

{include file="Installation/templates/integrityDetails.tpl"}

<p>
{$link} <a href="misc/redirectToUrl.php?url=http://piwik.org/docs/requirements/" target="_blank">{'Installation_Requirements'|translate}</a> 
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
