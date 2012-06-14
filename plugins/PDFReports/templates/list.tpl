<div id='entityEditContainer'>
	<table class="dataTable entityTable">
	<thead>
	<tr>
        <th class="first">{'General_Description'|translate}</th>
        <th>{'PDFReports_EmailSchedule'|translate}</th>
        <th>{'PDFReports_ReportFormat'|translate}</th>
        <th>{'PDFReports_SendReportTo'|translate}</th>
        <th>{'General_Download'|translate}</th>
        <th>{'General_Edit'|translate}</th>
        <th>{'General_Delete'|translate}</th>
	</tr>
	</thead>
	
	{if $userLogin=='anonymous'}
		<tr><td colspan='7'> 
		<br/>
		{'PDFReports_MustBeLoggedIn'|translate}
		<br/>&rsaquo; <a href='index.php?module={$loginModule}'>{'Login_LogIn'|translate}</a></strong>   
		<br/><br/> 
		</td></tr>
		</table>
	{elseif empty($reports)}
		<tr><td colspan='7'> 
		<br/>
		{'PDFReports_ThereIsNoReportToManage'|translate:$siteName}.
		<br/><br/>
		<a onclick='' id='linkAddReport'>&rsaquo; {'PDFReports_CreateAndScheduleReport'|translate}</a>
		<br/><br/> 
		</td></tr>
		</table>
	{else}
		{foreach from=$reports item=report}
			<tr>
				<td class="first">{$report.description}</td>
				<td>{$periods[$report.period]}
		 		<!-- Last sent on {$report.ts_last_sent} -->
				</td>
				<td>
					{if !empty($report.format)}
						{$report.format|upper}
					{/if}
				</td>
				<td>
					{*report recipients*}
					{if $report.recipients|@count eq 0}
						{'PDFReports_NoRecipients'|translate}
					{else}
						{foreach name=recipients from=$report.recipients item=recipient}
							{$recipient}<br/>
						{/foreach}
						{*send now link*}
						<a href='#' idreport='{$report.idreport}' name='linkSendNow' class="link_but" style='margin-top:3px'>
							<img border=0 src='{$reportTypes[$report.type]}'/>
							{'PDFReports_SendReportNow'|translate}
						</a>
					{/if}
				</td>
				<td>
					{*download link*}
					<a href="{url module=API token_auth=$token_auth method='PDFReports.generateReport' date=$rawDate idReport=$report.idreport outputType=$downloadOutputType language=$language}"
					   target="_blank" name="linkDownloadReport" id="{$report.idreport}" class="link_but">
						<img src='{$reportFormatsByReportType[$report.type][$report.format]}' border="0" />
						{'General_Download'|translate}
					</a>
				</td>
				<td>
					{*edit link*}
					<a href='#' name="linkEditReport" id="{$report.idreport}" class="link_but">
						<img src='themes/default/images/ico_edit.png' border="0" />
							{'General_Edit'|translate}
					</a>
				</td>
				<td>
					{*delete link *}
					<a href='#' name="linkDeleteReport" id="{$report.idreport}" class="link_but">
						<img src='themes/default/images/ico_delete.png' border="0" />
						{'General_Delete'|translate}
					</a>
				</td>
			</tr>
		{/foreach}
		</table>
		{if $userLogin != 'anonymous'}
			<br/>
			<a onclick='' id='linkAddReport'>&rsaquo; {'PDFReports_CreateAndScheduleReport'|translate}</a>
			<br/><br/>
		{/if}
	{/if}
</div>
