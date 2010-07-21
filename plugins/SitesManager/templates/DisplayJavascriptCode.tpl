
{literal}
<style>
code {
	background-color:#F6F9F9;
	border-color:#3B3BB5;
	border-style:dashed dashed dashed solid;
	border-width:1px 1px 1px 5px;
	direction:ltr;
	display:table;
	font-size:100%;
	margin:12px 2px 0px;
	padding:5px 50px 5px 15px;
	text-align:left;
	line-height:1.3em;
	font-family: "Courier New" Courier monospace;
}
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
.trackingHelp .toggleHelp {
	display:none;
}
p {
	text-align:justify;
}
</style>
{/literal}

<h2>{'SitesManager_TrackingTags'|translate:$displaySiteName}</h2>

<div class='trackingHelp'>
To record visitors, visits and page views in Piwik, you must add a Tracking code in all your pages. 
We recommend to use the standard Javascript Tracking tag.

<h3>Standard Javascript Tracking tag</h3>
Copy and paste the following code in all the pages you want to track with Piwik. 
<br/>In most websites, blogs, CMS, etc. you can edit your website templates and add this code in a "footer" file.

<p>{'SitesManager_JsTrackingTagHelp'|translate}, just before the &lt;/body&gt; tag.</p>

<code>{$jsTag}</code>

<br/>
If you want to do more than tracking a page view,  
please check out the <a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/docs/javascript-tracking/">
Piwik Javascript Tracking documentation</a> for the list of available functions.

{include file='SitesManager/templates/DisplayAlternativeTags.tpl'}

</div>


{literal}
<script type='text/javascript'>
$(document).ready( function() {
	$('.toggleHelp').each(function() {
		var id = $(this).attr('id');
		// show 'display' link
		$(this).show(); 
		// hide help block
		$('.'+id).hide();
	});

	// click on Display links will toggle help text
	$('.toggleHelp').click( function() {
		// on click, show help block, hide link
		$('.'+ $(this).attr('id')).show();
		$(this).hide();
	});
});
</script>
{/literal}
