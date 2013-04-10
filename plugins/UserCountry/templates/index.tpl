<div id="leftcolumn">
    {postEvent name="template_leftColumnUserCountry"}

    <h2>{'UserCountry_Continent'|translate}</h2>
    {$dataTableContinent}

    <div class="sparkline">
        {sparkline src=$urlSparklineCountries}
        {'UserCountry_DistinctCountries'|translate:"<strong>$numberDistinctCountries</strong>"}
    </div>

    {postEvent name="template_footerUserCountry"}

</div>

<div id="rightcolumn">

    <h2>{'UserCountry_Country'|translate}</h2>
    {$dataTableCountry}

    <h2>{'UserCountry_Region'|translate}</h2>
    {$dataTableRegion}

    <h2>{'UserCountry_City'|translate}</h2>
    {$dataTableCity}

</div>

