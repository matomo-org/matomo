{if $piwikUrl == 'http://demo.piwik.org/' || $debugTrackVisitsInsidePiwikUI}

<div class="clear"></div>
{literal}
<!-- Piwik -->
<script src="js/piwik.js" type="text/javascript"></script>
<script type="text/javascript">
try {
 var piwikTracker = Piwik.getTracker("piwik.php", 1);
 {/literal}
 {if $piwikUrl == 'http://demo.piwik.org/'}
 	piwikTracker.setCookieDomain('*.piwik.org');
 {/if}
 {literal}
 //Set the domain the visitor landed on, in the Custom Variable
 if(!piwikTracker.getCustomVariable(1)) { 
   piwikTracker.setCustomVariable(1, "Domain landed", document.domain );
 }
 //Set the selected Piwik language in a custom var
 piwikTracker.setCustomVariable(2, "Demo language", piwik.languageName );
 piwikTracker.setDocumentTitle(document.domain + "/" + document.title);
 piwikTracker.trackPageView();
 piwikTracker.enableLinkTracking();
} catch(err) {}
</script>
<!-- End Piwik Code -->
{/literal}
{/if}
