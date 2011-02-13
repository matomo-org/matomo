{if $piwikUrl == 'http://demo.piwik.org/' || $debugTrackVisitsInsidePiwikUI}
<div class="clear"></div>
{literal}
<!-- Piwik -->
<script src="piwik.js" type="text/javascript"></script>
<script type="text/javascript">
try {
 var piwikTracker = Piwik.getTracker("piwik.php", 1);
 piwikTracker.setCustomVariable(1, "Piwik username", piwik.userLogin);
 piwikTracker.setCustomVariable(2, "Variable name ", "Value");
 piwikTracker.setDocumentTitle(document.domain + "/" + document.title);
 piwikTracker.trackPageView();
 piwikTracker.enableLinkTracking();
} catch(err) {}
</script>
<!-- End Piwik Code -->
{/literal}
{/if}
