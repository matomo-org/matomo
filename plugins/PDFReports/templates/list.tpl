<div id='entityEditContainer'>
	<table class="dataTable entityTable">
	<thead>
	<tr>
        <th class="first">{'General_Description'|translate}</th>
        <th>{'PDFReports_EmailSchedule'|translate}</th>
        <th>{'PDFReports_SendReportTo'|translate}</th>
        <th>{'General_Download'|translate}</th>
        <th>{'General_Edit'|translate}</th>
        <th>{'General_Delete'|translate}</th>
	</tr>
	</thead>
	
	{if $userLogin=='anonymous'}
		<tr><td colspan=6> 
		<br/>
		{'PDFReports_YouMustBeLoggedIn'|translate}
		<br/>&rsaquo; <a href='index.php?module={$loginModule}'>{'Login_LogIn'|translate}</a></strong>   
		<br/><br/> 
		</td></tr>
		</table>
	{elseif empty($reports)}
		<tr><td colspan=6> 
		<br/>
		{'PDFReports_ThereIsNoPDFReportToManage'|translate:$siteName}. 
		<br/><br/>
		<a onclick='' id='linkAddReport'>&rsaquo; {'PDFReports_CreateAndSchedulePDFReport'|translate}</a>
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
				<td>{if $report.email_me == 1}{$currentUserEmail}{/if} 
					{$report.additional_emails|replace:",":" "}
					<br/><a href='#' idreport='{$report.idreport}' name='linkEmailNow' class="link_but" style='margin-top:3px'><img border=0 src='themes/default/images/email.png'> {'PDFReports_SendReportNow'|translate}</a>
					</td>
				<td><a href="{url module=API token_auth=$token_auth method='PDFReports.generateReport' idSite=$idSite date=$date idReport=$report.idreport outputType=$pdfDownloadOutputType}" target="_blank" name="linkDownloadReport" id="{$report.idreport}" class="link_but"><img src='plugins/UserSettings/images/plugins/pdf.gif' border="0" /> {'General_Download'|translate}</a></td>
				<td><a href='#' name="linkEditReport" id="{$report.idreport}" class="link_but"><img src='themes/default/images/ico_edit.png' border="0" /> {'General_Edit'|translate}</a></td>
				<td><a href='#' name="linkDeleteReport" id="{$report.idreport}" class="link_but"><img src='themes/default/images/ico_delete.png' border="0" /> {'General_Delete'|translate}</a></td>
			</tr>
		{/foreach}
		</table>
		{if $userLogin != 'anonymous'}
			<br/>
			<a onclick='' id='linkAddReport'>&rsaquo; {'PDFReports_CreateAndSchedulePDFReport'|translate}</a>
		{/if}
	{/if}
</div>
