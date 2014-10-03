<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
{$options}  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    {$setTrackerUrl}
    {$optionsBeforeTrackerUrl}_paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', {$idSite}]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="{$protocol}{$piwikUrl}/piwik.php?idsite={$idSite}" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
