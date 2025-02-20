<?php
?><!DOCTYPE html>
<html lang='en'>
    <head>
        <title>User ID / Visitor ID Test</title>

        <script src='../../../node_modules/jquery/dist/jquery.min.js' type='text/javascript'></script>
    </head>
    <body>
        <h1>User ID / Visitor ID</h1>

        <script>
          let doForceNewVisit = <?= json_encode((bool) ($_GET['forceNewVisit'] ?? false)) ?>;
          const pkBaseUrl = (('https:' == document.location.protocol) ? 'https://%trackerBaseUrl%' : 'http://%trackerBaseUrl%');

          window._paq = window._paq || [];

          _paq.push(['setTrackerUrl', pkBaseUrl + 'matomo.php']);
          _paq.push(['setSiteId', %idSite%]);

          <?php if (!empty($_GET['userId'])) : ?>
          _paq.push(['setUserId', <?= json_encode($_GET['userId']) ?>]);
          <?php endif ?>

          (function() {
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true; g.src = '/matomo.js'; s.parentNode.insertBefore(g, s);
          })();

          // @see Piwik\Tests\Framework\Fixture::ADMIN_USER_TOKEN
          const tokenAuth = 'c4ca4238a0b923820dcc509a6f75849b';

          function trackAction(url, cdt) {
            const appendToTracking = [];

            if (cdt) {
              appendToTracking.push(`cdt=${cdt}`);
              appendToTracking.push(`token_auth=${tokenAuth}`);
            }

            if (doForceNewVisit) {
              appendToTracking.push('new_visit=1');
              doForceNewVisit = false;
            }

            _paq.push(['appendToTrackingUrl', appendToTracking.join('&')]);
            _paq.push(['trackLink', pkBaseUrl + url, 'link']);
            _paq.push(['appendToTrackingUrl', '']);
          }

          function trackPageView(url, cdt) {
            const appendToTracking = [];

            if (cdt) {
              appendToTracking.push(`cdt=${cdt}`);
              appendToTracking.push(`token_auth=${tokenAuth}`);
            }

            if (doForceNewVisit) {
              appendToTracking.push('new_visit=1');
              doForceNewVisit = false;
            }

            _paq.push(['setCustomUrl', pkBaseUrl + url]);
            _paq.push(['appendToTrackingUrl', appendToTracking.join('&')]);
            _paq.push(['trackPageView']);
            _paq.push(['appendToTrackingUrl', '']);
          }
        </script>
    </body>
</html>
