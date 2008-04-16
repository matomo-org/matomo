{postEvent name="template_headerUserCountry"}

<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

<h2>{'UserCountry_Country'|translate}</h2>
{$dataTableCountry}

<h2>{'UserCountry_Continent'|translate}</h2>
{$dataTableContinent}

<p><img class="sparkline" src="{$urlSparklineCountries}" /> <span>
{'UserCountry_DistinctCountries'|translate:"<strong>$numberDistinctCountries</strong>"} </span></p>	

{postEvent name="template_footerUserCountry"}
