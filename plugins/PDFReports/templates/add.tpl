<div class='entityAddContainer' style='display:none'>
<div class='entityCancel'>
	{'PDFReports_CancelAndReturnToPDF'|translate:"<a class='entityCancelLink'>":"</a>"}
</div>
<div class='clear'></div>
<form id='addEditReport'>
<table class="dataTable entityTable">
	<thead>
		<tr class="first">
			<th colspan="2">{'PDFReports_CreateAndSchedulePDFReport'|translate}</th>
		<tr>
	</thead>
	<tbody>
		<tr>
            <td class="first">{'General_Website'|translate} </th>
			<td  style="width:650px">
				{$siteName}
			</td>
		</tr>
		<tr>
            <td class="first">{'General_Description'|translate} </th>
			<td>
			<textarea cols="30" rows="3" id="report_description" class="inp"></textarea>
			<div class="entityInlineHelp">
				{'PDFReports_DescriptionWillBeFirstPage'|translate}
			</div>
			</td>
		</tr>
		<tr>
			<td class="first">{'PDFReports_EmailSchedule'|translate}</td>
			<td>
				<select id="report_period" class="inp">
				{foreach from=$periods item=period key=periodId}
                    <option value="{$periodId}">{$period}</option>
				{/foreach}
				</select>
				
				<div class="entityInlineHelp">
					{'PDFReports_WeeklyScheduleHelp'|translate}
					<br/>
					{'PDFReports_MonthlyScheduleHelp'|translate}
				</div>
			</td>
		</tr>
		<tr>
			<td style='width:240px;' class="first">{'PDFReports_SendReportTo'|translate}
			</td>
			<td>
				<input type="checkbox" id="report_email_me" />
				<label for="report_email_me">{'PDFReports_SentToMe'|translate} (<i>{$currentUserEmail}</i>) </label>
				<br/><br/>
				{'PDFReports_AlsoSendReportToTheseEmails'|translate}<br/>
				<textarea cols="30" rows="3" id="report_additional_emails" class="inp"></textarea>
			</td>
		</tr>
		<tr>
			<td class="first">{'PDFReports_ReportsIncludedInPDF'|translate}</td>
			<td>
			<div id='reportsList'>
				{assign var=countReports value=0}
				<div id='leftcolumn'>
				{foreach from=$reportsByCategory item=reports key=category name=reports}
					{if $countReports >= $newColumnAfter && $newColumnAfter != 0}
						{assign var=newColumnAfter value=0}
						</div><div id='rightcolumn'>
					{/if}
					<div class='reportCategory'>{$category}</div><ul class='listReports'>
					{foreach from=$reports item=report}
						<li><input type="checkbox" id="{$report.uniqueId}" /><label for="{$report.uniqueId}">{$report.name}</label></li>
						{assign var=countReports value=$countReports+1}
					{/foreach}
					</ul>
					<br/>
				{/foreach}
				</div>
			</div>
			</td>
		</tr>
		
	</tbody>
</table>
<input type="hidden" id="report_idreport" value="">
<input type="submit" value="{'PDFReports_CreatePDFReport'|translate}" name="submit" id="report_submit" class="submit" />
</form>
<div class='entityCancel'>
	{'General_OrCancel'|translate:"<a class='entityCancelLink'>":"</a>"}
</span>
</div>

