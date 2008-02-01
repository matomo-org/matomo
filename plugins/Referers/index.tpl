<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

	<a name="evolutionGraph" graphId="getLastDistinctKeywordsGraph"></a>
	<h3>Evolution over the period</h3>
	{$graphEvolutionReferers}
	
	<h3>Referer Type</h3>
	<table>
		<tr><td>
			<p><img class="sparkline" src="{$urlSparklineDirectEntry}" /> <span><strong>{$visitorsFromDirectEntry} </strong> direct entries</span></p>
			<p><img class="sparkline" src="{$urlSparklineSearchEngines}" /> <span><strong>{$visitorsFromSearchEngines} </strong>  from search engines</span></p>
			<p><img class="sparkline" src="{$urlSparklinePartners}" /> <span><strong>{$visitorsFromPartners} </strong> from partners</span></p>
		</td><td>
			<p><img class="sparkline" src="{$urlSparklineWebsites}" /> <span><strong>{$visitorsFromWebsites} </strong> from websites</span></p>
			<p><img class="sparkline" src="{$urlSparklineNewsletters}" /> <span><strong>{$visitorsFromNewsletters} </strong>  from newsletters</span></p>
			<p><img class="sparkline" src="{$urlSparklineCampaigns}" /> <span><strong>{$visitorsFromCampaigns} </strong>  from campaigns</span></p>
		</td></tr>
	</table>
	
	<h3>Other</h3>
	<table>
		<tr><td>
			<p><img class="sparkline" src="{$urlSparklineDistinctSearchEngines}" /> <span><strong>{$numberDistinctSearchEngines} </strong>  distinct search engines</span></p>
			<p><img class="sparkline" src="{$urlSparklineDistinctKeywords}" /> <span><strong>{$numberDistinctKeywords} </strong> distinct keywords</span></p>
		</td><td>
			<p><img class="sparkline" src="{$urlSparklineDistinctWebsites}" /> <span><strong>{$numberDistinctWebsites} </strong>  distinct websites (using <strong>{$numberDistinctWebsitesUrls}</strong> distinct urls)</span></p>
			<p><img class="sparkline" src="{$urlSparklineDistinctPartners}" /> <span><strong>{$numberDistinctPartners} </strong>   distinct partners (using <strong>{$numberDistinctPartnersUrls}</strong> distinct urls)</span></p>
			<p><img class="sparkline" src="{$urlSparklineDistinctCampaigns}" /> <span><strong>{$numberDistinctCampaigns} </strong>  distinct campaigns</span></p>
			</td></tr>
	</table>
	
	<p>Tag cloud output</p>
	{$dataTableRefererType}