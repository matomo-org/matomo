<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

<a name="evolutionGraph" graphId="getLastVisitsGraph"></a>
<h3>Evolution on the last 30 {$period}s</h3>
{$graphEvolutionVisitsSummary}

<h3>Report</h3>

<p><img class="sparkline" src="{$urlSparklineNbVisits}" /> <span><strong>{$nbVisits} </strong>visits</span></p>
<p><img class="sparkline" src="{$urlSparklineNbUniqVisitors}" /> <span><strong>{$nbUniqVisitors}</strong> unique visitors</span></p>
<p><img class="sparkline" src="{$urlSparklineNbActions}" /> <span><strong>{$nbActions}</strong> actions (page views)</span></p>
<p><img class="sparkline" src="{$urlSparklineSumVisitLength}" /> <span><strong>{$sumVisitLength|sumtime}</strong> total time spent by the visitors</span></p>
<p><img class="sparkline" src="{$urlSparklineMaxActions}" /> <span><strong>{$maxActions}</strong> max actions in one visit</span></p>
<p><img class="sparkline" src="{$urlSparklineBounceCount}" /> <span><strong>{$bounceCount} </strong>visitors have bounced (left the site after one page)</span></p>


<br><br><br><hr width="300px" align="left">
<p><small>{$totalTimeGeneration} seconds {if $totalNumberOfQueries != 0}/ {$totalNumberOfQueries}  queries{/if} to generate the page</p>
 