
<!-- Piwik -->
<a href="http://piwik.org" title="{$hrefTitle}" onclick="window.open(this.href);return(false);">
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://{$piwikUrl}" : "http://{$piwikUrl}");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
<!--
	piwik_action_name = {$actionName};
	piwik_idsite = {$idSite};
	piwik_url = pkBaseURL + "piwik.php";
	piwik_log(piwik_action_name, piwik_idsite, piwik_url);
//-->
</script><object>
<noscript><p>{$hrefTitle} <img src="http://{$piwikUrl}piwik.php" style="border:0" alt="piwik"/></p>
</noscript></object></a>
<!-- /Piwik -->