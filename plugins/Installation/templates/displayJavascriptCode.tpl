
{literal}
<style>
code {
	background-color:#F0F7FF;
	border-color:#00008B;
	border-style:dashed dashed dashed solid;
	border-width:1px 1px 1px 5px;
	direction:ltr;
	display:block;
	font-size:80%;
	margin:2px 2px 20px;
	padding:4px;
	text-align:left;
}
</style>

<script>
$(document).ready( function(){
	$('code').click( function(){ $(this).select(); });
});
</script>

{/literal}

{if isset($displayfirstWebsiteSetupSuccess)}

<span id="toFade" class="success">
	Website {$websiteName} created with success!
	<img src="themes/default/images/success_medium.png">
</span>
{/if}
<h1>{'Installation_JsTag'|translate}</h1>
{'Installation_JsTagHelp'|translate}
<code>
{$javascriptTag}
</code>

<h1>Quick help:</h1>
<ul>
<li>You can generally edit your website templates and add this code in a "footer" file</li>
<li><a target="_blank" href="http://piwik.org/javascript-tag-documentation/">More information about the javascript</a></li>
<li>Suggested: <a target="_blank" href="http://dev.piwik.org/trac/wiki/Crontab">How to setup a crontab to automatically archive overnight?</a></li>
<!-- <li>Link to help with the main blog engines wordpress/drupal/myspace/blogspot</li> -->
</ul>
