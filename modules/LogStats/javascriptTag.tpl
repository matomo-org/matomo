<!-- Piwik -->
<a href="http://piwik.org" title="{$hrefTitle}" onclick="window.open(this.href);return(false);">
<script language="javascript" src="{$piwikUrl}piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
	piwik_action_name = {$actionName};
	piwik_idsite = {$idSite};
	piwik_url = '{$piwikUrl}piwik.php';
	piwik_log(piwik_action_name, piwik_idsite, piwik_url);
//-->
</script><object>
<noscript><p>{$hrefTitle} <img src="{$piwikUrl}piwik.php" style="border:0" alt="piwik"/></p>
</noscript></object></a>
<!-- /Piwik -->