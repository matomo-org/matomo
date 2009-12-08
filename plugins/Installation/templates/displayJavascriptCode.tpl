
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
	{'Installation_SetupWebsiteSetupSuccess'|translate:$websiteName}
	<img src="themes/default/images/success_medium.png">
</span>
{/if}
<h1>{'Installation_JsTag'|translate}</h1>
{'Installation_JsTagHelp'|translate}
<code>
{$javascriptTag}
</code>

<h1>{'Installation_JsTagHelpTitle'|translate}</h1>
{include file=SitesManager/templates/JavascriptTagHelp.tpl}

<br/>
<h1>{'Installation_LargePiwikInstances'|translate}</h1>
{'Installation_JsTagArchivingHelp'|translate}
<!-- <li>Link to help with the main blog engines wordpress/drupal/myspace/blogspot</li> -->
