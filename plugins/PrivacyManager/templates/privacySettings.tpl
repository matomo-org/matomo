{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

{if $isSuperUser}

<h2>{'PrivacyManager_TeaserHeadline'|translate}</h2>
<p>{'PrivacyManager_Teaser'|translate:'<a href="#anonymizeIPAnchor">':"</a>":'<a href="#deleteLogsAnchor">':"</a>":'<a href="#optOutAnchor">':"</a>"}</p>
<a name="anonymizeIPAnchor"></a>
<h2>{'PrivacyManager_UseAnonymizeIp'|translate}</h2>
<form method="post" action="{url action=index form=formMaskLength}" id="formMaskLength" name="formMaskLength">
	<div id='anonymizeIpSettings'>
		<table class="adminTable" style='width:800px;'>
			<tr>
				<td width="250">{'PrivacyManager_UseAnonymizeIp'|translate}<br/>
					<span class="form-description">{'PrivacyManager_AnonymizeIpDescription'|translate}</span>
				</td>
				<td width='500'>
					<label><input type="radio" name="anonymizeIPEnable" value="1" {if $anonymizeIP.enabled eq '1'}
								  checked {/if}/> {'General_Yes'|translate}</label>
					<label><input type="radio" name="anonymizeIPEnable" value="0"
								  style="margin-left:20px;" {if $anonymizeIP.enabled eq '0'} checked {/if}/>  {'General_No'|translate}
					</label>
					<input type="hidden" name="token_auth" value="{$token_auth}"/>
					<input type="hidden" name="pluginName" value="{$anonymizeIP.name}"/>
				</td>
				<td width="200">
					{'AnonymizeIP_PluginDescription'|translate|inlineHelp}
				</td>
			</tr>
		</table>
	</div>
	<div id="anonymizeIPenabled">
		<table class="adminTable">
			<tr>
				<td width="250">{'PrivacyManager_AnonymizeIpMaskLengtDescription'|translate}</td>
				<td>
					<label><input type="radio" name="maskLength" value="1" {if $anonymizeIP.maskLength eq '1'}
								  checked {/if}/> {'PrivacyManager_AnonymizeIpMaskLength'|translate:"1":"192.168.100.xxx"} {'General_Recommended'|translate}
					</label><br/>
					<label><input type="radio" name="maskLength" value="2" {if $anonymizeIP.maskLength eq '2'}
								  checked {/if}/> {'PrivacyManager_AnonymizeIpMaskLength'|translate:"2":"192.168.xxx.xxx"}</label><br/>
					<label><input type="radio" name="maskLength" value="3" {if $anonymizeIP.maskLength eq '3'}
								  checked {/if}/> {'PrivacyManager_AnonymizeIpMaskLength'|translate:"3":"192.xxx.xxx.xxx"}</label>
				</td>
			</tr>
		</table>
	</div>
	<input type="submit" value="{'General_Save'|translate}" id="privacySettingsSubmit" class="submit"/>
</form>

<a name="deleteLogsAnchor"></a>
<h2>{'PrivacyManager_DeleteLogSettings'|translate}</h2>
<p>{'PrivacyManager_DeleteLogDescription'|translate}</p>
<form method="post" action="{url action=index form=formDeleteSettings}" id="formDeleteSettings" name="formMaskLength">
	<div id='deleteLogSettingEnabled'>
		<table class="adminTable" style='width:800px;'>
			<tr>
				<td width="250">{'PrivacyManager_UseDeleteLog'|translate}<br/>

				</td>
				<td width='500'>
					<label><input type="radio" name="deleteEnable" value="1" {if $deleteLogs.config.delete_logs_enable eq '1'}
								  checked {/if}/> {'General_Yes'|translate}</label>
					<label><input type="radio" name="deleteEnable" value="0"
								  style="margin-left:20px;" {if $deleteLogs.config.delete_logs_enable eq '0'}
								  checked {/if}/>  {'General_No'|translate}
					</label>
					<span class="ajaxSuccess">
						{'PrivacyManager_DeleteLogDescription2'|translate}
						<a href="http://piwik.org/faq/general/#faq_125" target="_blank">
							{'General_ClickHere'|translate}
						</a>
					</span>
				</td>
				<td width="200">
					{'PrivacyManager_DeleteLogInfo'|translate:$deleteLogs.deleteTables|inlineHelp}
				</td>
			</tr>
		</table>
	</div>

	<div id="deleteLogSettings">
		<table class="adminTable" style='width:800px;'>
			<tr>
				<td width="250">&nbsp;</td>
				<td width="500">
					<label>{'PrivacyManager_DeleteLogsOlderThan'|translate}
						<input type="text" id="deleteOlderThan" value="{$deleteLogs.config.delete_logs_older_than}" style="width:30px;"
							   name="deleteOlderThan">
						{'CoreHome_PeriodDays'|translate}</label><br/>
					<span class="form-description">{'PrivacyManager_LeastDaysInput'|translate:"7"}</span>
				</td>
				<td width="200">

				</td>
			</tr>
			<tr>
				<td width="250">&nbsp;</td>
				<td width="500">
					{'PrivacyManager_DeleteLogInterval'|translate}
					<select id="deleteLowestInterval" name="deleteLowestInterval">
						<option {if $deleteLogs.config.delete_logs_schedule_lowest_interval eq '1'} selected="selected" {/if}
																									value="1"> {'CoreHome_PeriodDay'|translate}</option>
						<option {if $deleteLogs.config.delete_logs_schedule_lowest_interval eq '7'} selected="selected" {/if}
																									value="7">{'CoreHome_PeriodWeek'|translate}</option>
						<option {if $deleteLogs.config.delete_logs_schedule_lowest_interval eq '30'} selected="selected" {/if}
																									 value="30">{'CoreHome_PeriodMonth'|translate}</option>
					</select>
				</td>
				<td width="200">
					{capture assign=purgeStats}
						{if $deleteLogs.lastRun}<strong>{'PrivacyManager_LastDelete'|translate}:</strong>
							{$deleteLogs.lastRunPretty}
							<br/><br/>{/if}
						<strong>{'PrivacyManager_NextDelete'|translate}:</strong>
						{$deleteLogs.nextRunPretty}
					{/capture}
					{$purgeStats|inlineHelp}
				</td>
			</tr>
			<tr>
				<td width="250">&nbsp;</td>
				<td width="500">
					{'PrivacyManager_DeleteMaxRows'|translate} 
					<select id="deleteMaxRows" name="deleteMaxRows">
						<option {if $deleteLogs.config.delete_max_rows_per_run eq '100'} selected="selected" {/if}  value="100">100.000</option>
						<option {if $deleteLogs.config.delete_max_rows_per_run eq '500'} selected="selected" {/if} value="500">500.000</option>
						<option {if $deleteLogs.config.delete_max_rows_per_run eq '1000'} selected="selected" {/if} value="1000">1.000.000</option>
					</select>
				</td>
				<td width="200"></td>
			</tr>
		</table>
	</div>
	<input type="submit" value="{'General_Save'|translate}" id="deleteLogSettingsSubmit" class="submit"/>
</form>

{/if}

<a name="optOutAnchor"></a>
<h2>{'CoreAdminHome_OptOutForYourVisitors'|translate}</h2>
<p>{'CoreAdminHome_OptOutExplanation'|translate}
{capture name=optOutUrl}{$piwikUrl}index.php?module=CoreAdminHome&action=optOut&language={$language}{/capture}
{assign var=optOutUrl value=$smarty.capture.optOutUrl}
{capture name=iframeOptOut}
<iframe frameborder="no" width="600px" height="200px" src="{$smarty.capture.optOutUrl}"></iframe>{/capture}
<code>{$smarty.capture.iframeOptOut|escape:'html'}</code>
<br/>
{'CoreAdminHome_OptOutExplanationBis'|translate:"<a href='$optOutUrl' target='_blank'>":"</a>"}
</p>

{include file="CoreAdminHome/templates/footer.tpl"}
