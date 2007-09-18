<?php

function main()
{
	$api = Piwik_API_Proxy::getInstance();
	
//	$api->SitesManager->addSite("t2site2", array("http://localhost44", "http://test123.com"));
//	$api->SitesManager->addSite("2e site33", array("http://localhost52", "http://test123.com"));
//	$api->SitesManager->addSite("te2 site44", array("http://localhost31231", "http://test123.com"));
//	$api->SitesManager->addSite("test name site", array("http://localhost", "http://test.com"));
//	$api->SitesManager->getSiteUrlsFromId(1);
//	$api->UsersManager->deleteUser("login");
//	$api->UsersManager->addUser("login", "password", "email@geage.com");
	require_once "API/Request.php";
	
//	Piwik::log("getResolution");
	$request = new Piwik_API_Request('
			method=UserSettings.getResolution
			&idSite=1
			&date=2007-08-25
			&period=week
			&format=console
			&filter_limit=
			&filter_offset=
			&filter_column=label
			&filter_pattern=
		');
	print(($request->process())); 
	
	exit;
	
	Piwik::log("getOS");
	$request = new Piwik_API_Request('method=UserSettings.getOS

			&idSite=1
			&date=2007-08-25
			&period=week
			&format=xml
			&filter_limit=
			&filter_offset=
			&filter_column=label
			&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getConfiguration");
	$request = new Piwik_API_Request('
				method=UserSettings.getConfiguration
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=10
				&filter_offset=0
				&filter_column=label
				&filter_pattern=
		');
	dump(htmlentities($request->process()));
	
	Piwik::log("getBrowser");
	$request = new Piwik_API_Request('
				method=UserSettings.getBrowser
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getBrowserType");
	$request = new Piwik_API_Request('
				method=UserSettings.getBrowserType
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getWideScreen");
	$request = new Piwik_API_Request('
				method=UserSettings.getWideScreen
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getPlugin");
	$request = new Piwik_API_Request('
				method=UserSettings.getPlugin
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getActions");
	$request = new Piwik_API_Request(
		'method=Actions.getActions
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=html
		&filter_limit=10
		&filter_offset=0
	'
	);
//	echo(($request->process()));

	Piwik::log("getActions EXPANDED");
	$request = new Piwik_API_Request(
		'method=Actions.getActions
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=html
		&expanded=true
		&filter_column=label
		&filter_pattern=a
		&filter_limit=10
		&filter_offset=0
		
	'
	);
//	echo(($request->process()));
	
	Piwik::log("getActions EXPANDED SUBTABLE");
	$request = new Piwik_API_Request(
		'method=Actions.getActions
		&idSubtable=5477
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=html
		&expanded=false
		
	'
	);
//	echo(($request->process()));
	
	Piwik::log("getDownloads");
	$request = new Piwik_API_Request(
		'method=Actions.getDownloads
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
//	dump(htmlentities($request->process()));
	Piwik::log("getOutlinks");
	$request = new Piwik_API_Request(
		'method=Actions.getOutlinks
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
//	dump(htmlentities($request->process()));
	Piwik::log("getProvider");
	$request = new Piwik_API_Request(
		'method=Provider.getProvider
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getCountry");
	$request = new Piwik_API_Request(
		'method=UserCountry.getCountry
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getContinent");
	$request = new Piwik_API_Request(
		'method=UserCountry.getContinent
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getContinent");
	$request = new Piwik_API_Request(
		'method=VisitFrequency.getSummary
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getNumberOfVisitsPerVisitDuration");
	$request = new Piwik_API_Request(
		'method=VisitorInterest.getNumberOfVisitsPerVisitDuration
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getNumberOfVisitsPerPage");
	$request = new Piwik_API_Request(
		'method=VisitorInterest.getNumberOfVisitsPerPage
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getVisitInformationPerServerTime");
	$request = new Piwik_API_Request(
		'method=VisitTime.getVisitInformationPerServerTime
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getRefererType");
	$request = new Piwik_API_Request(
		'method=Referers.getRefererType
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getKeywords");
	$request = new Piwik_API_Request(
		'method=Referers.getKeywords
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	Piwik::log("getSearchEnginesFromKeywordId");
	$request = new Piwik_API_Request(
		'method=Referers.getSearchEnginesFromKeywordId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&idSubtable=1886
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getSearchEngines");
	$request = new Piwik_API_Request(
		'method=Referers.getSearchEngines
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getKeywordsFromSearchEngineId");
	$request = new Piwik_API_Request(
		'method=Referers.getKeywordsFromSearchEngineId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=1779
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getCampaigns");
	$request = new Piwik_API_Request(
		'method=Referers.getCampaigns
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getKeywordsFromCampaignId");
	$request = new Piwik_API_Request(
		'method=Referers.getKeywordsFromCampaignId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=2251
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getWebsites");
	$request = new Piwik_API_Request(
		'method=Referers.getWebsites
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getUrlsFromWebsiteId");
	$request = new Piwik_API_Request(
		'method=Referers.getUrlsFromWebsiteId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=2432
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getPartners");
	$request = new Piwik_API_Request(
		'method=Referers.getPartners
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getUrlsFromPartnerId");
	$request = new Piwik_API_Request(
		'method=Referers.getUrlsFromPartnerId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=3090
	');
	dump(htmlentities($request->process()));
	
	
	$referersNumeric=array(
		'getNumberOfDistinctSearchEngines',	
		'getNumberOfDistinctKeywords',
		'getNumberOfDistinctCampaigns',
		'getNumberOfDistinctWebsites',
		'getNumberOfDistinctWebsitesUrls',
		'getNumberOfDistinctPartners',
		'getNumberOfDistinctPartnersUrls',
	);
	foreach($referersNumeric as $name)
	{
		Piwik::log("$name");
		$request = new Piwik_API_Request(
			"method=Referers.$name
			&idSite=1
			&date=2007-08-20
			&period=day
			&format=xml
			&filter_limit=10
			&filter_offset=0
		"
		);
		dump(htmlentities($request->process()));
	}
	
}