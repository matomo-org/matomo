

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
<h1>Javascript tag</h1>
<p>To count all visitors, you must insert the javascript code on all of your pages.</p>
<p>Your pages do not have to be made with PHP, phpMyVisites will work on all kinds of pages (whether it is HTML, ASP, Perl or any other languages).</p>
<p>Here is the code you have to insert: (copy and paste on all your pages) </P>
<code>
{$javascriptTag}
</code>

<p>Help todo</p>
<ul>
<li>Link to help with the main blog engines wordpress/drupal/myspace/blogspot</li>
<li>Concept of footer</li>
<li>How to use the piwik_action_name variable in the JS tag? for example replace by <pre> piwik_action_name = document.title;</pre> </li>	
</ul>
