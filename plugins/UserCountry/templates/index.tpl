{postEvent name="template_headerUserCountry"}

<h2>{'UserCountry_Country'|translate}</h2>
{$dataTableCountry}

<h2>{'UserCountry_Continent'|translate}</h2>
{$dataTableContinent}

<div class="sparkline">
	{sparkline src=$urlSparklineCountries}
	{'UserCountry_DistinctCountries'|translate:"<strong>$numberDistinctCountries</strong>"}
</div>	

{postEvent name="template_footerUserCountry"}
