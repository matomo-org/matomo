<!-- Piwik -->
<script>
  var _paq = _paq || [];
{$options}
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://{$piwikUrl}";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', {$idSite}]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();

</script>
<noscript><p><img src="http://{$piwikUrl}piwik.php?idsite={$idSite}" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
