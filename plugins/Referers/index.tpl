<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

	<a name="evolutionGraph" graphId="{$nameGraphEvolutionReferers}"></a>
	<h2>{'Referers_Evolution'|translate}</h2>
	{$graphEvolutionReferers}
	
	<h2>{'Referers_Type'|translate}</h2>
	<table>
		<tr><td>
			<p><img class="sparkline" src="{$urlSparklineDirectEntry}" /> <span>
			{'Referers_TypeDirectEntries'|translate:"<strong>$visitorsFromDirectEntry</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklineSearchEngines}" /> <span>
			{'Referers_TypeSearchEngines'|translate:"<strong>$visitorsFromSearchEngines</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklinePartners}" /> <span>
			{'Referers_TypePartners'|translate:"<strong>$visitorsFromPartners</strong>"}</span></p>
		</td><td>
			<p><img class="sparkline" src="{$urlSparklineWebsites}" /> <span>
			{'Referers_TypeWebsites'|translate:"<strong>$visitorsFromWebsites</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklineNewsletters}" /> <span>
			{'Referers_TypeNewsletters'|translate:"<strong>$visitorsFromNewsletters</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklineCampaigns}" /> <span>
			{'Referers_TypeCampaigns'|translate:"<strong>$visitorsFromCampaigns</strong>"}</span></p>
		</td></tr>
	</table>
	
	<h2>{'Referers_Other'|translate}</h2>
	<table>
		<tr><td>
			<p><img class="sparkline" src="{$urlSparklineDistinctSearchEngines}" /> <span>
			{'Referers_OtherDistinctSearchEngines'|translate:"<strong>$numberDistinctSearchEngines</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklineDistinctKeywords}" /> <span>
			{'Referers_OtherDistinctKeywords'|translate:"<strong>$numberDistinctKeywords</strong>"}</span></p>
		</td><td>
			<p><img class="sparkline" src="{$urlSparklineDistinctWebsites}" /> <span>
			{'Referers_OtherDistinctWebsites'|translate:"<strong>$numberDistinctWebsites</strong>":"<strong>$numberDistinctWebsitesUrls</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklineDistinctPartners}" /> <span>
			{'Referers_OtherDistinctPartners'|translate:"<strong>$numberDistinctPartners</strong>":"<strong>$numberDistinctPartnersUrls</strong>"}</span></p>
			<p><img class="sparkline" src="{$urlSparklineDistinctCampaigns}" /> <span> 
			{'Referers_OtherDistinctCampaigns'|translate:"<strong>$numberDistinctCampaigns</strong>"}</span></p>
			</td></tr>
	</table>
	
       <p>{'Referers_TagCloud'|translate}</p>
       {$dataTableRefererType}

