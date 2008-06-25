
	<p><img class="sparkline" src="{$urlSparklineNbVisitsReturning}" alt="" /> <span>
	{'VisitFrequency_ReturnVisits'|translate:"<strong>$nbVisitsReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineNbActionsReturning}" alt="" /> <span>
	{'VisitFrequency_ReturnActions'|translate:"<strong>$nbActionsReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineMaxActionsReturning}" alt="" /> <span>
	 {'VisitFrequency_ReturnMaxActions'|translate:"<strong>$maxActionsReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineSumVisitLengthReturning}" alt="" /> <span>
	 {assign var=sumtimeVisitLengthReturning value=$sumVisitLengthReturning|sumtime}
	 {'VisitFrequency_ReturnTotalTime'|translate:"<strong>$sumtimeVisitLengthReturning</strong>"}</span></p>
	<p><img class="sparkline" src="{$urlSparklineBounceCountReturning}" alt="" /> <span>
	 {'VisitFrequency_ReturnBounces'|translate:"<strong>$bounceCountReturning</strong>"} </span></p>
