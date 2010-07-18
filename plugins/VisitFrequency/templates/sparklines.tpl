
<div class="sparkline">{sparkline src=$urlSparklineNbVisitsReturning}
{'VisitFrequency_ReturnVisits'|translate:"<strong>$nbVisitsReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineNbActionsReturning}
{'VisitFrequency_ReturnActions'|translate:"<strong>$nbActionsReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineActionsPerVisitReturning}
 {'VisitFrequency_ReturnAvgActions'|translate:"<strong>$nbActionsPerVisitReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineAvgVisitDurationReturning}
 {assign var=avgVisitDurationReturning value=$avgVisitDurationReturning|sumtime}
 {'VisitFrequency_ReturnAverageVisitDuration'|translate:"<strong>$avgVisitDurationReturning</strong>"}</div>
<div class="sparkline">{sparkline src=$urlSparklineBounceRateReturning}
 {'VisitFrequency_ReturnBounceRate'|translate:"<strong>$bounceRateReturning%</strong>"} </div>
{include file=CoreHome/templates/sparkline_footer.tpl}
