<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body style="color: rgb({$reportTextColor});">

	<a name="reportTop"/>
	<a target="_blank" href="{$currentPath}"><img title="{'General_GoTo'|translate:"Piwik"}" border="0" alt="Piwik" src='{$logoHeader}' /></a>

	<h1 style="color: rgb({$reportTitleTextColor}); font-size: {$reportTitleTextSize}pt;">
		{'General_Website'|translate} {$websiteName}
	</h1>

	<p>
		{$description} - {'General_DateRange'|translate} {$prettyDate}
	</p>

	{if sizeof($reportMetadata) > 1}

		<h2 style="color: rgb({$reportTitleTextColor}); font-size: {$reportTitleTextSize}pt;">
			{'PDFReports_TableOfContent'|translate}
		</h2>

		<ul>
			{foreach from=$reportMetadata item=metadata}
				<li>
					<a href="#{$metadata.uniqueId}" style="text-decoration:none; color: rgb({$reportTextColor});">
						{$metadata.name|escape:"html"}
					</a>
				</li>
			{/foreach}
		</ul>

	{/if}
