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
		<td class="label">{'Installation_DatabaseClientVersion'|translate}</td>
		<td>{if isset($clientVersionWarning)}{$warning}{else}{$ok}{/if}</td>
	</tr>
{if isset($clientVersionWarning)}
	<tr>
		<td colspan="2">
			<small>
				<span style="color:#FF7F00">{$clientVersionWarning}</span>
			</small>
		</td>
	</tr>
{/if}
	<tr>
		<td class="label">{'Installation_DatabaseCreation'|translate}</td>
		<td>{if isset($databaseCreated)}{$ok}{else}{$error}{/if}</td>
	</tr>
	<tr>
		<td class="label">{'Installation_DatabaseTimezone'|translate}</td>
		<td>{if isset($timezoneWarning)}{$warning}{else}{$ok}{/if}</td>
	</tr>
{if isset($timezoneWarning)}
	<tr>
		<td colspan="2">
			<small>
				<span style="color:#FF7F00">{'Installation_TimezoneMismatch'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/FAQ/troubleshooting/#faq_58' target='_blank'>FAQ</a>"}.</span>
			</small>
		</td>
	</tr>
{/if}
</table>

<p>
{$link} <a href="misc/redirectToUrl.php?url=http://piwik.org/docs/requirements/" target="_blank">{'Installation_Requirements'|translate}</a> 
</p>
