<div class='entityAddContainer' style='display:none'>
<div class='entityCancel'>
	{'PDFReports_CancelAndReturnToReports'|translate:"<a class='entityCancelLink'>":"</a>"}
</div>
<div class='clear'></div>
<form id='addEditReport'>
<table class="dataTable entityTable">
	<thead>
		<tr class="first">
			<th colspan="2">{'PDFReports_CreateAndScheduleReport'|translate}</th>
		<tr>
	</thead>
	<tbody>
		<tr>
            <td class="first">{'General_Website'|translate} </td>
			<td  style="width:650px">
				{$siteName}
			</td>
		</tr>
		<tr>
            <td class="first">{'General_Description'|translate} </td>
			<td>
			<textarea cols="30" rows="3" id="report_description" class="inp"></textarea>
			<div class="entityInlineHelp">
				{'PDFReports_DescriptionOnFirstPage'|translate}
			</div>
			</td>
		</tr>
		<tr>
			<td class="first">{'PDFReports_EmailSchedule'|translate}</td>
			<td>
				<select id="report_period" class="inp">
				{foreach from=$periods item=period key=periodId}
					<option value="{$periodId}">
						{$period}
					</option>
				{/foreach}
				</select>
				
				<div class="entityInlineHelp">
					{'PDFReports_WeeklyScheduleHelp'|translate}
					<br/>
					{'PDFReports_MonthlyScheduleHelp'|translate}
				</div>
			</td>
		</tr>

		<tr {if $reportTypes|@count eq 1}style='display:none'{/if}>
			<td class='first'>
				{'PDFReports_ReportType'|translate}
			</td>
			<td>
				<select id='report_type'>
				{foreach from=$reportTypes key=reportType item=reportTypeIcon}
					<option value="{$reportType}">{$reportType|upper}</option>
				{/foreach}
				</select>
			</td>
		</tr>

		<tr>
			<td class='first'>
			{'PDFReports_ReportFormat'|translate}
			</td>

			<td>
				{foreach from=$reportFormatsByReportType key=reportType item=reportFormats}
					<select name='report_format' class='{$reportType}'>
						{foreach from=$reportFormats key=reportFormat item=reportFormatIcon}
							<option value="{$reportFormat}">{$reportFormat|upper}</option>
						{/foreach}
					</select>
				{/foreach}
			</td>
		</tr>

		{postEvent name="template_reportParametersPDFReports"}

		<tr>
			<td class="first">{'PDFReports_ReportsIncluded'|translate}</td>
			<td>
			{foreach from=$reportsByCategoryByReportType key=reportType item=reportsByCategory}
				<div name='reportsList' class='{$reportType}'>

					{if $allowMultipleReportsByReportType[$reportType]}
						{assign var=reportInputType value='checkbox'}
					{else}
						{assign var=reportInputType value='radio'}
					{/if}

					{assign var=countCategory value=0}

					{math
						equation="ceil (reportsByCategoryCount / 2)"
						reportsByCategoryCount=$reportsByCategory|@count
						assign=newColumnAfter
					}

					<div id='leftcolumn'>
					{foreach from=$reportsByCategory item=reports key=category name=reports}
						{if $countCategory >= $newColumnAfter && $newColumnAfter != 0}
							{assign var=newColumnAfter value=0}
							</div><div id='rightcolumn'>
						{/if}
						<div class='reportCategory'>{$category}</div><ul class='listReports'>
						{foreach from=$reports item=report}
							<li>
								<input type='{$reportInputType}' id="{$reportType}{$report.uniqueId}" report-unique-id='{$report.uniqueId}' name='{$reportType}Reports'/>
								<label for="{$reportType}{$report.uniqueId}">
									{$report.name|escape:"html"}
									{if $report.uniqueId=='MultiSites_getAll'}
										<div class="entityInlineHelp">{'PDFReports_ReportIncludeNWebsites'|translate:"$countWebsites "}</div>
									{/if}
								</label>
							</li>
						{/foreach}
						{assign var=countCategory value=$countCategory+1}
						</ul>
						<br/>
					{/foreach}
					</div>
				</div>
			{/foreach}
			</td>
		</tr>
		
	</tbody>
</table>

	<input type="hidden" id="report_idreport" value="">
	<input type="submit" id="report_submit" name="submit" class="submit"/>

</form>
<div class='entityCancel'>
	{'General_OrCancel'|translate:"<a class='entityCancelLink'>":"</a>"}
</div>
</div>