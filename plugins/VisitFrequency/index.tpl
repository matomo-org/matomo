<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

	<a name="evolutionGraph" graphId="getLastVisitsReturningGraph"></a>
	<h3>Evolution over the period</h3>
	{$graphEvolutionVisitFrequency}

	<p><img class="sparkline" src="{$urlSparklineNbVisitsReturning}" /> <span><strong>{$nbVisitsReturning} </strong> returning visits</span></p>
	<p><img class="sparkline" src="{$urlSparklineNbActionsReturning}" /> <span><strong>{$nbActionsReturning} </strong> actions by the returning visits</span></p>
	<p><img class="sparkline" src="{$urlSparklineMaxActionsReturning}" /> <span><strong>{$maxActionsReturning} </strong> maximum actions by a returning visit</span></p>
	<p><img class="sparkline" src="{$urlSparklineSumVisitLengthReturning}" /> <span><strong>{$sumVisitLengthReturning|sumtime} </strong> total time spent by returning visits</span></p>
	<p><img class="sparkline" src="{$urlSparklineBounceCountReturning}" /> <span><strong>{$bounceCountReturning} </strong> times that a returning visit has bounced (left the site after one page) </span></p>
