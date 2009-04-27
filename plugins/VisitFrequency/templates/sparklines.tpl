<div class="sparkline">{sparkline src=$urlSparklineNbVisitsReturning}
{'VisitFrequency_ReturnVisits'|translate:"<strong>$nbVisitsReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineNbActionsReturning}
{'VisitFrequency_ReturnActions'|translate:"<strong>$nbActionsReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineMaxActionsReturning}
 {'VisitFrequency_ReturnMaxActions'|translate:"<strong>$maxActionsReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineSumVisitLengthReturning}
 {assign var=sumtimeVisitLengthReturning value=$sumVisitLengthReturning|sumtime}
 {'VisitFrequency_ReturnTotalTime'|translate:"<strong>$sumtimeVisitLengthReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineBounceRateReturning}
 {'VisitFrequency_ReturnBounceRate'|translate:"<strong>$bounceRateReturning%</strong>"} </div>