<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body style="font-family: {$reportFont}; color: rgb({$reportTextColor});">

	<a name="reportTop"/>
	<img src='{$currentHost}/themes/default/images/logo-header.png'/>

	<h1 style="color: rgb({$reportTitleTextColor}); font-size: {$reportTitleTextSize}px;">
		{'General_Website'|translate} {$websiteName}
	</h1>

	<p>
		{$description} - {'General_DateRange'|translate} {$prettyDate}
	</p>

	{if sizeof($reportMetadata) > 1}

		<h2 style="color: rgb({$reportTitleTextColor}); font-size: {$reportTitleTextSize}px;">
			{'PDFReports_TableOfContent'|translate}
		</h2>

		<ul>
			{foreach from=$reportMetadata item=metadata}
				<li>
					<a href="#{$metadata.uniqueId}" style="text-decoration:none; color: rgb({$reportTextColor});">
						{$metadata.name}
					</a>
				</li>
			{/foreach}
		</ul>

	{/if}