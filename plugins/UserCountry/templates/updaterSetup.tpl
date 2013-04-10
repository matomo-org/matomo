<div id="geoipdb-update-info" {if !$geoIPDatabasesInstalled}style="display:none"{/if}>
    <p>{'UserCountry_GeoIPUpdaterInstructions'|translate:'<a href="http://www.maxmind.com/en/download_files?rId=piwik" _target="blank">':'</a>':'<a href="http://www.maxmind.com/?rId=piwik">':'</a>'}
        <br/><br/>
{'UserCountry_GeoLiteCityLink'|translate:"<a href=\"$geoLiteUrl\">":$geoLiteUrl:'</a>'}
	{if $geoIPDatabasesInstalled}
	<br/><br/>{'UserCountry_GeoIPUpdaterIntro'|translate}:
	{/if}
	</p>
	<table class="adminTable" style="width:900px">
		<tr>
			<th>{'Live_GoalType'|translate}</th>
			<th>{'Actions_ColumnDownloadURL'|translate}</th>
			<th></th>
		</tr>
		<tr>
			<td width="140">{'UserCountry_LocationDatabase'|translate}</td>
			<td><input type="text" id="geoip-location-db" value="{$geoIPLocUrl}"/></td>
			<td width="164">
				{capture assign=locationHint}
				{'UserCountry_LocationDatabaseHint'|translate}
				{/capture}
				{$locationHint|inlineHelp}
			</td>
		</tr>
		<tr>
			<td width="140">{'UserCountry_ISPDatabase'|translate}</td>
			<td><input type="text" id="geoip-isp-db" value="{$geoIPIspUrl}"/></td>
		</tr>
		<tr>
			<td width="140">{'UserCountry_OrgDatabase'|translate}</td>
			<td><input type="text" id="geoip-org-db" value="{$geoIPOrgUrl}"/></td>
		</tr>
		<tr>
			<td width="140">{'UserCountry_DownloadNewDatabasesEvery'|translate}</td>
			<td id="geoip-update-period-cell">
				<input type="radio" name="geoip-update-period" value="month" id="geoip-update-period-month" {if $geoIPUpdatePeriod eq 'month'}checked="checked"{/if}/>
				<label for="geoip-update-period-month">{'CoreHome_PeriodMonth'|translate}</label>
				
				<input type="radio" name="geoip-update-period" value="week" id="geoip-update-period-week" {if $geoIPUpdatePeriod eq 'week'}checked="checked"{/if}/>
				<label for="geoip-update-period-week">{'CoreHome_PeriodWeek'|translate}</label>
			</td>
			<td width="164">
			{capture assign=lastTimeRunNote}
				{if !empty($lastTimeUpdaterRun)}
					{'UserCountry_UpdaterWasLastRun'|translate:$lastTimeUpdaterRun}
				{else}
					{'UserCountry_UpdaterHasNotBeenRun'|translate}
				{/if}
			{/capture}
			{$lastTimeRunNote|inlineHelp}
			</td>
		</tr>
	</table>
	<p style="display:inline-block;vertical-align:top">
		<input type="button" class="submit" value="{if !$geoIPDatabasesInstalled}{'General_Continue'|translate}{else}{'General_Save'|translate}{/if}" id="update-geoip-links"/>
	</p>
	<div style="display:inline-block;width:700px">
		<span style="display:none" class="ajaxSuccess" id="done-updating-updater">{'General_Done'|translate}!</span>
		<span id="geoipdb-update-info-error" style="display:none" class="error"></span>
		<div id="geoip-progressbar-container" style="display:none">
			<div id="geoip-updater-progressbar"></div>
			<span id="geoip-updater-progressbar-label"></span>
		</div>
	</div>
</div>
