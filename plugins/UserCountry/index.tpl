{postEvent name="template_headerUserCountry"}

<script type="text/javascript" src="plugins/Home/templates/sparkline.js"></script>

<h2>Country</h2>
{$dataTableCountry}

<h2>Continent</h2>
{$dataTableContinent}

<p><img class="sparkline" src="{$urlSparklineCountries}" /> <span><strong>{$numberDistinctCountries} </strong> distinct countries</span></p>	

{postEvent name="template_footerUserCountry"}