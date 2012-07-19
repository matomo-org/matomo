{assign var=ok value="<img src='themes/default/images/ok.png' />"}
{assign var=error value="<img src='themes/default/images/error.png' />"}
{assign var=warning value="<img src='themes/default/images/warning.png' />"}
{assign var=link value="<img src='themes/default/images/link.gif' />"}

{if !$showNextStep}
{include file="Installation/templates/systemCheck_legend.tpl"}
<br style="clear:both">
{/if}

<h3>{'Installation_SystemCheck'|translate}</h2>
<br/>
<table class="infosServer">
	<tr>
		{capture assign="MinPHP"}{'Installation_SystemCheckPhp'|translate} &gt; {$infos.phpVersion_minimum}{/capture}
		<td class="label">{$MinPHP}</td>

		<td>{if $infos.phpVersion_ok}{$ok}
		{else}{$error} <span class="err">{'General_Error'|translate}: {'General_Required'|translate:$MinPHP}</span>{/if}</td>
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
					{'Installation_SystemCheckPdoAndMysqliHelp'|translate:"<br /><br /><code>--with-mysqli</code><br /><code>--with-pdo-mysql</code><br /><br />":"<br /><br /><code>extension=mysqli.so</code><br /><code>extension=pdo.so</code><br /><code>extension=pdo_mysql.so</code><br />"}
				{/if}
				{'Installation_RestartWebServer'|translate}
				<br />
				<br />
				{'Installation_SystemCheckPhpPdoAndMysqliSite'|translate}
				</p>
			</small>
		</td>
	</tr>
	{/if}
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckExtensions'|translate}</td>
		<td>{foreach from=$infos.needed_extensions item=needed_extension}
				{if in_array($needed_extension, $infos.missing_extensions)}
					{$error}
					{capture assign="hasError"}1{/capture}
				{else}
					{$ok}
				{/if}
				{$needed_extension}
				<br />
			{/foreach}
			<br/>{if isset($hasError)}{'Installation_RestartWebServer'|translate}{/if}
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
				{if  in_array($needed_function, $infos.missing_functions)}
					{$error} <span class='err'>{$needed_function}</span>
					{capture assign="hasError"}1{/capture}
					<p>
					<i>{$helpMessages[$needed_function]|translate}</i>
					</p>
				{else}
					{$ok} {$needed_function}<br />
				{/if}
			{/foreach}
			<br/>{if isset($hasError)}{'Installation_RestartWebServer'|translate}{/if}
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
<br />
	
{if $problemWithSomeDirectories}
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
		<td class="label">{'Installation_SystemCheckTracker'|translate}</td>
		<td>
			{if $infos.tracker_status == 0}
				{$ok}
			{else}
				{$warning} <span class="warn">{$infos.tracker_status}
				<br />{'Installation_SystemCheckTrackerHelp'|translate} </span>
				<br/>{'Installation_RestartWebServer'|translate}
			{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckMemoryLimit'|translate}</td>
		<td>
			{if $infos.memory_ok}
				{$ok} {$infos.memoryCurrent}
			{else}
				{$warning} <span class="warn">{$infos.memoryCurrent}</span>
				<br />{'Installation_SystemCheckMemoryLimitHelp'|translate} 
				{'Installation_RestartWebServer'|translate}
			{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'SitesManager_Timezone'|translate}</td>
		<td>
			{if $infos.timezone}{$ok}
			{else}{$warning} 
			<span class="warn">{'SitesManager_AdvancedTimezoneSupportNotFound'|translate} </span>
			<br/><a href="http://php.net/manual/en/datetime.installation.php" target="_blank">Timezone PHP documentation</a>.
			{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckOpenURL'|translate}</td>
		<td>
			{if $infos.openurl}{$ok} {$infos.openurl}
			{else}{$warning} <span class="warn">{'Installation_SystemCheckOpenURLHelp'|translate}</span>
			{/if}
			{if !$infos.can_auto_update}
				<br />{$warning} <span class="warn">{'Installation_SystemCheckAutoUpdateHelp'|translate}</span>{/if}	
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckGD'|translate}</td>
		<td>
			{if $infos.gd_ok}{$ok}
			{else}{$warning} <span class="warn">{'Installation_SystemCheckGD'|translate} 
			<br /> {'Installation_SystemCheckGDHelp'|translate} </span>{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckMbstring'|translate}</td>
		<td>
			{if $infos.hasMbstring}
				{if $infos.multibyte_ok}{$ok}
				{else}
					{$warning} <span class="warn">{'Installation_SystemCheckMbstring'|translate}
					<br/> {'Installation_SystemCheckMbstringFuncOverloadHelp'|translate}</span>
				{/if}
			{else}
				{$warning} <span class="warn">{'Installation_SystemCheckMbstringExtensionHelp'|translate}</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckOtherExtensions'|translate}</td>
		<td>{foreach from=$infos.desired_extensions item=desired_extension}
				{if in_array($desired_extension, $infos.missing_desired_extensions)}
					{$warning}<span class="warn">{$desired_extension}</span>
					<p>{$helpMessages[$desired_extension]|translate}</p>
				{else}
					{$ok} {$desired_extension}<br />
				{/if}
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="label">{'Installation_SystemCheckOtherFunctions'|translate}</td>
		<td>{foreach from=$infos.desired_functions item=desired_function}
				{if in_array($desired_function, $infos.missing_desired_functions)}
					{$warning} <span class="warn">{$desired_function}</span>
					<p>{$helpMessages[$desired_function]|translate}</p>
				{else}
					{$ok} {$desired_function}<br />
				{/if}
			{/foreach}
		</td>
	</tr>
	{if isset($infos.general_infos.assume_secure_protocol)}
	<tr>
		<td class="label">{'Installation_SystemCheckSecureProtocol'|translate}</td>
		<td>
			{$warning} <span class="warn">{$infos.protocol} </span><br/>
			{'Installation_SystemCheckSecureProtocolHelp'|translate}
			<br /><br />
			<code>[General]<br/>
assume_secure_protocol = 1</code><br />
		</td>
	</tr>
	{/if}
</table>

{include file="Installation/templates/integrityDetails.tpl"}


{if !$showNextStep}
<br/><p>
{$link} &nbsp;<a href="?module=Proxy&action=redirect&url=http://piwik.org/docs/requirements/" target="_blank">{'Installation_Requirements'|translate}</a> 
</p>
{include file="Installation/templates/systemCheck_legend.tpl"}
{/if}
