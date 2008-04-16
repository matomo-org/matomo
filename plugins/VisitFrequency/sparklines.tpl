
	<p><img class="sparkline" src="{$urlSparklineNbVisitsReturning}" /> <span>
	{'VisitFrequency_ReturnVisits'|translate:"<strong>$nbVisitsReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineNbActionsReturning}" /> <span>
	{'VisitFrequency_ReturnActions'|translate:"<strong>$nbActionsReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineMaxActionsReturning}" /> <span>
	 {'VisitFrequency_ReturnMaxActions'|translate:"<strong>$maxActionsReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineSumVisitLengthReturning}" /> <span>
	 {assign var=sumtimeVisitLengthReturning value=$sumVisitLengthReturning|sumtime}
	 {'VisitFrequency_ReturnTotalTime'|translate:"<strong>$sumtimeVisitLengthReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineBounceCountReturning}" /> <span>
	 {'VisitFrequency_ReturnBounces'|translate:"<strong>$bounceCountReturning</strong>"} </span></p>
