{if ereg('http://127.0.0.1|http://localhost|http://piwik.org', $url)}
{literal}
<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script language="javascript" src="piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
piwik_action_name = '';
piwik_idsite = 1;
piwik_url = 'piwik.php';
piwik_vars = { 'video_play':1, 'video_finished':0 };
piwik_log(piwik_action_name, piwik_idsite, piwik_url, piwik_vars);
//-->
</script><object>
<noscript><p>Web analytics <img src="piwik.php" style="border:0" alt="piwik"/></p>
</noscript></object></a>
<!-- /Piwik -->
{/literal}
{/if}