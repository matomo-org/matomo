{assign var=ok value="<img src='themes/default/images/ok.png' />"}
{assign var=error value="<img src='themes/default/images/error.png' />"}
{assign var=warning value="<img src='themes/default/images/warning.png' />"}
{assign var=link value="<img src='themes/default/images/link.gif' />"}

<h1>{'Installation_DatabaseCheck'|translate}</h1>

<table class="infosServer">
	<tr>
		<td class="label">{'Installation_DatabaseServerVersion'|translate}</td>
		<td>{if isset($databaseVersionOk)}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td class="label">{'Installation_DatabaseCreation'|translate}</td>
		<td>{if isset($databaseCreated)}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td class="label">{'Installation_DatabaseClientCharset'|translate}</td>
		<td>{if isset($charsetWarning)}{$warning}{else}utf8 {$ok}{/if}</td>
	</tr>
{if isset($charsetWarning)}
	<tr>
		<td colspan="2">
			<small>
				<span style="color:orange">{'Installation_ConnectionCharacterSetNotUtf8'|translate}</span>
			</small>
		</td>
	</tr>
{/if}
	<tr>
		<td class="label">{'Installation_DatabaseTimezone'|translate}</td>
		<td>{if isset($timezoneWarning)}{$warning}{else}{$ok}{/if}</td>
	</tr>
{if isset($timezoneWarning)}
	<tr>
		<td colspan="2">
			<small>
				<span style="color:orange">{'Installation_TimezoneMismatch'|translate}</span>
			</small>
		</td>
	</tr>
{/if}
</table>

<p>
{$link} <a href="http://piwik.org/docs/requirements/" target="_blank">{'Installation_Requirements'|translate}</a> 
</p>
