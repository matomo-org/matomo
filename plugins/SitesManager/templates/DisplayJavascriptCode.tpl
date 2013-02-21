
{literal}
<style type="text/css">
.trackingHelp ul { 
	padding-left:40px;
	list-style-type:square;
}
.trackingHelp ul li {
	margin-bottom:10px;
}
.trackingHelp h2 {
	margin-top:20px;
}
p {
	text-align:justify;
}
</style>
{/literal}

<h2>{'SitesManager_TrackingTags'|translate:$displaySiteName}</h2>

<div class='trackingHelp'>
{'Installation_JSTracking_Intro'|translate}
<br/><br/>
{'CoreAdminHome_JSTrackingIntro3'|translate:'<a href="http://piwik.org/integrate/" target="_blank">':'</a>'}

<h3>{'SitesManager_JsTrackingTag'|translate}</h3>
<p>{'CoreAdminHome_JSTracking_CodeNote'|translate:"&lt;/body&gt;"}</p>

<code>{$jsTag}</code>

<br />
{'CoreAdminHome_JSTrackingIntro5'|translate:'<a target="_blank" href="http://piwik.org/docs/javascript-tracking/">':'</a>'}
<br/><br/>
{'Installation_JSTracking_EndNote'|translate:'<em>':'</em>'}

</div>

